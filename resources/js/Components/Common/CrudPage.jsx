import { useState } from 'react';
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
  children,
}) {
  const [showModal, setShowModal] = useState(false);

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
      />

      {children}
    </AppLayout>
  );
}
