import { useState, useCallback } from 'react';
import AppLayout from '@/Layouts/AppLayout';
import { t } from '@/utils/i18n';
import DataManager from '@/Components/DataManager';
import Modal from '@/Components/Common/Modal';

export default function CrudPage({
  title,
  items,
  columns,
  formSchema,
  createFormSchema,
  routes,
  filterSchema = [],
  advancedFilterFields = [],
  breadcrumbs,
  baseRoute,
  modalSize = '',
  entityName,
  onRowClick,
  refreshKey = 0,
  children,
}) {
  const [showModal, setShowModal] = useState(false);
  const [internalKey, setInternalKey] = useState(0);

  const handleModalSuccess = useCallback(() => {
    setInternalKey(k => k + 1);
  }, []);

  const resolvedBreadcrumbs = breadcrumbs ?? (baseRoute
    ? [
        { name: t('pages.sidebar.dashboard'), url: '/dashboard' },
        { name: t(title), url: baseRoute },
      ]
    : undefined
  );

  return (
    <AppLayout
      title={t('pages.index_pages.mgmt_title', { entity: t(title) })}
      breadcrumbs={resolvedBreadcrumbs}
    >
      <Modal
        formSchema={createFormSchema}
        routes={routes}
        size={modalSize}
        open={showModal}
        onClose={() => setShowModal(false)}
        onSuccess={handleModalSuccess}
      />

      <DataManager
        title={t(title)}
        entityName={entityName}
        items={items}
        routes={routes}
        columns={columns}
        formSchema={formSchema}
        filterSchema={filterSchema}
        advancedFilterFields={advancedFilterFields}
        onNew={() => setShowModal(true)}
        onRowClick={onRowClick}
        refreshKey={refreshKey + internalKey}
      />

      {children}
    </AppLayout>
  );
}
