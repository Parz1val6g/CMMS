import { useState } from 'react';
import AppLayout from '@/Layouts/AppLayout';
import DataManager from '@/Components/DataManager';
import Modal from '@/Components/Common/Modal';

export default function ServiceTypesIndex({ service_types, columns, formSchema, createFormSchema, routes, filterSchema }) {
  const [showModal, setShowModal] = useState(false);

  const breadcrumbs = [
    { name: 'Dashboard', url: '/dashboard' },
    { name: 'Service Types', url: '/service-types' },
  ];

  return (
    <AppLayout title="Service Types Management" breadcrumbs={breadcrumbs}>
      <Modal
        formSchema={createFormSchema}
        routes={routes}
        open={showModal}
        onClose={() => setShowModal(false)}
      />

      <DataManager
        title="Service Types"
        items={service_types}
        routes={routes}
        columns={columns}
        formSchema={formSchema}
        filterSchema={filterSchema ?? []}
        onNew={() => setShowModal(true)}
      />
    </AppLayout>
  );
}
