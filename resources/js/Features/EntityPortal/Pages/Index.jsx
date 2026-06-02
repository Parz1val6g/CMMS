import { useState, useCallback, useMemo } from 'react';
import { router } from '@inertiajs/react';
import EntityLayout from '@/Layouts/EntityLayout';
import Modal from '@/Components/Common/Modal';
import WorkspaceDrawer from '@/Components/Drawer/WorkspaceDrawer';
import { labelFor } from '@/utils/enums';
import { formatDate } from '@/utils/format';
import { csrfHeader } from '@/utils/csrf';
import { t } from '@/utils/i18n';
import LoanOrderDrawerTabs from '@/Components/Shared/LoanOrderDrawer';
import { Plus, FileText, ChevronRight } from 'lucide-react';

/* ── Light-mode badge palette (WCAG AA compliant) ───────────────────── */
const PORTAL_BADGE = {
  pending:     'bg-yellow-50 text-yellow-800 ring-1 ring-inset ring-yellow-300/70',
  approved:    'bg-blue-50 text-blue-800 ring-1 ring-inset ring-blue-300/70',
  checked_out: 'bg-purple-50 text-purple-800 ring-1 ring-inset ring-purple-300/70',
  returned:    'bg-green-50 text-green-800 ring-1 ring-inset ring-green-300/70',
  cancelled:   'bg-red-50 text-red-800 ring-1 ring-inset ring-red-300/70',
};

function PortalStatusBadge({ value }) {
  const cls = PORTAL_BADGE[String(value).toLowerCase()] ?? 'bg-gray-100 text-gray-600 ring-1 ring-inset ring-gray-300/70';
  return (
    <span className={`inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium ${cls}`}>
      {labelFor(value)}
    </span>
  );
}

const COLUMNS = [
  { key: 'reference',  label: t('pages.entity_portal.col_reference') },
  { key: 'status',     label: t('pages.entity_portal.col_status') },
  { key: 'created_at', label: t('pages.entity_portal.col_created_at') },
  { key: 'equipments', label: t('pages.entity_portal.col_equipments') },
  { key: 'arrow',      label: '' },
];

function EquipmentPills({ equipments }) {
  if (!equipments?.length) return <span className="text-gray-400 text-xs">—</span>;
  return (
    <div className="flex flex-wrap gap-1.5">
      {equipments.slice(0, 2).map((eq, i) => (
        <span key={i} className="inline-flex items-center rounded-md bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-600 ring-1 ring-inset ring-gray-200">
          {eq.name}
        </span>
      ))}
      {equipments.length > 2 && (
        <span className="text-xs text-gray-400 self-center">+{equipments.length - 2}</span>
      )}
    </div>
  );
}

export default function EntityPortalIndex({ loan_orders, createFormSchema, routes }) {
  const [createOpen, setCreateOpen] = useState(false);
  const [formErrors, setFormErrors] = useState({});
  const [drawer, setDrawer] = useState({ open: false, loanOrder: null, loading: false, error: null });

  const orders = loan_orders?.data ?? [];

  const openDrawer = useCallback(async (item) => {
    setDrawer({ open: true, loanOrder: null, loading: true, error: null });
    try {
      const res = await fetch(`/api/loan-orders/${item.id}`, {
        headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest', ...csrfHeader() },
      });
      if (!res.ok) throw new Error(t('pages.entity_portal.load_failed'));
      const data = await res.json();
      setDrawer({ open: true, loanOrder: data.data ?? data, loading: false, error: null });
    } catch (e) {
      setDrawer({ open: true, loanOrder: null, loading: false, error: e.message });
    }
  }, []);

  const closeDrawer = useCallback(() => {
    setDrawer({ open: false, loanOrder: null, loading: false, error: null });
  }, []);

  const drawerTabs = useMemo(() => {
    if (drawer.loading) return [{ id: 'loading', label: '...', component: <div className="p-4 text-gray-500">{t('common.loading')}</div> }];
    if (drawer.error)   return [{ id: 'error',   label: t('pages.entity_portal.tab_error'), component: <div className="p-4 text-red-500">{drawer.error}</div> }];
    if (!drawer.loanOrder) return [];
    return LoanOrderDrawerTabs(drawer.loanOrder, { onAction: closeDrawer, entityMode: true });
  }, [drawer.loading, drawer.error, drawer.loanOrder, closeDrawer]);

  const handleCreate = useCallback(async (e, formData) => {
    const res = await fetch(routes.store, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', Accept: 'application/json', ...csrfHeader() },
      body: JSON.stringify(formData),
    });
    if (!res.ok) {
      const err = await res.json().catch(() => ({}));
      setFormErrors(err.errors ?? { _general: err.message ?? t('pages.entity_portal.submit_failed') });
      return;
    }
    setFormErrors({});
    setCreateOpen(false);
    router.reload();
  }, [routes.store]);

  return (
    <EntityLayout title={t('pages.entity_portal.page_title')}>
      <div className="max-w-5xl mx-auto">

        {/* Page header */}
        <div className="flex items-center justify-between mb-6">
          <div>
            <h1 className="text-2xl font-bold text-gray-800 tracking-tight">{t('pages.entity_portal.heading')}</h1>
            <p className="text-gray-500 text-sm mt-1">{t('pages.entity_portal.subtitle')}</p>
          </div>
          <button
            onClick={() => { setFormErrors({}); setCreateOpen(true); }}
            className="flex items-center gap-2 bg-brand-accent hover:bg-brand-accent/90 text-white text-sm font-semibold px-4 py-2.5 rounded-lg shadow-sm transition-colors whitespace-nowrap"
          >
            <Plus size={16} strokeWidth={2.5} />
            {t('pages.entity_portal.btn_new')}
          </button>
        </div>

        {/* Orders table */}
        <div className="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
          {orders.length === 0 ? (
            <div className="flex flex-col items-center justify-center py-20 text-gray-400">
              <FileText size={40} className="mb-3 opacity-30" />
              <p className="text-sm font-medium text-gray-500">{t('pages.entity_portal.empty_title')}</p>
              <p className="text-xs text-gray-400 mt-1 mb-4">{t('pages.entity_portal.empty_desc')}</p>
              <button
                onClick={() => setCreateOpen(true)}
                className="flex items-center gap-1.5 text-brand-accent text-sm font-medium hover:underline"
              >
                <Plus size={14} />
                {t('pages.entity_portal.empty_btn')}
              </button>
            </div>
          ) : (
            <table className="w-full text-sm">
              <thead>
                <tr className="border-b border-gray-100 bg-gray-50">
                  {COLUMNS.map((col) => (
                    <th
                      key={col.key}
                      className="text-left text-gray-600 font-semibold px-5 py-3.5 text-xs uppercase tracking-wider"
                    >
                      {col.label}
                    </th>
                  ))}
                </tr>
              </thead>
              <tbody className="divide-y divide-gray-100">
                {orders.map((order) => (
                  <tr
                    key={order.id}
                    onClick={() => openDrawer(order)}
                    className="hover:bg-gray-50 cursor-pointer transition-colors group"
                  >
                    <td className="px-5 py-4 text-gray-900 font-semibold">{order.reference}</td>
                    <td className="px-5 py-4"><PortalStatusBadge value={order.status} /></td>
                    <td className="px-5 py-4 text-gray-500 tabular-nums">{formatDate(order.created_at)}</td>
                    <td className="px-5 py-4"><EquipmentPills equipments={order.equipments} /></td>
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
        {loan_orders?.total > loan_orders?.per_page && (
          <p className="text-gray-400 text-xs mt-3 text-right">
            {t('pages.entity_portal.pagination_total', { n: loan_orders.total })}
          </p>
        )}
      </div>

      {/* Create modal */}
      <Modal
        entityName={t('pages.entity_portal.modal_entity_name')}
        formSchema={createFormSchema}
        routes={routes}
        open={createOpen}
        onClose={() => { setFormErrors({}); setCreateOpen(false); }}
        onSubmit={handleCreate}
        externalErrors={formErrors}
      />

      {/* Detail drawer */}
      <WorkspaceDrawer
        isOpen={drawer.open}
        onClose={closeDrawer}
        title={drawer.loanOrder?.reference ?? ''}
        subtitle={drawer.loanOrder?.status ? <PortalStatusBadge value={drawer.loanOrder.status} /> : ''}
        tabs={drawerTabs}
      />
    </EntityLayout>
  );
}
