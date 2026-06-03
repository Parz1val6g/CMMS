import { useState, useEffect, useCallback, useRef, useMemo } from 'react';
import ClientLocationSelector from '@/Components/Shared/ClientLocationSelector';
import SectorConfigPanel from '../Components/SectorConfigPanel';
import { csrfHeader } from '@/utils/csrf';
import { buildCreatePayload } from '@/utils/serviceOrderPayload';
import { labelFor, badgeStyle } from '@/utils/enums';
import { formatDate } from '@/utils/format';
import { usePage } from '@inertiajs/react';
import { useToast } from '@/Components/Toast/ToastContext';
import { useOptimisticMutation } from '@/composables/useOptimisticMutation';
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

export default function ServiceOrdersIndex({ service_orders, columns, formSchema, createFormSchema, routes, filterSchema, advancedFilterFields, serviceTypesBySector = {} }) {
  const [showModal, setShowModal] = useState(false);
  const [formErrors, setFormErrors] = useState({});
  const [photoPreview, setPhotoPreview] = useState(null);
  const [clientLocationId, setClientLocationId] = useState(null);
  const [locationsDirty, setLocationsDirty] = useState(false);
  const [currentClientId, setCurrentClientId] = useState(null);
  const [sectorConfigs, setSectorConfigs] = useState([]);
  const [viewMode, setViewMode] = useState('table'); // 'table' or 'kanban'
  const [refreshKey, setRefreshKey] = useState(0);
  const [serviceOrdersState, setServiceOrdersState] = useState(service_orders);
  const [drawerOpen, setDrawerOpen] = useState(false);
  const [selectedServiceOrder, setSelectedServiceOrder] = useState(null);
  const [soDetail, setSoDetail] = useState(null);
  const [soLoading, setSoLoading] = useState(false);
  const [soError, setSoError] = useState(null);
  const savingRef = useRef(false);
  const { can } = usePage().props;
  const globalToast = useToast();
  const { mutate } = useOptimisticMutation();

  const breadcrumbs = [
    { name: t('pages.sidebar.dashboard'), url: '/dashboard' },
    { name: t('pages.sidebar.service_orders'), url: '/service-orders' },
  ];

  /* ── Sync Inertia prop into local state on partial reloads ── */
  useEffect(() => {
    setServiceOrdersState(service_orders);
  }, [service_orders]);

  /* ── Track client_id changes for ClientLocationSelector ──── */
  useEffect(() => {
    if (!showModal) {
      setCurrentClientId(null);
      setClientLocationId(null);
      setLocationsDirty(false);
      setSectorConfigs([]);
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

    if (sectorConfigs.length === 0) {
      setFormErrors({ sector_ids: [t('pages.validation.required_select')] });
      return;
    }

    savingRef.current = true;
    setFormErrors({});

    // Capture FormData synchronously before closing the modal
    const form = e.target;
    const formData = new FormData(form);

    sectorConfigs.forEach((config, i) => {
      formData.append(`sector_configs[${i}][sector_id]`, config.sector_id);
      (config.service_types ?? []).forEach((st, j) => {
        formData.append(`sector_configs[${i}][service_types][${j}][id]`, st.id);
        if (st.priority) {
          formData.append(`sector_configs[${i}][service_types][${j}][priority]`, st.priority);
        }
      });
    });

    const LOCATION_FIELDS = ['parish_id', 'street', 'reference_point', 'postal_code', 'latitude', 'longitude'];
    buildCreatePayload(formData, clientLocationId, locationsDirty, LOCATION_FIELDS);

    // Close modal and give immediate feedback — errors will arrive as a toast
    setShowModal(false);
    setPhotoPreview(null);
    setClientLocationId(null);
    setLocationsDirty(false);
    setSectorConfigs([]);
    globalToast.success(t('pages.service_orders.create_success'));

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
        setRefreshKey(k => k + 1);
      } else {
        const errorMsg = body.errors
          ? Object.values(body.errors).flat().join(' ')
          : (body.message ?? body.error ?? t('pages.service_orders.create_failed'));
        globalToast.error(errorMsg);
      }
    } catch {
      globalToast.error(t('pages.service_orders.unexpected_error'));
    } finally {
      savingRef.current = false;
    }
  }, [routes.store, globalToast, clientLocationId, locationsDirty, sectorConfigs]);

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
        globalToast.error(body.error || t('pages.service_orders.status_update_failed'));
      }
    } catch {
      // Rollback on network error
      setServiceOrdersState(originalState);
      globalToast.error(t('pages.service_orders.network_error'));
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
    const id = selectedServiceOrder.id;
    const prevState = serviceOrdersState;
    handleCloseDrawer();
    await mutate({
      url: `/api/service-orders/${id}/activate`,
      applyOptimistic: () => {
        setServiceOrdersState(s => applyStatusUpdate(s, id, 'in_progress'));
        return () => setServiceOrdersState(prevState);
      },
      errorMessage: t('pages.service_orders.activate_failed'),
    });
  }, [selectedServiceOrder, serviceOrdersState, handleCloseDrawer, mutate]);

  const handleComplete = useCallback(async () => {
    if (!selectedServiceOrder) return;
    const id = selectedServiceOrder.id;
    const prevState = serviceOrdersState;
    handleCloseDrawer();
    await mutate({
      url: `/api/service-orders/${id}/complete`,
      applyOptimistic: () => {
        setServiceOrdersState(s => applyStatusUpdate(s, id, 'completed'));
        return () => setServiceOrdersState(prevState);
      },
      errorMessage: t('pages.service_orders.complete_failed'),
    });
  }, [selectedServiceOrder, serviceOrdersState, handleCloseDrawer, mutate]);

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
            onClick={handleActivate}
            className="inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-semibold text-white bg-brand-accent hover:opacity-90 transition-opacity"
          >
            <Play size={12} />
            {t('pages.service_orders.btn_activate')}
          </button>
        )}
        {showComplete && (
          <button
            type="button"
            onClick={handleComplete}
            className="inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-semibold text-white bg-green-600 hover:bg-green-700 transition-colors"
          >
            <Check size={12} />
            {t('pages.service_orders.btn_complete')}
          </button>
        )}
      </>
    );
  }, [soDetail, selectedServiceOrder, can, handleActivate, handleComplete]);

  /* ── Drawer title with SO context ─────────────────────────── */
  const drawerTitle = useMemo(() => {
    const so = soDetail ?? selectedServiceOrder;
    if (!so) return null;

    return (
      <div className="flex items-center gap-4">
        <span>{so.title || so.process || `${t('pages.service_orders.drawer.os_prefix')}${so.id}`}</span>
        <span className={`inline-flex items-center px-2.5 py-0.5 text-xs font-semibold rounded-full ${badgeStyle(so.status, { border: true })}`}>
          {labelFor(so.status)}
        </span>
        <span className={`inline-flex items-center px-2.5 py-0.5 text-xs font-semibold rounded-full ${badgeStyle(so.priority, { border: true })}`}>
          {labelFor(so.priority)}
        </span>
      </div>
    );
  }, [selectedServiceOrder, soDetail]);

  /* ── Kanban Card Content Renderer ─────────────────────────── */
  const renderCardContent = useCallback((item) => {
    const managerInitial = item.manager?.name?.split(' ')[0]?.[0]?.toUpperCase() || '?';

    return (
      <>
        {/* Process ID */}
        <p className="text-brand-accent font-mono font-bold text-xs mb-1">
          {item.process}
        </p>

        {/* Title / Description */}
        {(item.title || item.description) && (
          <h4 className="text-sm font-semibold text-brand-darkest mb-2 line-clamp-2">
            {item.title || item.description}
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
      <Modal
        formSchema={createFormSchema}
        routes={routes}
        size="lg"
        open={showModal}
        onClose={() => setShowModal(false)}
        onSubmit={handleCreate}
        injectAfterField={[
          {
            fieldKey: 'client_id',
            content: (
              <ClientLocationSelector
                isOpen={showModal}
                clientId={currentClientId}
                onClientLocationChange={setClientLocationId}
                onDirtyChange={setLocationsDirty}
              />
            ),
          },
          {
            fieldKey: 'manager_id',
            content: (
              <SectorConfigPanel
                isOpen={showModal}
                serviceTypesBySector={serviceTypesBySector}
                onChange={(configs) => { setSectorConfigs(configs); if (configs.length > 0) setFormErrors(prev => { const { sector_ids, ...rest } = prev; return rest; }); }}
                error={formErrors.sector_ids ? (Array.isArray(formErrors.sector_ids) ? formErrors.sector_ids[0] : formErrors.sector_ids) : undefined}
              />
            ),
          },
        ]}
        externalErrors={formErrors}
      />

      {/* Hidden file change handler — wire via DOM delegation */}
      {showModal && (
        <PhotoListener inputSelector="input[name='photo']" onFileChange={onFileChange} />
      )}

      {/* Unified View with DataManager handling toggles */}
      <DataManager
        title={t('pages.service_orders.dm_title')}
        entityName={t('pages.service_orders.dm_entity_name')}
        items={serviceOrdersState}
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
        refreshKey={refreshKey}
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
        subtitle={(soDetail ?? selectedServiceOrder)?.client?.name ? `${t('pages.service_orders.drawer_client_label')} ${(soDetail ?? selectedServiceOrder).client.name}` : null}
        tabs={soTabs}
        headerActions={drawerActions}
      />
    </AppLayout>
  );
}

function applyStatusUpdate(state, id, newStatus) {
  const items = Array.isArray(state) ? state : (state?.data ?? []);
  const updated = items.map(so =>
    String(so.id) === String(id) ? { ...so, status: newStatus } : so
  );
  return Array.isArray(state) ? updated : { ...state, data: updated };
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

      {/* Sectors with per-sector priority and service types */}
      {so.sectors?.length > 0 && (
        <section>
          <h4 className="text-sm font-semibold text-brand-mid uppercase tracking-wider mb-2">{t('pages.service_orders.section_sectors')}</h4>
          <div className="space-y-2">
            {so.sectors.map((s) => (
              <div key={s.id} className="rounded-md border border-brand-mid/20 px-3 py-2">
                <div className="flex items-center justify-between gap-2 mb-1">
                  <span className="text-sm font-medium text-brand-darkest">{s.name}</span>
                  {s.priority && (
                    <span className={`text-xs font-medium px-2 py-0.5 rounded ${{
                      low:    'bg-blue-500/20 text-blue-300',
                      normal: 'bg-brand-mid/20 text-brand-mid',
                      high:   'bg-orange-500/20 text-orange-300',
                      urgent: 'bg-red-500/20 text-red-300',
                    }[s.priority] || 'bg-brand-mid/20 text-brand-mid'}`}>
                      {labelFor(s.priority)}
                    </span>
                  )}
                </div>
                {s.service_types?.length > 0 && (
                  <div className="flex flex-wrap gap-1 mt-1">
                    {s.service_types.map(st => (
                      <span key={st.id} className="text-xs px-1.5 py-0.5 rounded bg-brand-mid/10 text-brand-mid border border-brand-mid/20">
                        {st.name}
                      </span>
                    ))}
                  </div>
                )}
              </div>
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

