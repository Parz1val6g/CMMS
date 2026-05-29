import { useState, useEffect, useCallback, useRef, useMemo } from 'react';
import ClientLocationSelector from '@/Components/Shared/ClientLocationSelector';
import { csrfHeader } from '@/utils/csrf';
import { buildCreatePayload } from '@/utils/serviceOrderPayload';
import { labelFor, badgeStyle } from '@/utils/enums';
import { formatDate } from '@/utils/format';
import { usePage } from '@inertiajs/react';
import { useToast } from '@/Components/Toast/ToastContext';
import { MapPin, Clock, Loader2, AlertCircle, Play, Check } from 'lucide-react';
import { t } from '@/utils/i18n';
import AppLayout from '@/Layouts/AppLayout';
import DataManager from '@/Components/DataManager';
import Modal from '@/Components/Common/Modal';
import PhotoListener from '@/Hooks/usePhotoListener';
import KanbanBoard from '@/Components/Kanban/KanbanBoard';
import WorkspaceDrawer from '@/Components/Drawer/WorkspaceDrawer';
import SOTasksTree from '../Components/DrawerTabs/TasksTree';
import LocationMap from '@/Components/Shared/LocationMap';

export default function ServiceOrdersIndex({ service_orders, columns, formSchema, createFormSchema, routes, filterSchema, advancedFilterFields}) {
  const [showModal, setShowModal] = useState(false);
  const [formErrors, setFormErrors] = useState({});
  const [photoPreview, setPhotoPreview] = useState(null);
  const [clientLocationId, setClientLocationId] = useState(null);
  const [locationsDirty, setLocationsDirty] = useState(false);
  const [currentClientId, setCurrentClientId] = useState(null);
  const [viewMode, setViewMode] = useState('table'); // 'table' or 'kanban'
  const [serviceOrdersState, setServiceOrdersState] = useState(service_orders);
  const [drawerOpen, setDrawerOpen] = useState(false);
  const [selectedServiceOrder, setSelectedServiceOrder] = useState(null);
  const [soDetail, setSoDetail] = useState(null);
  const [soLoading, setSoLoading] = useState(false);
  const [soError, setSoError] = useState(null);
  const [toast, setToast] = useState(null);
  const savingRef = useRef(false);
  const { flash, can } = usePage().props;
  const globalToast = useToast();
  const [activating, setActivating] = useState(false);
  const [completing, setCompleting] = useState(false);

  const breadcrumbs = [
    { name: t('pages.sidebar.dashboard'), url: '/dashboard' },
    { name: t('pages.sidebar.service_orders'), url: '/service-orders' },
  ];

  /* ── Sync Inertia prop into local state on partial reloads ── */
  useEffect(() => {
    setServiceOrdersState(service_orders);
  }, [service_orders]);

  /* ── Flash message auto-dismiss ───────────────────────────── */
  useEffect(() => {
    if (flash?.success || flash?.error) {
      setToast(flash);
      const t = setTimeout(() => setToast(null), 4000);
      return () => clearTimeout(t);
    }
  }, [flash]);

  /* ── Track client_id changes for ClientLocationSelector ──── */
  useEffect(() => {
    if (!showModal) {
      setCurrentClientId(null);
      setClientLocationId(null);
      setLocationsDirty(false);
      return;
    }

    // Read initial value after Modal renders fields
    const initEl = document.querySelector('select[name="client_id"]');
    if (initEl?.value) setCurrentClientId(initEl.value);

    const handler = (e) => {
      if (e.detail.name === 'client_id') {
        const newId = e.detail.value || null;
        setCurrentClientId(newId);
        // Changing client clears any saved location selection
        setClientLocationId(null);
        setLocationsDirty(false);
      }
    };
    document.addEventListener('modal-field-change', handler);
    return () => document.removeEventListener('modal-field-change', handler);
  }, [showModal]);

  /* ── Create form submission via FormData (multipart) ─────── */
  const handleCreate = useCallback(async (e, formValues = {}) => {
    e.preventDefault();
    if (!routes.store || savingRef.current) return;
    savingRef.current = true;
    setFormErrors({});

    const form = e.target;
    const formData = new FormData(form);

    // Append React-controlled multi-select values (not captured by FormData)
    ['sector_ids'].forEach((key) => {
      const vals = formValues[key];
      if (Array.isArray(vals) && vals.length > 0) {
        vals.forEach((v) => formData.append(`${key}[]`, v));
      }
    });

    // Scenario A/B/C: client location logic
    const LOCATION_FIELDS = ['parish_id', 'street', 'reference_point', 'postal_code', 'latitude', 'longitude'];
    buildCreatePayload(formData, clientLocationId, locationsDirty, LOCATION_FIELDS);

    try {
      const res = await fetch(routes.store, {
        method: 'POST',
        headers: {
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
          ...csrfHeader(),
        },
        body: formData,
      });
      const body = await res.json();

      if (res.ok) {
        setShowModal(false);
        setPhotoPreview(null);
        setClientLocationId(null);
        setLocationsDirty(false);
        window.location.reload();
      } else {
        if (body.errors) setFormErrors(body.errors);
        else globalToast.error(body.message ?? body.error ?? t('pages.service_orders.create_failed'));
      }
    } catch {
      globalToast.error(t('pages.service_orders.unexpected_error'));
    } finally {
      savingRef.current = false;
    }
  }, [routes.store, globalToast, clientLocationId, locationsDirty]);

  /* ── Open modal for create ────────────────────────────────── */
  const openCreate = useCallback(() => {
    setFormErrors({});
    setPhotoPreview(null);
    setShowModal(true);
  }, []);

  /* ── Handle file input change for preview ─────────────────── */
  const onFileChange = useCallback((e) => {
    const file = e.target.files?.[0];
    if (file) {
      setPhotoPreview(URL.createObjectURL(file));
    } else {
      setPhotoPreview(null);
    }
  }, []);

  /* ── Kanban Status Columns Configuration ──────────────────── */
  const statusColumns = [
    { id: 'pending', label: t('pages.service_orders.col_pending'), color: 'bg-yellow-500/20 text-yellow-300 border-yellow-500' },
    { id: 'in_progress', label: t('pages.service_orders.col_in_progress'), color: 'bg-blue-500/20 text-blue-300 border-blue-500' },
    { id: 'awaiting_approval', label: t('pages.service_orders.col_awaiting_approval'), color: 'bg-orange-500/20 text-orange-300 border-orange-500' },
    { id: 'completed', label: t('pages.service_orders.col_completed'), color: 'bg-green-500/20 text-green-300 border-green-500' },
    { id: 'cancelled', label: t('pages.service_orders.col_cancelled'), color: 'bg-red-500/20 text-red-300 border-red-500' },
  ];

  /* ── Handle Drag & Drop with Optimistic UI ──────────────────── */
  const handleDragEnd = useCallback(async (event) => {
    const { activeId, overId } = event;

    if (!overId || String(activeId) === String(overId)) return;

    const newStatus = overId;
    const serviceOrderId = activeId;

    // Find the service order being moved
    const itemsArray = Array.isArray(serviceOrdersState) ? serviceOrdersState : (serviceOrdersState?.data ?? []);
    const serviceOrder = itemsArray.find(so => String(so.id) === String(serviceOrderId));
    if (!serviceOrder) return;

    // Store original state for rollback
    const originalState = serviceOrdersState;

    // Optimistic UI update — move card immediately
    const updatedItems = itemsArray.map(so =>
      String(so.id) === String(serviceOrderId) ? { ...so, status: newStatus } : so
    );
    setServiceOrdersState(Array.isArray(serviceOrdersState) ? updatedItems : { ...serviceOrdersState, data: updatedItems });

    // Fire backend request
    try {
      const res = await fetch(`${routes.update.replace(':id', serviceOrderId)}`, {
        method: 'PATCH',
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
          ...csrfHeader(),
        },
        body: JSON.stringify({ status: newStatus }),
      });

      if (!res.ok) {
        // Rollback on error
        setServiceOrdersState(originalState);
        const body = await res.json();
        setToast({
          error: body.error || t('pages.service_orders.status_update_failed'),
        });
      }
    } catch {
      // Rollback on network error
      setServiceOrdersState(originalState);
      setToast({
        error: t('pages.service_orders.network_error'),
      });
    }
  }, [serviceOrdersState, routes.update]);

  /* ── Open drawer & lazy-load full SO data via API ──────────── */
  const handleCardClick = useCallback(async (item) => {
    setSelectedServiceOrder(item);
    setDrawerOpen(true);
    setSoDetail(null);
    setSoLoading(true);
    setSoError(null);

    try {
      const res = await fetch(`/api/service-orders/${item.id}`, {
        headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest', ...csrfHeader() },
      });
      if (!res.ok) throw new Error(`HTTP ${res.status}`);
      const body = await res.json();
      setSoDetail(body.data ?? body);
    } catch (err) {
      setSoError(err.message);
    } finally {
      setSoLoading(false);
    }
  }, []);

  /* ── Close drawer & clear detail state ────────────────────── */
  const handleCloseDrawer = useCallback(() => {
    setDrawerOpen(false);
    // Clear detail so next open triggers a fresh fetch
    setSoDetail(null);
    setSoError(null);
  }, []);

  /* ── Auto-open drawer when arriving via ?view=<id> link ─── */
  const viewParamHandled = useRef(false);
  useEffect(() => {
    if (viewParamHandled.current) return;
    viewParamHandled.current = true;
    const id = new URLSearchParams(window.location.search).get('view');
    if (!id) return;
    window.history.replaceState({}, '', window.location.pathname);
    handleCardClick({ id });
  }, []); // eslint-disable-line react-hooks/exhaustive-deps

  /* ── Activate / Complete handlers ─────────────────────────── */
  const handleActivate = useCallback(async () => {
    if (!selectedServiceOrder) return;
    setActivating(true);
    try {
      const res = await fetch(`/api/service-orders/${selectedServiceOrder.id}/activate`, {
        method: 'POST',
        headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest', ...csrfHeader() },
      });
      if (!res.ok) throw new Error();
      handleCloseDrawer();
      window.location.reload();
    } catch {
      globalToast.error(t('pages.service_orders.activate_failed'));
    } finally {
      setActivating(false);
    }
  }, [selectedServiceOrder, handleCloseDrawer, globalToast]);

  const handleComplete = useCallback(async () => {
    if (!selectedServiceOrder) return;
    setCompleting(true);
    try {
      const res = await fetch(`/api/service-orders/${selectedServiceOrder.id}/complete`, {
        method: 'POST',
        headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest', ...csrfHeader() },
      });
      if (!res.ok) throw new Error();
      handleCloseDrawer();
      window.location.reload();
    } catch {
      globalToast.error(t('pages.service_orders.complete_failed'));
    } finally {
      setCompleting(false);
    }
  }, [selectedServiceOrder, handleCloseDrawer, globalToast]);

  /* ── Build tabs array for WorkspaceDrawer ─────────────────── */
  const soTabs = useMemo(() => {
    const so = selectedServiceOrder;
    const detail = soDetail;
    if (!so) return [];

    // Show loading spinner while fetching
    if (soLoading) {
      return [{
        id: 'loading',
        label: t('pages.service_orders.tab_details'),
        component: (
          <div className="flex items-center justify-center h-40 text-brand-mid gap-2">
            <Loader2 className="h-5 w-5 animate-spin" />
            <span className="text-sm">{t('pages.service_orders.tab_details')}…</span>
          </div>
        ),
      }];
    }

    // Show error state
    if (soError) {
      return [{
        id: 'error',
        label: t('pages.service_orders.drawer.tab_error'),
        component: (
          <div className="flex items-center justify-center h-40 text-red-400 gap-2">
            <AlertCircle className="h-5 w-5" />
            <span className="text-sm">{t('pages.service_orders.unexpected_error')}: {soError}</span>
          </div>
        ),
      }];
    }

    // Use detail data if available, fallback to list data
    const data = detail ?? so;

    const tabs = [
      { id: 'details', label: t('pages.service_orders.tab_details'), component: <SODetailsTab serviceOrder={data} /> },
      { id: 'tasks', label: t('pages.service_orders.tab_tasks'), component: <SOTasksTree serviceOrderId={so.id} /> },
    ];

    return tabs;
  }, [selectedServiceOrder, soDetail, soLoading, soError]);

  /* ── Drawer header action buttons ─────────────────────────── */
  const drawerActions = useMemo(() => {
    const so = soDetail ?? selectedServiceOrder;
    if (!so) return null;
    const status = so.status?.value ?? so.status;
    const showActivate = status === 'pending' && can?.activateServiceOrder;
    const showComplete = status === 'awaiting_approval' && can?.completeServiceOrder;
    return (
      <>
        {showActivate && (
          <button
            type="button"
            disabled={activating}
            onClick={handleActivate}
            className="inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-semibold text-white bg-brand-accent hover:opacity-90 transition-opacity disabled:opacity-50"
          >
            <Play size={12} />
            {activating ? '…' : t('pages.service_orders.btn_activate')}
          </button>
        )}
        {showComplete && (
          <button
            type="button"
            disabled={completing}
            onClick={handleComplete}
            className="inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-semibold text-white bg-green-600 hover:bg-green-700 transition-colors disabled:opacity-50"
          >
            <Check size={12} />
            {completing ? '…' : t('pages.service_orders.btn_complete')}
          </button>
        )}
      </>
    );
  }, [soDetail, selectedServiceOrder, can, activating, completing, handleActivate, handleComplete]);

  /* ── Drawer title with SO context ─────────────────────────── */
  const drawerTitle = useMemo(() => {
    const so = selectedServiceOrder;
    if (!so) return null;

    return (
      <div className="flex items-center gap-4">
        <span>{so.process || `${t('pages.service_orders.drawer.os_prefix')}${so.id}`}</span>
        <span className={`inline-flex items-center px-2.5 py-0.5 text-xs font-semibold rounded-full ${badgeStyle(so.status, { border: true })}`}>
          {labelFor(so.status)}
        </span>
        <span className={`inline-flex items-center px-2.5 py-0.5 text-xs font-semibold rounded-full ${badgeStyle(so.priority, { border: true })}`}>
          {labelFor(so.priority)}
        </span>
      </div>
    );
  }, [selectedServiceOrder]);

  /* ── Kanban Card Content Renderer ─────────────────────────── */
  const renderCardContent = useCallback((item) => {
    const managerInitial = item.manager?.name?.split(' ')[0]?.[0]?.toUpperCase() || '?';

    return (
      <>
        {/* Process ID */}
        <p className="text-brand-accent font-mono font-bold text-xs mb-1">
          {item.process}
        </p>

        {/* Title/Description */}
        {item.description && item.description.trim() && (
          <h4 className="text-sm font-semibold text-brand-darkest mb-2 line-clamp-2">
            {item.description}
          </h4>
        )}

        {/* Client */}
        {item.client && (
          <p className="text-xs text-brand-darkest mb-2">
            <span className="font-medium">{t('pages.service_orders.card_client_label')}</span> {item.client.name}
          </p>
        )}

        {/* Location & Date */}
        <div className="space-y-1 mb-3">
          {item.location && (
            <div className="flex items-start gap-2 text-xs text-brand-mid">
              <MapPin className="h-3 w-3 mt-0.5 flex-shrink-0 text-brand-accent" />
              <span className="line-clamp-1">
                {item.location.street || item.location.landmark || t('pages.service_orders.card_unknown_location')}
              </span>
            </div>
          )}
          {(item.start_date || item.end_date) && (
            <div className="flex items-center gap-2 text-xs text-brand-mid">
              <Clock className="h-3 w-3 flex-shrink-0 text-brand-accent" />
              <span>{[item.start_date, item.end_date].filter(Boolean).map(d => formatDate(d)).join(' – ')}</span>
            </div>
          )}
        </div>

        {/* Footer: Priority Badge + Manager Avatar */}
        <div className="flex items-center justify-between">
          <span className={`text-xs font-medium px-2 py-1 rounded ${{
              low: 'bg-blue-500/20 text-blue-300',
              normal: 'bg-brand-mid/20 text-brand-mid',
              high: 'bg-orange-500/20 text-orange-300',
              urgent: 'bg-red-500/20 text-red-300',
            }[item.priority] || 'bg-brand-mid/20 text-brand-mid'
            }`}>
            {labelFor(item.priority)}
          </span>
          <div
            className="w-6 h-6 rounded-full bg-brand-accent text-brand-white flex items-center justify-center text-xs font-bold"
            title={item.manager?.name ? `${t('pages.service_orders.drawer.assigned_to')}${item.manager.name}` : t('pages.service_orders.drawer.unassigned')}
            aria-label={item.manager?.name ? `${t('pages.service_orders.drawer.assigned_to')}${item.manager.name}` : t('pages.service_orders.drawer.unassigned')}
          >
            {managerInitial}
          </div>
        </div>
      </>
    );
  }, []);

  return (
    <AppLayout title={t('pages.service_orders.page_title')} breadcrumbs={breadcrumbs}>
      {/* Flash Toast */}
      {toast && (
        <div
          className={`mb-4 rounded-lg px-4 py-3 text-sm shadow-sm ${toast.success
            ? 'bg-green-50 text-green-700'
            : 'bg-red-50 text-red-700'
            }`}
        >
          {toast.success ?? toast.error}
          <button
            type="button"
            className="ml-3 font-medium underline"
            onClick={() => setToast(null)}
          >
            {t('pages.service_orders.dismiss')}
          </button>
        </div>
      )}

      <Modal
        formSchema={createFormSchema}
        routes={routes}
        size="lg"
        open={showModal}
        onClose={() => setShowModal(false)}
        onSubmit={handleCreate}
        injectAfterField="client_id"
        externalErrors={formErrors}
      >
        <ClientLocationSelector
          isOpen={showModal}
          clientId={currentClientId}
          onClientLocationChange={setClientLocationId}
          onDirtyChange={setLocationsDirty}
        />
      </Modal>

      {/* Hidden file change handler — wire via DOM delegation */}
      {showModal && (
        <PhotoListener inputSelector="input[name='photo']" onFileChange={onFileChange} />
      )}

      {/* Unified View with DataManager handling toggles */}
      <DataManager
        title={t('pages.service_orders.dm_title')}
        entityName={t('pages.service_orders.dm_entity_name')}
        items={service_orders}
        routes={routes}
        columns={columns}
        formSchema={formSchema}
        filterSchema={filterSchema ?? []}
        advancedFilterFields={advancedFilterFields ?? []}
        onNew={openCreate}
        onRowClick={handleCardClick}
        viewMode={viewMode}
        onViewModeChange={setViewMode}
        supportKanban={true}
      >
        {/* Kanban Board (only render when in Kanban view) */}
        {viewMode === 'kanban' && (
          <KanbanBoard
            items={serviceOrdersState}
            columns={statusColumns}
            statusField="status"
            renderCardContent={renderCardContent}
            onDragEnd={handleDragEnd}
            onCardClick={handleCardClick}
          />
        )}
      </DataManager>

      {/* Service Order Workspace Drawer */}
      <WorkspaceDrawer
        isOpen={drawerOpen}
        onClose={handleCloseDrawer}
        title={drawerTitle}
        subtitle={selectedServiceOrder?.client?.name ? `${t('pages.service_orders.drawer_client_label')} ${selectedServiceOrder.client.name}` : null}
        tabs={soTabs}
        headerActions={drawerActions}
      />
    </AppLayout>
  );
}

/* ── Details tab content for Service Order (no created_at) ──── */
function SODetailsTab({ serviceOrder }) {
  const so = serviceOrder;
  if (!so) return null;

  return (
    <div className="space-y-6">
      {/* Description */}
      {so.description && (
        <section>
          <h4 className="text-sm font-semibold text-brand-mid uppercase tracking-wider mb-2">{t('pages.service_orders.section_description')}</h4>
          <p className="text-sm text-brand-darkest">{so.description}</p>
        </section>
      )}

      {/* Client */}
      {so.client && (
        <section>
          <h4 className="text-sm font-semibold text-brand-mid uppercase tracking-wider mb-2">{t('pages.service_orders.section_client')}</h4>
          <p className="text-sm text-brand-darkest">{so.client.name}</p>
          {so.client.nif && <p className="text-xs text-brand-mid mt-0.5">{t('pages.service_orders.drawer.nif_prefix')}{so.client.nif}</p>}
        </section>
      )}

      {/* Manager */}
      {so.manager && (
        <section>
          <h4 className="text-sm font-semibold text-brand-mid uppercase tracking-wider mb-2">{t('pages.service_orders.section_manager')}</h4>
          <p className="text-sm text-brand-darkest">{so.manager.name}</p>
        </section>
      )}

      {/* Location */}
      {so.location && (
        <section>
          <h4 className="text-sm font-semibold text-brand-mid uppercase tracking-wider mb-2">{t('pages.service_orders.section_location')}</h4>
          <LocationMap location={so.location} />
        </section>
      )}

      {/* Service Type */}
      {so.service_type && (
        <section>
          <h4 className="text-sm font-semibold text-brand-mid uppercase tracking-wider mb-2">{t('pages.service_orders.section_service_type')}</h4>
          <p className="text-sm text-brand-darkest">{so.service_type.name || so.service_type}</p>
        </section>
      )}

      {/* Start Date */}
      {so.start_date && (
        <section>
          <h4 className="text-sm font-semibold text-brand-mid uppercase tracking-wider mb-2">{t('pages.service_orders.section_start_date')}</h4>
          <p className="text-sm text-brand-darkest">{formatDate(so.start_date)}</p>
        </section>
      )}

      {/* End Date */}
      {so.end_date && (
        <section>
          <h4 className="text-sm font-semibold text-brand-mid uppercase tracking-wider mb-2">{t('pages.service_orders.section_end_date')}</h4>
          <p className="text-sm text-brand-darkest">{formatDate(so.end_date)}</p>
        </section>
      )}

      {/* Sectors */}
      {so.sectors?.length > 0 && (
        <section>
          <h4 className="text-sm font-semibold text-brand-mid uppercase tracking-wider mb-2">{t('pages.service_orders.section_sectors')}</h4>
          <div className="flex flex-wrap gap-1.5">
            {so.sectors.map((s) => (
              <span key={s.id} className="inline-flex items-center px-2 py-0.5 text-xs rounded-full bg-brand-mid/20 text-brand-mid border border-brand-mid/30">
                {s.name}
              </span>
            ))}
          </div>
        </section>
      )}

      {/* Photo */}
      {so.photo_url && (
        <section>
          <h4 className="text-sm font-semibold text-brand-mid uppercase tracking-wider mb-2">{t('pages.service_orders.section_photo')}</h4>
          <img
            src={so.photo_url}
            alt={t('pages.service_orders.photo_preview_alt')}
            className="h-40 w-full object-cover rounded-lg border border-brand-mid/20"
          />
        </section>
      )}
    </div>
  );
}

