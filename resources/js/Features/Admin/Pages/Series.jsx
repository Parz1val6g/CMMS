import { useState } from 'react';
import AppLayout from '@/Layouts/AppLayout';
import DataManager from '@/Components/DataManager';
import Modal from '@/Components/Common/Modal';

export default function SeriesIndex({ series, columns, formSchema, createFormSchema, routes, filterSchema }) {
  const [showModal, setShowModal] = useState(false);

  const breadcrumbs = [
    { name: 'Dashboard', url: '/dashboard' },
    { name: 'Series', url: '/admin/series' },
  ];

  return (
    <AppLayout title="Series Management" breadcrumbs={breadcrumbs}>
      <Modal
        formSchema={createFormSchema}
        routes={routes}
        open={showModal}
        onClose={() => setShowModal(false)}
      />

      <DataManager
        title="Series"
        items={series}
        routes={routes}
        columns={columns}
        formSchema={formSchema}
        filterSchema={filterSchema ?? []}
        onNew={() => setShowModal(true)}
      />
    </AppLayout>
  );
}
