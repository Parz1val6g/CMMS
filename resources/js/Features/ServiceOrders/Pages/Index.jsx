import { useState, useEffect, useCallback, useRef, useMemo } from 'react';
import { labelFor, badgeStyle } from '@/utils/enums';
import { formatDate } from '@/utils/format';
import { usePage } from '@inertiajs/react';
import { useToast } from '@/Components/Toast/ToastContext';
import { MapPin, Clock, Loader2, AlertCircle } from 'lucide-react';
import { t } from '@/utils/i18n';
import AppLayout from '@/Layouts/AppLayout';
import DataManager from '@/Components/DataManager';
import Modal from '@/Components/Common/Modal';
import PhotoListener from '@/Hooks/usePhotoListener';
import KanbanBoard from '@/Components/Kanban/KanbanBoard';
import WorkspaceDrawer from '@/Components/Drawer/WorkspaceDrawer';
import SOTasksTree from '../Components/DrawerTabs/TasksTree';
import { useClientLocations } from '@/Hooks/useClientLocations';

export default function ServiceOrdersIndex({ service_orders, columns, formSchema, createFormSchema, routes, filterSchema, advancedFilterFields}) {
  const [showModal, setShowModal] = useState(false);
  const [formErrors, setFormErrors] = useState({});
  const [photoPreview, setPhotoPreview] = useState(null);
  const [clientLocationId, setClientLocationId] = useState('');
  const [viewMode, setViewMode] = useState('table'); // 'table' or 'kanban'
  const [serviceOrdersState, setServiceOrdersState] = useState(service_orders);
  const [drawerOpen, setDrawerOpen] = useState(false);
  const [selectedServiceOrder, setSelectedServiceOrder] = useState(null);
  const [soDetail, setSoDetail] = useState(null);
  const [soLoading, setSoLoading] = useState(false);
  const [soError, setSoError] = useState(null);
  const [toast, setToast] = useState(null);
  const savingRef = useRef(false);
  const { flash } = usePage().props;
  const globalToast = useToast();

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

  /* ── Create form submission via FormData (multipart) ─────── */
  const handleCreate = useCallback(async (e, formValues = {}) => {
    e.preventDefault();
    if (!routes.store || savingRef.current) return;
    savingRef.current = true;
    setFormErrors({});

    const form = e.target;
    const formData = new FormData(form);

    // Append React-controlled multi-select values (not captured by FormData)
    ['sector_ids', 'equipment_ids'].forEach((key) => {
      const vals = formValues[key];
      if (Array.isArray(vals) && vals.length > 0) {
        vals.forEach((v) => formData.append(`${key}[]`, v));
      }
    });

    // Append selected client location if any
    if (clientLocationId) {
      formData.append('client_location_id', clientLocationId);
    }

    const token = document.querySelector('meta[name="csrf-token"]')?.content;

    try {
      const res = await fetch(routes.store, {
        method: 'POST',
        headers: {
          'Accept': 'application/json',
          'X-CSRF-TOKEN': token ?? '',
          'X-Requested-With': 'XMLHttpRequest',
        },
        body: formData,
      });
      const body = await res.json();

      if (res.ok) {
        setShowModal(false);
        setPhotoPreview(null);
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
  }, [routes.store, globalToast]);

  /* ── Open modal for create ────────────────────────────────── */
  const openCreate = useCallback(() => {
    setFormErrors({});
    setPhotoPreview(null);
    setClientLocationId('');
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
    const token = document.querySelector('meta[name="csrf-token"]')?.content;
    try {
      const res = await fetch(`${routes.update.replace(':id', serviceOrderId)}`, {
        method: 'PATCH',
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': token ?? '',
          'X-Requested-With': 'XMLHttpRequest',
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
      const token = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
      const res = await fetch(`/api/service-orders/${item.id}`, {
        headers: { Accept: 'application/json', 'X-CSRF-TOKEN': token, 'X-Requested-With': 'XMLHttpRequest' },
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
          <div className="flex items-center justify-center h-40 text-slate-400 gap-2">
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
        label: 'Error',
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
    const isLoan = data.workflow_type === 'loan';

    const tabs = [
      { id: 'details', label: t('pages.service_orders.tab_details'), component: <SODetailsTab serviceOrder={data} /> },
      { id: 'tasks', label: t('pages.service_orders.tab_tasks'), component: <SOTasksTree serviceOrderId={so.id} workflowType={data.workflow_type} /> },
    ];

    // Equipment tab — loan only
    if (isLoan) {
      tabs.push({ id: 'equipment', label: t('pages.service_orders.tab_equipment'), component: <SOEquipmentTab serviceOrder={data} /> });
    }

    return tabs;
  }, [selectedServiceOrder, soDetail, soLoading, soError]);

  /* ── Drawer title with SO context ─────────────────────────── */
  const drawerTitle = useMemo(() => {
    const so = selectedServiceOrder;
    if (!so) return null;

    return (
      <div className="flex items-center gap-4">
        <span>{so.process || `OS/${so.id}`}</span>
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
        <p className="text-indigo-400 font-mono font-bold text-xs mb-1">
          {item.process}
        </p>

        {/* Title/Description */}
        {item.description && item.description.trim() && (
          <h4 className="text-sm font-semibold text-slate-100 mb-2 line-clamp-2">
            {item.description}
          </h4>
        )}

        {/* Client */}
        {item.client && (
          <p className="text-xs text-slate-300 mb-2">
            <span className="font-medium">{t('pages.service_orders.card_client_label')}</span> {item.client.name}
          </p>
        )}

        {/* Location & Date */}
        <div className="space-y-1 mb-3">
          {item.location && (
            <div className="flex items-start gap-2 text-xs text-slate-400">
              <MapPin className="h-3 w-3 mt-0.5 flex-shrink-0 text-indigo-400" />
              <span className="line-clamp-1">
                {item.location.street || item.location.landmark || t('pages.service_orders.card_unknown_location')}
              </span>
            </div>
          )}
          {item.execution_date && (
            <div className="flex items-center gap-2 text-xs text-slate-400">
              <Clock className="h-3 w-3 flex-shrink-0 text-indigo-400" />
              <span>{formatDate(item.execution_date)}</span>
            </div>
          )}
        </div>

        {/* Footer: Priority Badge + Manager Avatar */}
        <div className="flex items-center justify-between">
          <span className={`text-xs font-medium px-2 py-1 rounded ${{
              low: 'bg-blue-500/20 text-blue-300',
              normal: 'bg-slate-500/20 text-slate-300',
              high: 'bg-orange-500/20 text-orange-300',
              urgent: 'bg-red-500/20 text-red-300',
            }[item.priority] || 'bg-slate-500/20 text-slate-300'
            }`}>
            {labelFor(item.priority)}
          </span>
          <div
            className="w-6 h-6 rounded-full bg-indigo-600 text-white flex items-center justify-center text-xs font-bold"
            title={item.manager?.name ? `Atribuído a: ${item.manager.name}` : 'Sem responsável'}
            aria-label={item.manager?.name ? `Atribuído a: ${item.manager.name}` : 'Sem responsável'}
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
            ? 'bg-green-50 text-green-700 dark:bg-green-900/30 dark:text-green-300'
            : 'bg-red-50 text-red-700 dark:bg-red-900/30 dark:text-red-300'
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
      >
        <ClientLocationSelector
          isOpen={showModal}
          onClientLocationChange={setClientLocationId}
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
      {/* Process & Status row */}
      <div className="grid grid-cols-2 gap-4">
        <section>
          <h4 className="text-sm font-semibold text-slate-400 uppercase tracking-wider mb-2">{t('pages.service_orders.section_process')}</h4>
          <p className="text-sm font-mono text-indigo-400 font-bold">{so.process || `OS/${so.id}`}</p>
        </section>
        <section>
          <h4 className="text-sm font-semibold text-slate-400 uppercase tracking-wider mb-2">{t('pages.service_orders.section_status')}</h4>
          <span className={`inline-flex items-center px-2.5 py-0.5 text-xs font-semibold rounded-full ${badgeStyle(so.status)}`}>
            {labelFor(so.status)}
          </span>
        </section>
      </div>

      {/* Priority & Workflow row */}
      <div className="grid grid-cols-2 gap-4">
        <section>
          <h4 className="text-sm font-semibold text-slate-400 uppercase tracking-wider mb-2">{t('pages.service_orders.section_priority')}</h4>
          <span className={`inline-flex items-center px-2.5 py-0.5 text-xs font-semibold rounded-full ${badgeStyle(so.priority)}`}>
            {labelFor(so.priority)}
          </span>
        </section>
        <section>
          <h4 className="text-sm font-semibold text-slate-400 uppercase tracking-wider mb-2">{t('pages.service_orders.section_workflow')}</h4>
          <span className="inline-flex items-center px-2.5 py-0.5 text-xs font-semibold rounded-full bg-indigo-500/20 text-indigo-300">
            {labelFor(so.workflow_type)}
          </span>
        </section>
      </div>

      {/* Description */}
      {so.description && (
        <section>
          <h4 className="text-sm font-semibold text-slate-400 uppercase tracking-wider mb-2">{t('pages.service_orders.section_description')}</h4>
          <p className="text-sm text-slate-200">{so.description}</p>
        </section>
      )}

      {/* Client */}
      {so.client && (
        <section>
          <h4 className="text-sm font-semibold text-slate-400 uppercase tracking-wider mb-2">{t('pages.service_orders.section_client')}</h4>
          <p className="text-sm text-slate-200">{so.client.name}</p>
          {so.client.nif && <p className="text-xs text-slate-400 mt-0.5">NIF: {so.client.nif}</p>}
        </section>
      )}

      {/* Manager */}
      {so.manager && (
        <section>
          <h4 className="text-sm font-semibold text-slate-400 uppercase tracking-wider mb-2">{t('pages.service_orders.section_manager')}</h4>
          <p className="text-sm text-slate-200">{so.manager.name}</p>
        </section>
      )}

      {/* Location */}
      {so.location && (
        <section>
          <h4 className="text-sm font-semibold text-slate-400 uppercase tracking-wider mb-2">{t('pages.service_orders.section_location')}</h4>
          <p className="text-sm text-slate-200">
            {so.location.street || so.location.landmark || t('pages.service_orders.value_missing')}
          </p>
          {so.location.parish && (
            <p className="text-xs text-slate-400 mt-0.5">
              {so.location.parish.name}
              {so.location.parish.municipality && <> · {so.location.parish.municipality.name}</>}
              {so.location.parish.municipality?.district && <> · {so.location.parish.municipality.district.name}</>}
            </p>
          )}
        </section>
      )}

      {/* Service Type */}
      {so.service_type && (
        <section>
          <h4 className="text-sm font-semibold text-slate-400 uppercase tracking-wider mb-2">{t('pages.service_orders.section_service_type')}</h4>
          <p className="text-sm text-slate-200">{so.service_type.name || so.service_type}</p>
        </section>
      )}

      {/* Execution Date */}
      {so.execution_date && (
        <section>
          <h4 className="text-sm font-semibold text-slate-400 uppercase tracking-wider mb-2">{t('pages.service_orders.section_execution_date')}</h4>
          <p className="text-sm text-slate-200">{formatDate(so.execution_date)}</p>
        </section>
      )}

      {/* Sectors */}
      {so.sectors?.length > 0 && (
        <section>
          <h4 className="text-sm font-semibold text-slate-400 uppercase tracking-wider mb-2">{t('pages.service_orders.section_sectors')}</h4>
          <div className="flex flex-wrap gap-1.5">
            {so.sectors.map((s) => (
              <span key={s.id} className="inline-flex items-center px-2 py-0.5 text-xs rounded-full bg-slate-700/50 text-slate-300 border border-slate-600/50">
                {s.name}
              </span>
            ))}
          </div>
        </section>
      )}

      {/* Photo */}
      {so.photo_url && (
        <section>
          <h4 className="text-sm font-semibold text-slate-400 uppercase tracking-wider mb-2">{t('pages.service_orders.section_photo')}</h4>
          <img
            src={so.photo_url}
            alt={t('pages.service_orders.photo_preview_alt')}
            className="h-40 w-full object-cover rounded-lg border border-slate-700/50"
          />
        </section>
      )}
    </div>
  );
}

/* ── Single equipment card (used within SOEquipmentTab) ─────────── */
function EquipmentCard({ eq, index }) {
  const eqBadgeStyle = {
    active:              'bg-green-500/20  text-green-300',
    in_use:              'bg-blue-500/20   text-blue-300',
    maintenance_pending: 'bg-yellow-500/20 text-yellow-300',
    under_maintenance:   'bg-orange-500/20 text-orange-300',
    broken:              'bg-red-500/20    text-red-300',
    under_repair:        'bg-purple-500/20 text-purple-300',
    inactive:            'bg-slate-500/20  text-slate-300',
    retired:             'bg-gray-500/20   text-gray-300',
  };

  return (
    <div className="rounded-lg border border-slate-700/50 bg-slate-800/40 p-4 space-y-4">
      {/* Header */}
      <div className="flex items-start justify-between">
        <div>
          <h4 className="text-sm font-semibold text-slate-400 uppercase tracking-wider mb-1">{t('pages.service_orders.section_equipment')} #{index + 1}</h4>
          <p className="text-sm font-mono text-cyan-400 font-bold">{eq.name}</p>
        </div>
        <span className={`inline-flex items-center px-2.5 py-0.5 text-xs font-semibold rounded-full ${eqBadgeStyle[eq.status] || eqBadgeStyle.active}`}>
          {labelFor(eq.status)}
        </span>
      </div>

      {/* Brand & Model */}
      <div className="grid grid-cols-2 gap-4">
        <section>
          <h4 className="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">{t('pages.service_orders.section_brand')}</h4>
          <p className="text-sm text-slate-200">{eq.brand || t('pages.service_orders.value_missing')}</p>
        </section>
        <section>
          <h4 className="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">{t('pages.service_orders.section_model')}</h4>
          <p className="text-sm text-slate-200">{eq.model || t('pages.service_orders.value_missing')}</p>
        </section>
      </div>

      {/* Serial Number */}
      <section>
        <h4 className="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">{t('pages.service_orders.section_serial_number')}</h4>
        <p className="text-sm font-mono text-slate-200">{eq.serial_number || t('pages.service_orders.value_missing')}</p>
      </section>

      {/* Revisions */}
      <div className="grid grid-cols-2 gap-4">
        <section>
          <h4 className="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">{t('pages.service_orders.section_last_revision')}</h4>
          <p className="text-sm text-slate-200">{eq.last_revision_date ? formatDate(eq.last_revision_date) : t('pages.service_orders.value_missing')}</p>
        </section>
        <section>
          <h4 className="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">{t('pages.service_orders.section_next_revision')}</h4>
          <p className="text-sm text-slate-200">{eq.next_revision_date ? formatDate(eq.next_revision_date) : t('pages.service_orders.value_missing')}</p>
        </section>
      </div>

      {/* Manager */}
      {eq.manager && (
        <section>
          <h4 className="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">{t('pages.service_orders.section_manager')}</h4>
          <p className="text-sm text-slate-200">{eq.manager.name}</p>
        </section>
      )}

      {/* Description */}
      {eq.description && (
        <section>
          <h4 className="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">{t('pages.service_orders.section_description')}</h4>
          <p className="text-sm text-slate-200">{eq.description}</p>
        </section>
      )}
    </div>
  );
}

/* ── Cascading client → location selector rendered inside the create Modal ── */
function ClientLocationSelector({ isOpen, onClientLocationChange }) {
  const [clientId, setClientId] = useState('');
  const [selectedId, setSelectedId] = useState('');
  const { locations } = useClientLocations(clientId || null);

  // Detect client_id changes from Modal's updateValue via custom event
  useEffect(() => {
    if (!isOpen) {
      setClientId('');
      setSelectedId('');
      return;
    }

    const handler = (e) => {
      if (e.detail.name === 'client_id') {
        setClientId(e.detail.value || '');
        setSelectedId('');
        onClientLocationChange('');
      }
    };

    document.addEventListener('modal-field-change', handler);
    return () => document.removeEventListener('modal-field-change', handler);
  }, [isOpen, onClientLocationChange]);

  const handleChange = (e) => {
    const id = e.target.value;
    setSelectedId(id);
    onClientLocationChange(id);

    if (!id) return;

    const cl = locations.find(l => l.id === id);
    if (cl?.location) {
      document.dispatchEvent(new CustomEvent('autofill-location', {
        detail: {
          parish_id:       cl.location.parish_id       ?? '',
          street:          cl.location.street_address  ?? '',
          reference_point: cl.location.landmark        ?? '',
          postal_code:     cl.location.postal_code     ?? '',
          latitude:        cl.location.latitude        ?? '',
          longitude:       cl.location.longitude       ?? '',
        },
      }));
    }
  };

  if (!isOpen || !clientId || locations.length === 0) return null;

  return (
    <div className="rounded-lg border border-indigo-500/30 bg-indigo-950/20 p-3 space-y-1.5">
      <label className="block text-xs font-semibold text-indigo-300 uppercase tracking-wider">
        {t('pages.service_orders.job_site_label')}
      </label>
      <select
        value={selectedId}
        onChange={handleChange}
        className="w-full rounded-lg bg-slate-700 border border-slate-600 text-slate-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
      >
        <option value="">— {t('pages.service_orders.job_site_none')} —</option>
        {locations.map(cl => (
          <option key={cl.id} value={cl.id}>
            {cl.is_primary ? '★ ' : ''}{cl.name}
            {cl.location?.street_address ? ` — ${cl.location.street_address}` : ''}
          </option>
        ))}
      </select>
      <p className="text-xs text-slate-500">{t('pages.service_orders.job_site_helper')}</p>
    </div>
  );
}

/* ── Equipment tab content (loan-only) — renders a list of equipments ── */
function SOEquipmentTab({ serviceOrder }) {
  const eqList = serviceOrder?.equipments;
  if (!eqList || eqList.length === 0) {
    return (
      <div className="flex items-center justify-center h-40 text-slate-500">
        <p className="text-sm">{t('pages.service_orders.no_equipment_assigned')}</p>
      </div>
    );
  }

  return (
    <div className="space-y-4">
      {eqList.map((eq, i) => (
        <EquipmentCard key={eq.id} eq={eq} index={i} />
      ))}
    </div>
  );
}

