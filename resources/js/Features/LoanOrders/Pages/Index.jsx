import { useState, useEffect, useCallback, useMemo } from 'react';
import { router } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';
import DataManager from '@/Components/DataManager';
import Modal from '@/Components/Common/Modal';
import WorkspaceDrawer from '@/Components/Drawer/WorkspaceDrawer';
import { t } from '@/utils/i18n';
import { labelFor, badgeStyle } from '@/utils/enums';
import { csrfHeader } from '@/utils/csrf';
import LoanOrderDrawerTabs from '@/Components/Shared/LoanOrderDrawer';

export default function LoanOrdersIndex({ loan_orders, columns, formSchema, createFormSchema, routes, filterSchema = [], advancedFilterFields = [] }) {
  const breadcrumbs = [
    { label: t('pages.loan_orders.breadcrumb_dashboard'), href: '/dashboard' },
    { label: t('pages.loan_orders.breadcrumb') },
  ];

  const [drawer, setDrawer] = useState({ open: false, loanOrder: null, loading: false, error: null });
  const [createOpen, setCreateOpen] = useState(false);

  const openDrawer = useCallback(async (item) => {
    setDrawer({ open: true, loanOrder: null, loading: true, error: null });
    try {
      const res = await fetch(`/api/loan-orders/${item.id}`, {
        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest', ...csrfHeader() },
      });
      if (!res.ok) throw new Error(t('pages.loan_orders.load_failed'));
      const data = await res.json();
      setDrawer({ open: true, loanOrder: data.data ?? data, loading: false, error: null });
    } catch (e) {
      setDrawer({ open: true, loanOrder: null, loading: false, error: e.message });
    }
  }, []);

  const closeDrawer = useCallback(() => {
    setDrawer({ open: false, loanOrder: null, loading: false, error: null });
  }, []);

  const parsedColumns = useMemo(() =>
    columns.map((col) => ({
      ...col,
      render: col.key === 'status'
        ? (val) => <span className={badgeStyle(val)}>{labelFor(val)}</span>
        : undefined,
    })), [columns]);

  const drawerTabs = useMemo(() => {
    if (drawer.loading) return [{ id: 'loading', label: '...', component: () => <div className="p-4 text-brand-mid">{t('common.loading')}</div> }];
    if (drawer.error) return [{ id: 'error', label: t('pages.loan_orders.tab_error'), component: () => <div className="p-4 text-red-500">{drawer.error}</div> }];
    if (!drawer.loanOrder) return [];
    return LoanOrderDrawerTabs(drawer.loanOrder, { onAction: closeDrawer });
  }, [drawer.loading, drawer.error, drawer.loanOrder, closeDrawer]);

  const handleCreate = useCallback(async (e, formData) => {
    const res = await fetch(routes.store, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        ...csrfHeader(),
      },
      body: JSON.stringify(formData),
    });
    if (!res.ok) {
      const err = await res.json().catch(() => ({ message: t('pages.loan_orders.create_failed') }));
      throw err;
    }
    setCreateOpen(false);
    window.location.reload();
  }, [routes.store]);

  return (
    <AppLayout title={t('pages.loan_orders.page_title')}>
      <DataManager
        title={t('pages.loan_orders.dm_title')}
        entityName={t('pages.loan_orders.dm_entity_name')}
        items={loan_orders}
        columns={parsedColumns}
        formSchema={formSchema}
        routes={routes}
        filterSchema={filterSchema}
        advancedFilterFields={advancedFilterFields}
        onRowClick={openDrawer}
        onNew={() => setCreateOpen(true)}
      >
        <Modal
          entityName={t('pages.loan_orders.dm_entity_name')}
          formSchema={createFormSchema}
          routes={routes}
          open={createOpen}
          onClose={() => setCreateOpen(false)}
          onSubmit={handleCreate}
        />
        <WorkspaceDrawer
          isOpen={drawer.open}
          onClose={closeDrawer}
          title={drawer.loanOrder?.reference ?? ''}
          subtitle={drawer.loanOrder?.status ? labelFor(drawer.loanOrder.status) : ''}
          tabs={drawerTabs}
        />
      </DataManager>
    </AppLayout>
  );
}
