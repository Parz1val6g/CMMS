import { useState } from 'react';
import AppLayout from '@/Layouts/AppLayout';
import DataManager from '@/Components/Common/DataManager';
import Modal from '@/Components/Common/Modal';

export default function ClientsIndex({ clients, columns, formSchema, createFormSchema, routes, filterSchema }) {
  const [showModal, setShowModal] = useState(false);

  const breadcrumbs = [
    { name: 'Dashboard', url: '/dashboard' },
    { name: 'Clients', url: '/clients' },
  ];

  return (
    <AppLayout title="Clients Management" breadcrumbs={breadcrumbs}>
      <Modal
        formSchema={createFormSchema}
        routes={routes}
        open={showModal}
        onClose={() => setShowModal(false)}
      />

      <DataManager
        title="Clients"
        items={clients}
        routes={routes}
        columns={columns}
        formSchema={formSchema}
        filterSchema={filterSchema ?? []}
      />
    </AppLayout>
  );
}
