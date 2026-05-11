import { useState, useCallback, useMemo } from 'react';
import AppLayout from '@/Layouts/AppLayout';
import { t } from '@/utils/i18n';
import DataManager from '@/Components/DataManager';
import WorkspaceDrawer from '@/Components/Drawer/WorkspaceDrawer';
import ClientLocationManager from '../Components/ClientLocationManager';
import ClientCreateModal from '../Components/ClientCreateModal';

export default function ClientsIndex({ clients, columns, formSchema, createFormSchema, routes, filterSchema, advancedFilterFields, districts = [], municipalities = [], parishes = [] }) {
  const [showModal, setShowModal] = useState(false);
  const [drawerOpen, setDrawerOpen] = useState(false);
  const [selectedClient, setSelectedClient] = useState(null);

  const breadcrumbs = [
    { name: t('pages.sidebar.dashboard'), url: '/dashboard' },
    { name: t('pages.sidebar.clients'), url: '/clients' },
  ];

  const handleRowClick = useCallback((client) => {
    setSelectedClient(client);
    setDrawerOpen(true);
  }, []);

  const handleCloseDrawer = useCallback(() => {
    setDrawerOpen(false);
  }, []);

  const drawerTitle = useMemo(() => {
    if (!selectedClient) return null;
    return (
      <div className="flex items-center gap-3">
        <span>{selectedClient.name}</span>
        {selectedClient.nif && (
          <span className="text-xs font-mono text-slate-400">NIF {selectedClient.nif}</span>
        )}
      </div>
    );
  }, [selectedClient]);

  const drawerTabs = useMemo(() => {
    if (!selectedClient) return [];
    return [
      {
        id: 'locations',
        label: t('pages.client_locations.tab_label'),
        component: <ClientLocationManager client={selectedClient} districts={districts} municipalities={municipalities} parishes={parishes} />,
      },
    ];
  }, [selectedClient]);

  return (
    <AppLayout
      title={t('pages.index_pages.mgmt_title', { entity: t('pages.sidebar.clients') })}
      breadcrumbs={breadcrumbs}
    >
      <ClientCreateModal
        open={showModal}
        onClose={() => setShowModal(false)}
        storeUrl={routes.store}
        districts={districts}
        municipalities={municipalities}
        parishes={parishes}
      />

      <DataManager
        title={t('pages.sidebar.clients')}
        items={clients}
        routes={routes}
        columns={columns}
        formSchema={formSchema}
        filterSchema={filterSchema ?? []}
        advancedFilterFields={advancedFilterFields ?? []}
        onNew={() => setShowModal(true)}
        onRowClick={handleRowClick}
      />

      <WorkspaceDrawer
        isOpen={drawerOpen}
        onClose={handleCloseDrawer}
        title={drawerTitle}
        subtitle={selectedClient?.email ?? null}
        tabs={drawerTabs}
      />
    </AppLayout>
  );
}
