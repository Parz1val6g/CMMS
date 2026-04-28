import { useState } from 'react';
import AppLayout from '@/Layouts/AppLayout';
import DataManager from '@/Components/Common/DataManager';
import Modal from '@/Components/Common/Modal';

export default function TasksIndex({ tasks, columns, formSchema, createFormSchema, routes, filterSchema }) {
  const [showModal, setShowModal] = useState(false);

  const breadcrumbs = [
    { name: 'Dashboard', url: '/dashboard' },
    { name: 'Tasks', url: '/tasks' },
  ];

  return (
    <AppLayout title="Tasks Management" breadcrumbs={breadcrumbs}>
      <Modal
        formSchema={createFormSchema}
        routes={routes}
        size="lg"
        open={showModal}
        onClose={() => setShowModal(false)}
      />

      <DataManager
        title="Tasks"
        items={tasks}
        routes={routes}
        columns={columns}
        formSchema={formSchema}
        filterSchema={filterSchema ?? []}
      />
    </AppLayout>
  );
}
