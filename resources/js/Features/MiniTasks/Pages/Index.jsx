import { useState } from 'react';
import AppLayout from '@/Layouts/AppLayout';
import DataManager from '@/Components/DataManager';
import Modal from '@/Components/Common/Modal';

export default function MiniTasksIndex({ mini_tasks, columns, formSchema, createFormSchema, routes, filterSchema }) {
  const [showModal, setShowModal] = useState(false);

  const breadcrumbs = [
    { name: 'Dashboard', url: '/dashboard' },
    { name: 'Mini-Tasks', url: '/mini-tasks' },
  ];

  return (
    <AppLayout title="Mini-Tasks Management" breadcrumbs={breadcrumbs}>
      <Modal
        formSchema={createFormSchema}
        routes={routes}
        size="lg"
        open={showModal}
        onClose={() => setShowModal(false)}
      />

      <DataManager
        title="Mini-Tasks"
        items={mini_tasks}
        routes={routes}
        columns={columns}
        formSchema={formSchema}
        filterSchema={filterSchema ?? []}
        onNew={() => setShowModal(true)}
      />
    </AppLayout>
  );
}
