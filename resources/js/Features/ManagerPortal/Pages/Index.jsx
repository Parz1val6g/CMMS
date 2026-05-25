import { useState, useCallback, useMemo, useEffect } from 'react';
import AppLayout from '@/Layouts/AppLayout';
import Modal from '@/Components/Common/Modal';
import WorkspaceDrawer from '@/Components/Drawer/WorkspaceDrawer';
import SOTasksTree from '@/Components/Shared/SOTasksTree';
import ClientLocationSelector from '@/Components/Shared/ClientLocationSelector';
import { buildCreatePayload } from '@/utils/serviceOrderPayload';
import { labelFor, badgeStyle } from '@/utils/enums';
import { formatDate } from '@/utils/format';
import { csrfHeader } from '@/utils/csrf';
import { t } from '@/utils/i18n';
import { Plus, FileText, ChevronRight, Zap, CheckCircle, XCircle, Clock, AlertTriangle } from 'lucide-react';

/* ── Status badge ─────────────────────────────────────────────────────── */
const STATUS_BADGE = {
  pending:            'bg-yellow-50 text-yellow-800 ring-1 ring-inset ring-yellow-300/70',
  in_progress:        'bg-blue-50 text-blue-800 ring-1 ring-inset ring-blue-300/70',
  awaiting_approval:  'bg-orange-50 text-orange-800 ring-1 ring-inset ring-orange-300/70',
  completed:          'bg-green-50 text-green-800 ring-1 ring-inset ring-green-300/70',
  cancelled:          'bg-red-50 text-red-800 ring-1 ring-inset ring-red-300/70',
};

function StatusBadge({ value }) {
  const cls = STATUS_BADGE[value] ?? 'bg-gray-100 text-gray-600 ring-1 ring-inset ring-gray-300/70';
  return (
    <span className={`inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium ${cls}`}>
      {labelFor(value)}
    </span>
  );
}

/* ── Priority badge ───────────────────────────────────────────────────── */
const PRIORITY_DOT = {
  urgent: 'bg-red-500',
  high:   'bg-orange-400',
  normal: 'bg-blue-400',
  low:    'bg-gray-300',
};

function PriorityDot({ value }) {
  return (
    <span className="flex items-center gap-1.5 text-xs text-gray-600">
      <span className={`inline-block h-2 w-2 rounded-full ${PRIORITY_DOT[value] ?? 'bg-gray-300'}`} />
      {labelFor(value)}
    </span>
  );
}

/* ── Stats card ───────────────────────────────────────────────────────── */
function StatCard({ label, value, icon: Icon, color }) {
  return (
    <div className="bg-white rounded-xl border border-gray-200 shadow-sm p-5 flex items-center gap-4">
      <div className={`flex items-center justify-center h-10 w-10 rounded-lg ${color}`}>
        <Icon size={18} className="text-white" />
      </div>
      <div>
        <p className="text-2xl font-bold text-gray-900 tabular-nums">{value ?? 0}</p>
        <p className="text-xs text-gray-500 mt-0.5">{label}</p>
      </div>
    </div>
  );
}

/* ── Drawer content ───────────────────────────────────────────────────── */
function DrawerDetails({ so, routes, onAction }) {
  const [busy, setBusy] = useState(null);
  const [err, setErr] = useState(null);

  const action = useCallback(async (endpoint, method = 'POST') => {
    setBusy(endpoint);
    setErr(null);
    try {
      const res = await fetch(endpoint, {
        method,
        headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest', ...csrfHeader() },
      });
      if (!res.ok) {
        const body = await res.json().catch(() => ({}));
        throw new Error(body.message ?? t('pages.manager_portal.action_error'));
      }
      onAction();
    } catch (e) {
      setErr(e.message);
    } finally {
      setBusy(null);
    }
  }, [onAction]);

  const activateUrl = routes.activate.replace('__ID__', so.id);
  const completeUrl = routes.complete.replace('__ID__', so.id);
  const cancelUrl   = routes.cancel.replace('__ID__', so.id);

  return (
    <div className="flex flex-col gap-5">
      {/* Meta */}
      <div className="grid grid-cols-2 gap-3 text-sm">
        <div>
          <p className="text-xs text-gray-400 mb-0.5">{t('pages.manager_portal.drawer_status')}</p>
          <StatusBadge value={so.status} />
        </div>
        <div>
          <p className="text-xs text-gray-400 mb-0.5">{t('pages.manager_portal.drawer_priority')}</p>
          <PriorityDot value={so.priority} />
        </div>
        {so.client?.name && (
          <div>
            <p className="text-xs text-gray-400 mb-0.5">{t('pages.manager_portal.drawer_client')}</p>
            <p className="text-gray-700 font-medium">{so.client.name}</p>
          </div>
        )}
        {so.execution_date && (
          <div>
            <p className="text-xs text-gray-400 mb-0.5">{t('pages.manager_portal.drawer_execution_date')}</p>
            <p className="text-gray-700">{formatDate(so.execution_date)}</p>
          </div>
        )}
        {so.service_type?.name && (
          <div className="col-span-2">
            <p className="text-xs text-gray-400 mb-0.5">{t('pages.manager_portal.drawer_service_type')}</p>
            <p className="text-gray-700">{so.service_type.name}</p>
          </div>
        )}
        {so.description && (
          <div className="col-span-2">
            <p className="text-xs text-gray-400 mb-0.5">{t('pages.manager_portal.drawer_description')}</p>
            <p className="text-gray-600 text-xs leading-relaxed">{so.description}</p>
          </div>
        )}
      </div>

      {/* Actions */}
      <div className="flex flex-wrap gap-2">
        {so.status === 'pending' && (
          <button
            disabled={!!busy}
            onClick={() => action(activateUrl)}
            className="flex items-center gap-1.5 text-xs font-semibold px-3 py-1.5 rounded-lg bg-blue-600 text-white hover:bg-blue-700 disabled:opacity-50 transition-colors"
          >
            <Zap size={13} />
            {t('pages.manager_portal.btn_activate')}
          </button>
        )}
        {so.status === 'awaiting_approval' && (
          <button
            disabled={!!busy}
            onClick={() => action(completeUrl)}
            className="flex items-center gap-1.5 text-xs font-semibold px-3 py-1.5 rounded-lg bg-green-600 text-white hover:bg-green-700 disabled:opacity-50 transition-colors"
          >
            <CheckCircle size={13} />
            {t('pages.manager_portal.btn_complete')}
          </button>
        )}
        {!['completed', 'cancelled'].includes(so.status) && (
          <button
            disabled={!!busy}
            onClick={() => action(cancelUrl)}
            className="flex items-center gap-1.5 text-xs font-semibold px-3 py-1.5 rounded-lg bg-red-50 text-red-700 hover:bg-red-100 disabled:opacity-50 transition-colors ring-1 ring-inset ring-red-200"
          >
            <XCircle size={13} />
            {t('pages.manager_portal.btn_cancel')}
          </button>
        )}
      </div>

      {err && (
        <p className="text-xs text-red-600 flex items-center gap-1">
          <AlertTriangle size={12} /> {err}
        </p>
      )}

      {/* Tasks tree */}
      <div>
        <p className="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">{t('pages.manager_portal.section_tasks')}</p>
        <SOTasksTree serviceOrderId={so.id} />
      </div>
    </div>
  );
}

/* ── Table columns ────────────────────────────────────────────────────── */
const COLUMNS = [
  { key: 'process',       label: t('pages.manager_portal.col_process') },
  { key: 'status',        label: t('pages.manager_portal.col_status') },
  { key: 'priority',      label: t('pages.manager_portal.col_priority') },
  { key: 'execution_date',label: t('pages.manager_portal.col_execution_date') },
  { key: 'created_at',    label: t('pages.manager_portal.col_created_at') },
  { key: 'arrow',         label: '' },
];

/* ── Main page ────────────────────────────────────────────────────────── */
export default function ManagerPortalIndex({ service_orders, stats, createFormSchema, filterSchema, routes }) {
  const [createOpen, setCreateOpen]         = useState(false);
  const [clientLocationId, setClientLocationId] = useState(null);
  const [locationsDirty, setLocationsDirty]     = useState(false);
  const [currentClientId, setCurrentClientId]   = useState(null);
  const [activeStatus, setActiveStatus]         = useState(null);
  const [drawer, setDrawer]                     = useState({ open: false, so: null });

  /* ── Track client_id for ClientLocationSelector ───────────── */
  useEffect(() => {
    if (!createOpen) { setCurrentClientId(null); setClientLocationId(null); setLocationsDirty(false); return; }
    const handler = (e) => { if (e.detail.name === 'client_id') setCurrentClientId(e.detail.value || null); };
    document.addEventListener('modal-field-change', handler);
    return () => document.removeEventListener('modal-field-change', handler);
  }, [createOpen]);

  const orders = service_orders?.data ?? [];

  /* ── Filter by status tab ─────────────────────────────────── */
  const filtered = useMemo(() => {
    if (!activeStatus) return orders;
    return orders.filter(o => o.status === activeStatus);
  }, [orders, activeStatus]);

  /* ── Open drawer ──────────────────────────────────────────── */
  const openDrawer = useCallback((so) => {
    setDrawer({ open: true, so });
  }, []);

  const closeDrawer = useCallback(() => {
    setDrawer({ open: false, so: null });
  }, []);

  const handleAction = useCallback(() => {
    closeDrawer();
    window.location.reload();
  }, [closeDrawer]);

  /* ── Create submission ────────────────────────────────────── */
  const handleCreate = useCallback(async (e, formValues = {}) => {
    e.preventDefault();
    const formData = new FormData(e.target);

    const vals = formValues['sector_ids'];
    if (Array.isArray(vals)) vals.forEach(v => formData.append('sector_ids[]', v));

    buildCreatePayload(
      formData, clientLocationId, locationsDirty,
      ['parish_id', 'street', 'reference_point', 'postal_code', 'latitude', 'longitude']
    );

    const res = await fetch(routes.store, {
      method: 'POST',
      headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest', ...csrfHeader() },
      body: formData,
    });

    if (!res.ok) {
      const body = await res.json().catch(() => ({}));
      throw body;
    }

    setCreateOpen(false);
    setClientLocationId(null);
    setLocationsDirty(false);
    window.location.reload();
  }, [routes.store, clientLocationId, locationsDirty]);

  /* ── Stat cards config ────────────────────────────────────── */
  const statCards = [
    { key: 'pending',           label: t('pages.manager_portal.stat_pending'),          icon: Clock,        color: 'bg-yellow-400' },
    { key: 'in_progress',       label: t('pages.manager_portal.stat_in_progress'),       icon: Zap,          color: 'bg-blue-500' },
    { key: 'awaiting_approval', label: t('pages.manager_portal.stat_awaiting_approval'),  icon: AlertTriangle,color: 'bg-orange-400' },
    { key: 'completed',         label: t('pages.manager_portal.stat_completed'),         icon: CheckCircle,  color: 'bg-green-500' },
  ];

  return (
    <AppLayout title={t('pages.manager_portal.page_title')}>
      <div className="w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-2 pb-6 flex flex-col gap-6">

        {/* Header */}
        <div className="flex items-center justify-between">
          <div>
            <h1 className="text-2xl font-bold text-gray-900 tracking-tight">{t('pages.manager_portal.heading')}</h1>
            <p className="text-gray-500 text-sm mt-1">{t('pages.manager_portal.subtitle')}</p>
          </div>
          <button
            onClick={() => setCreateOpen(true)}
            className="flex items-center gap-2 bg-brand-accent hover:bg-brand-accent/90 text-white text-sm font-semibold px-4 py-2.5 rounded-lg shadow-sm transition-colors whitespace-nowrap"
          >
            <Plus size={16} strokeWidth={2.5} />
            {t('pages.manager_portal.btn_new')}
          </button>
        </div>

        {/* Stats */}
        <div className="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
          {statCards.map(({ key, label, icon, color }) => (
            <button
              key={key}
              onClick={() => setActiveStatus(activeStatus === key ? null : key)}
              className={`text-left transition-all ${activeStatus === key ? 'ring-2 ring-brand-accent ring-offset-1 rounded-xl' : ''}`}
            >
              <StatCard label={label} value={stats[key]} icon={icon} color={color} />
            </button>
          ))}
        </div>

        {/* Status filter pills */}
        {activeStatus && (
          <div className="flex items-center gap-2 text-sm">
            <span className="text-gray-500">{t('pages.manager_portal.filter_by')}</span>
            <StatusBadge value={activeStatus} />
            <button onClick={() => setActiveStatus(null)} className="text-gray-400 hover:text-gray-600 text-xs underline">
              {t('pages.manager_portal.clear_filter')}
            </button>
          </div>
        )}

        {/* Table */}
        <div className="w-full overflow-x-auto bg-white rounded-xl border border-gray-200 shadow-sm">
          {filtered.length === 0 ? (
            <div className="flex flex-col items-center justify-center py-20 text-gray-400">
              <FileText size={40} className="mb-3 opacity-30" />
              <p className="text-sm font-medium text-gray-500">{t('pages.manager_portal.empty_title')}</p>
              <button
                onClick={() => setCreateOpen(true)}
                className="flex items-center gap-1.5 text-brand-accent text-sm font-medium hover:underline mt-4"
              >
                <Plus size={14} /> {t('pages.manager_portal.empty_btn')}
              </button>
            </div>
          ) : (
            <table className="w-full text-sm">
              <thead>
                <tr className="border-b border-gray-100 bg-gray-50">
                  {COLUMNS.map(col => (
                    <th key={col.key} className="text-left text-gray-600 font-semibold px-5 py-3.5 text-xs uppercase tracking-wider">
                      {col.label}
                    </th>
                  ))}
                </tr>
              </thead>
              <tbody className="divide-y divide-gray-100">
                {filtered.map(so => (
                  <tr
                    key={so.id}
                    onClick={() => openDrawer(so)}
                    className="hover:bg-gray-50 cursor-pointer transition-colors group"
                  >
                    <td className="px-5 py-4 font-semibold text-gray-900">{so.process}</td>
                    <td className="px-5 py-4"><StatusBadge value={so.status} /></td>
                    <td className="px-5 py-4"><PriorityDot value={so.priority} /></td>
                    <td className="px-5 py-4 text-gray-500 tabular-nums">{so.execution_date ? formatDate(so.execution_date) : '—'}</td>
                    <td className="px-5 py-4 text-gray-400 tabular-nums">{formatDate(so.created_at)}</td>
                    <td className="px-5 py-4 text-gray-300 group-hover:text-gray-400 transition-colors">
                      <ChevronRight size={16} />
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          )}
        </div>

        {/* Pagination info */}
        {service_orders?.total > service_orders?.per_page && (
          <p className="text-gray-400 text-xs text-right">
            {t('pages.manager_portal.pagination_total', { n: service_orders.total })}
          </p>
        )}
      </div>

      {/* Create modal */}
      <Modal
        formSchema={createFormSchema}
        routes={routes}
        size="lg"
        open={createOpen}
        onClose={() => setCreateOpen(false)}
        onSubmit={handleCreate}
        injectAfterField="client_id"
      >
        <ClientLocationSelector
          isOpen={createOpen}
          clientId={currentClientId}
          onClientLocationChange={setClientLocationId}
          onDirtyChange={setLocationsDirty}
        />
      </Modal>

      {/* Detail drawer */}
      <WorkspaceDrawer
        isOpen={drawer.open}
        onClose={closeDrawer}
        title={drawer.so?.process ?? ''}
        subtitle={drawer.so ? labelFor(drawer.so.status) : ''}
        tabs={drawer.so ? [
          {
            id: 'details',
            label: t('pages.manager_portal.tab_details'),
            component: <DrawerDetails so={drawer.so} routes={routes} onAction={handleAction} />,
          },
        ] : []}
      />
    </AppLayout>
  );
}
