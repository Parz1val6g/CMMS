import { useState, useEffect, useCallback, useRef, useMemo } from 'react';
import { usePage } from '@inertiajs/react';
import { useToast } from '@/Components/Toast/ToastContext';
import { MapPin, Clock } from 'lucide-react';
import AppLayout from '@/Layouts/AppLayout';
import DataManager from '@/Components/DataManager';
import Modal from '@/Components/Common/Modal';
import KanbanBoard from '@/Components/Kanban/KanbanBoard';
import WorkspaceDrawer from '@/Components/Drawer/WorkspaceDrawer';
import SOMaterialsList from '../Components/DrawerTabs/MaterialsList';
import SOTasksTree from '../Components/DrawerTabs/TasksTree';

export default function ServiceOrdersIndex({ service_orders, columns, formSchema, createFormSchema, routes, filterSchema }) {
  const [showModal, setShowModal] = useState(false);
  const [formErrors, setFormErrors] = useState({});
  const [photoPreview, setPhotoPreview] = useState(null);
  const [viewMode, setViewMode] = useState('table'); // 'table' or 'kanban'
  const [serviceOrdersState, setServiceOrdersState] = useState(service_orders);
  const [drawerOpen, setDrawerOpen] = useState(false);
  const [selectedServiceOrder, setSelectedServiceOrder] = useState(null);
  const [toast, setToast] = useState(null);
  const savingRef = useRef(false);
  const { flash } = usePage().props;
  const globalToast = useToast();

  const breadcrumbs = [
    { name: 'Dashboard', url: '/dashboard' },
    { name: 'Service Orders', url: '/service-orders' },
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
  const handleCreate = useCallback(async (e) => {
    e.preventDefault();
    if (!routes.store || savingRef.current) return;
    savingRef.current = true;
    setFormErrors({});

    const form = e.target;
    const formData = new FormData(form);

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
        else globalToast.error(body.error ?? 'Falha ao criar ordem de serviço.');
      }
    } catch {
      globalToast.error('Ocorreu um erro inesperado.');
    } finally {
      savingRef.current = false;
    }
  }, [routes.store, globalToast]);

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

  /* ── Render validation errors ─────────────────────────────── */
  const errorList = Object.values(formErrors).flat();

  /* ── Kanban Status Columns Configuration ──────────────────── */
  const statusColumns = [
    { id: 'pending', label: 'Pending', color: 'bg-yellow-500/20 text-yellow-300 border-yellow-500' },
    { id: 'in_progress', label: 'In Progress', color: 'bg-blue-500/20 text-blue-300 border-blue-500' },
    { id: 'completed', label: 'Completed', color: 'bg-green-500/20 text-green-300 border-green-500' },
    { id: 'cancelled', label: 'Cancelled', color: 'bg-red-500/20 text-red-300 border-red-500' },
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
          error: body.error || 'Failed to update service order status. Changes reverted.',
        });
      }
    } catch {
      // Rollback on network error
      setServiceOrdersState(originalState);
      setToast({
        error: 'Network error updating service order. Changes reverted.',
      });
    }
  }, [serviceOrdersState, routes.update]);

  /* ── Open drawer with selected Service Order ──────────────── */
  const handleCardClick = useCallback((item) => {
    setSelectedServiceOrder(item);
    setDrawerOpen(true);
  }, []);

  /* ── Close drawer ─────────────────────────────────────────── */
  const handleCloseDrawer = useCallback(() => {
    setDrawerOpen(false);
    // Keep selectedServiceOrder for a smooth close transition
  }, []);

  /* ── Build tabs array for WorkspaceDrawer ─────────────────── */
  const soTabs = useMemo(() => {
    const so = selectedServiceOrder;
    if (!so) return [];

    const showMaterials = so.workflow_type === 'loan';

    return [
      { id: 'details', label: 'Details', component: <SODetailsTab serviceOrder={so} /> },
      { id: 'tasks', label: 'Tasks', component: <SOTasksTree serviceOrderId={so.id} workflowType={so.workflow_type} /> },
      ...(showMaterials ? [{ id: 'materials', label: 'Materials', component: <SOMaterialsList serviceOrder={so} /> }] : []),
      { id: 'work_logs', label: 'Work Logs', component: <div className="flex items-center justify-center h-40 text-slate-500"><p className="text-sm">Work logs — coming soon</p></div> },
    ];
  }, [selectedServiceOrder]);

  /* ── Drawer title with SO context ─────────────────────────── */
  const drawerTitle = useMemo(() => {
    const so = selectedServiceOrder;
    if (!so) return null;

    const statusStyles = {
      pending: 'bg-yellow-500/20 text-yellow-300 border border-yellow-500/40',
      in_progress: 'bg-blue-500/20  text-blue-300  border border-blue-500/40',
      completed: 'bg-green-500/20  text-green-300  border border-green-500/40',
      cancelled: 'bg-red-500/20    text-red-300    border border-red-500/40',
    };
    const priorityStyles = {
      low: 'bg-blue-500/20    text-blue-300    border border-blue-500/40',
      normal: 'bg-slate-500/20   text-slate-300   border border-slate-500/40',
      high: 'bg-orange-500/20  text-orange-300  border border-orange-500/40',
      urgent: 'bg-red-500/20     text-red-300     border border-red-500/40',
    };

    return (
      <div className="flex items-center gap-4">
        <span>{so.process || `OS/${so.id}`}</span>
        <span className={`inline-flex items-center px-2.5 py-0.5 text-xs font-semibold rounded-full ${statusStyles[so.status] || statusStyles.pending}`}>
          {so.status?.replace('_', ' ') || 'Pending'}
        </span>
        <span className={`inline-flex items-center px-2.5 py-0.5 text-xs font-semibold rounded-full ${priorityStyles[so.priority] || priorityStyles.normal}`}>
          {so.priority || 'Normal'}
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
            <span className="font-medium">Client:</span> {item.client.name}
          </p>
        )}

        {/* Location & Date */}
        <div className="space-y-1 mb-3">
          {item.location && (
            <div className="flex items-start gap-2 text-xs text-slate-400">
              <MapPin className="h-3 w-3 mt-0.5 flex-shrink-0 text-indigo-400" />
              <span className="line-clamp-1">
                {item.location.street || item.location.landmark || 'Unknown'}
              </span>
            </div>
          )}
          {item.execution_date && (
            <div className="flex items-center gap-2 text-xs text-slate-400">
              <Clock className="h-3 w-3 flex-shrink-0 text-indigo-400" />
              <span>{item.execution_date}</span>
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
            {item.priority || 'Normal'}
          </span>
          <div className="w-6 h-6 rounded-full bg-indigo-600 text-white flex items-center justify-center text-xs font-bold">
            {managerInitial}
          </div>
        </div>
      </>
    );
  }, []);

  return (
    <AppLayout title="Service Orders Management" breadcrumbs={breadcrumbs}>
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
            Dismiss
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
        {/* Validation errors */}
        {errorList.length > 0 && (
          <div className="mb-4 rounded-lg bg-red-50 p-3 text-sm text-red-700 dark:bg-red-900/30 dark:text-red-300">
            {errorList.map((msg, i) => (
              <p key={i}>{msg}</p>
            ))}
          </div>
        )}

        {/* Photo preview */}
        {photoPreview && (
          <div className="mb-4 overflow-hidden rounded-lg border border-gray-200 dark:border-gray-700">
            <img
              src={photoPreview}
              alt="Photo preview"
              className="h-40 w-full object-cover"
            />
          </div>
        )}
      </Modal>

      {/* Hidden file change handler — wire via DOM delegation */}
      {showModal && (
        <PhotoListener onFileChange={onFileChange} />
      )}

      {/* Unified View with DataManager handling toggles */}
      <DataManager
        title="Service Orders"
        entityName="Service Order"
        items={service_orders}
        routes={routes}
        columns={columns}
        formSchema={formSchema}
        filterSchema={filterSchema ?? []}
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
        subtitle={selectedServiceOrder?.client?.name ? `Client: ${selectedServiceOrder.client.name}` : null}
        tabs={soTabs}
      />
    </AppLayout>
  );
}

/* ── Details tab content for Service Order ────────────────────── */
function SODetailsTab({ serviceOrder }) {
  const so = serviceOrder;
  if (!so) return null;

  return (
    <div className="space-y-6">
      {so.description && (
        <section>
          <h4 className="text-sm font-semibold text-slate-400 uppercase tracking-wider mb-2">Description</h4>
          <p className="text-sm text-slate-200">{so.description}</p>
        </section>
      )}

      {so.location && (
        <section>
          <h4 className="text-sm font-semibold text-slate-400 uppercase tracking-wider mb-2">Location</h4>
          <p className="text-sm text-slate-200">
            {so.location.street || so.location.landmark || '—'}
          </p>
        </section>
      )}

      {so.execution_date && (
        <section>
          <h4 className="text-sm font-semibold text-slate-400 uppercase tracking-wider mb-2">Execution Date</h4>
          <p className="text-sm text-slate-200">{so.execution_date}</p>
        </section>
      )}

      {so.manager && (
        <section>
          <h4 className="text-sm font-semibold text-slate-400 uppercase tracking-wider mb-2">Manager</h4>
          <p className="text-sm text-slate-200">{so.manager.name}</p>
        </section>
      )}

      {so.service_type && (
        <section>
          <h4 className="text-sm font-semibold text-slate-400 uppercase tracking-wider mb-2">Service Type</h4>
          <p className="text-sm text-slate-200">{so.service_type.name || so.service_type}</p>
        </section>
      )}
    </div>
  );
}

/* ── Photo file listener (renders outside Modal to avoid re-render issues) ── */
function PhotoListener({ onFileChange }) {
  useEffect(() => {
    const el = document.querySelector('input[name="photo"]');
    if (!el) return;
    el.addEventListener('change', onFileChange);
    return () => el.removeEventListener('change', onFileChange);
  }, [onFileChange]);
  return null;
}
