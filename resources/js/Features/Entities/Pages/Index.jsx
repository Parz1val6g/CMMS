import { useState, useCallback, useMemo } from 'react';
import { router } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';
import DataManager from '@/Components/DataManager';
import Modal from '@/Components/Common/Modal';
import { t } from '@/utils/i18n';
import { labelFor, badgeStyle } from '@/utils/enums';
import { csrfHeader } from '@/utils/csrf';

export default function EntitiesIndex({
  entities,
  columns,
  formSchema,
  createFormSchema,
  routes,
  filterSchema = [],
  advancedFilterFields = [],
  entityTypeOptions = [],
}) {
  const [createOpen, setCreateOpen] = useState(false);
  const [formErrors, setFormErrors] = useState({});

  const breadcrumbs = [
    { label: t('pages.sidebar.dashboard'), href: '/dashboard' },
    { label: t('pages.sidebar.entities') },
  ];

  const parsedColumns = useMemo(() =>
    columns.map((col) => ({
      ...col,
      render: col.key === 'entity_type'
        ? (val) => {
            const opt = entityTypeOptions.find((o) => o.value === val);
            return <span className={badgeStyle(val)}>{opt?.label ?? val}</span>;
          }
        : col.key === 'loan_orders_count'
          ? (val) => <span className="badge bg-brand-mid text-brand-white">{val ?? 0}</span>
          : undefined,
    })), [columns, entityTypeOptions]);

  const handleCreate = useCallback(async (e, formData) => {
    const res = await fetch(routes.store, {
      method: 'POST',
      credentials: 'include',
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
        ...csrfHeader(),
      },
      body: JSON.stringify(formData),
    });
    if (!res.ok) {
      const err = await res.json().catch(() => ({}));
      setFormErrors(err.errors ?? { _general: err.message ?? t('pages.entities.create_failed') });
      return;
    }
    setFormErrors({});
    setCreateOpen(false);
    router.reload();
  }, [routes.store]);

  return (
    <AppLayout title={t('pages.entities.page_title')}>
      <DataManager
        title={t('pages.entities.dm_title')}
        entityName={t('pages.entities.dm_entity_name')}
        items={entities}
        columns={parsedColumns}
        formSchema={formSchema}
        routes={routes}
        filterSchema={filterSchema}
        advancedFilterFields={advancedFilterFields}
        onNew={() => { setFormErrors({}); setCreateOpen(true); }}
      >
        <Modal
          entityName={t('pages.entities.dm_entity_name')}
          formSchema={createFormSchema}
          routes={routes}
          open={createOpen}
          onClose={() => { setFormErrors({}); setCreateOpen(false); }}
          onSubmit={handleCreate}
          externalErrors={formErrors}
        />
      </DataManager>
    </AppLayout>
  );
}
