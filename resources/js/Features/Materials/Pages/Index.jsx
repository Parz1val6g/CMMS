import { useState } from 'react';
import AppLayout from '@/Layouts/AppLayout';
import DataManager from '@/Components/DataManager';
import Modal from '@/Components/Common/Modal';

export default function MaterialsIndex({ materials, columns, formSchema, createFormSchema, routes, filterSchema }) {
  const [showModal, setShowModal] = useState(false);

  const breadcrumbs = [
    { name: 'Dashboard', url: '/dashboard' },
    { name: 'Materials', url: '/materials' },
  ];

  return (
    <AppLayout title="Materials Management" breadcrumbs={breadcrumbs}>
      <Modal
        formSchema={createFormSchema}
        routes={routes}
        size="lg"
        open={showModal}
        onClose={() => setShowModal(false)}
      />

      <DataManager
        title="Materials"
        items={materials}
        routes={routes}
        columns={columns}
        formSchema={formSchema}
        filterSchema={filterSchema ?? []}
        onNew={() => setShowModal(true)}
      />
    </AppLayout>
  );
}
