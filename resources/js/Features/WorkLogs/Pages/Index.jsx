import { useState } from 'react';
import AppLayout from '@/Layouts/AppLayout';
import DataManager from '@/Components/Common/DataManager';
import Modal from '@/Components/Common/Modal';

export default function WorkLogsIndex({ work_logs, columns, formSchema, createFormSchema, routes, filterSchema }) {
  const [showModal, setShowModal] = useState(false);

  const breadcrumbs = [
    { name: 'Dashboard', url: '/dashboard' },
    { name: 'Work Logs', url: '/work-logs' },
  ];

  return (
    <AppLayout title="Work Logs Management" breadcrumbs={breadcrumbs}>
      <Modal
        formSchema={createFormSchema}
        routes={routes}
        size="lg"
        open={showModal}
        onClose={() => setShowModal(false)}
      />

      <DataManager
        title="Work Logs"
        items={work_logs}
        routes={routes}
        columns={columns}
        formSchema={formSchema}
        filterSchema={filterSchema ?? []}
      />
    </AppLayout>
  );
}
