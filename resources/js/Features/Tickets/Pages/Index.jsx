import { useState, useCallback, useRef } from 'react';
import { t } from '@/utils/i18n';
import { usePage } from '@inertiajs/react';
import { useToast } from '@/Components/Toast/ToastContext';
import AppLayout from '@/Layouts/AppLayout';
import DataManager from '@/Components/DataManager';
import Modal from '@/Components/Common/Modal';
import { csrfHeader } from '@/utils/csrf';
import TicketDrawer from '../Components/TicketDrawer';

export default function TicketsIndex({ tickets, columns, formSchema, createFormSchema, routes, filterSchema }) {
  const [showModal, setShowModal] = useState(false);
  const [formErrors, setFormErrors] = useState({});
  const [drawerOpen, setDrawerOpen] = useState(false);
  const [selectedTicket, setSelectedTicket] = useState(null);
  const [ticketDetail, setTicketDetail] = useState(null);
  const [ticketLoading, setTicketLoading] = useState(false);
  const savingRef = useRef(false);
  const globalToast = useToast();
  const { props: pageProps } = usePage();
  const userRole = pageProps?.auth?.user?.roles?.[0]?.name ?? null;

  const breadcrumbs = [
    { name: t('pages.sidebar.dashboard'), url: '/dashboard' },
    { name: t('pages.sidebar.tickets'), url: '/tickets' },
  ];

  /* ── Create ticket ────────────────────────────────── */
  const handleCreate = useCallback(async (e, formValues) => {
    e.preventDefault();
    if (!routes.store || savingRef.current) return;
    savingRef.current = true;
    setFormErrors({});

    const form = e.target;
    const formData = new FormData(form);
    const payload = {};
    formData.forEach((value, key) => { payload[key] = value; });

    try {
      const res = await fetch(routes.store, {
        method: 'POST',
        headers: {
          Accept: 'application/json',
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
          ...csrfHeader(),
        },
        body: JSON.stringify(payload),
      });
      const body = await res.json();

      if (res.ok) {
        setShowModal(false);
        window.location.reload();
      } else {
        if (body.errors) setFormErrors(body.errors);
        else globalToast.error(body.message ?? t('pages.tickets.create_failed'));
      }
    } catch {
      globalToast.error(t('pages.tickets.unexpected_error'));
    } finally {
      savingRef.current = false;
    }
  }, [routes.store, globalToast]);

  /* ── Row click — load detail ────────────────────────── */
  const handleRowClick = useCallback(async (item) => {
    setSelectedTicket(item);
    setDrawerOpen(true);
    setTicketDetail(null);
    setTicketLoading(true);

    try {
      const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
      const res = await fetch(`/api/tickets/${item.id}`, {
        headers: { Accept: 'application/json', 'X-CSRF-TOKEN': csrfToken, 'X-Requested-With': 'XMLHttpRequest' },
      });
      if (!res.ok) throw new Error(`HTTP ${res.status}`);
      const body = await res.json();
      setTicketDetail(body.data ?? body);
    } catch {
      // show placeholder
    } finally {
      setTicketLoading(false);
    }
  }, []);

  const handleCloseDrawer = useCallback(() => {
    setDrawerOpen(false);
    setTicketDetail(null);
  }, []);

  return (
    <AppLayout title={t('pages.tickets.page_title')} breadcrumbs={breadcrumbs}>
      <Modal
        formSchema={createFormSchema}
        routes={routes}
        size="md"
        open={showModal}
        onClose={() => setShowModal(false)}
        onSubmit={handleCreate}
        errors={formErrors}
      />

      <DataManager
        title={t('pages.tickets.dm_title')}
        entityName={t('pages.tickets.dm_entity_name')}
        items={tickets}
        routes={routes}
        columns={columns}
        formSchema={formSchema}
        filterSchema={filterSchema ?? []}
        onNew={() => { setFormErrors({}); setShowModal(true); }}
        onRowClick={handleRowClick}
      />

      {selectedTicket && (
        <TicketDrawer
          ticket={ticketLoading ? selectedTicket : (ticketDetail ?? selectedTicket)}
          isOpen={drawerOpen}
          onClose={handleCloseDrawer}
          routes={routes}
          userRole={userRole}
          onUpdated={() => window.location.reload()}
        />
      )}
    </AppLayout>
  );
}
