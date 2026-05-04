import { useState } from 'react';
import AppLayout from '@/Layouts/AppLayout';
import DataManager from '@/Components/DataManager';
import Modal from '@/Components/Common/Modal';

export default function EquipmentsIndex({ equipments, columns, formSchema, createFormSchema, routes, filterSchema }) {
  const [showModal, setShowModal] = useState(false);

  const breadcrumbs = [
    { name: 'Dashboard', url: '/dashboard' },
    { name: 'Equipments', url: '/equipments' },
  ];

  return (
    <AppLayout title="Equipments Management" breadcrumbs={breadcrumbs}>
      <Modal
        formSchema={createFormSchema}
        routes={routes}
        size="lg"
        open={showModal}
        onClose={() => setShowModal(false)}
      />

      <DataManager
        title="Equipments"
        items={equipments}
        routes={routes}
        columns={columns}
        formSchema={formSchema}
        filterSchema={filterSchema ?? []}
        onNew={() => setShowModal(true)}
      />
    </AppLayout>
  );
}
