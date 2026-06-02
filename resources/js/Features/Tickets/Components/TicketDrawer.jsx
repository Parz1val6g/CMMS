import { useState, useCallback } from 'react';
import { t } from '@/utils/i18n';
import { formatDate } from '@/utils/format';
import { useToast } from '@/Components/Toast/ToastContext';
import { csrfHeader } from '@/utils/csrf';
import WorkspaceDrawer from '@/Components/Drawer/WorkspaceDrawer';

const PRIORITY_BADGE = {
  low:    'bg-brand-mid/20 text-brand-mid',
  normal: 'bg-blue-100 text-blue-700',
  high:   'bg-orange-100 text-orange-700',
  urgent: 'bg-red-100 text-red-700',
};

const STATUS_BADGE = {
  open:        'bg-blue-100 text-blue-700',
  in_progress: 'bg-yellow-100 text-yellow-700',
  converted:   'bg-green-100 text-green-700',
  cancelled:   'bg-red-100 text-red-700',
};

export default function TicketDrawer({ ticket, isOpen, onClose, routes, userRole, onUpdated }) {
  const [converting, setConverting] = useState(false);
  const [cancelling, setCancelling] = useState(false);
  const toast = useToast();

  const isTerminal = ticket?.status?.value === 'converted' || ticket?.status?.value === 'cancelled';
  const canConvert = (userRole === 'admin' || userRole === 'manager') && !isTerminal;
  const canCancel  = !isTerminal;

  const handleCancel = useCallback(async () => {
    if (!ticket || cancelling) return;
    if (!window.confirm(t('pages.tickets.confirm_cancel'))) return;

    setCancelling(true);
    try {
      const res = await fetch(routes.destroy.replace(':id', ticket.id), {
        method: 'DELETE',
        headers: {
          Accept: 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
          ...csrfHeader(),
        },
      });

      if (res.ok) {
        toast.success(t('pages.tickets.cancelled_success'));
        onUpdated?.();
        onClose();
      } else {
        const body = await res.json();
        toast.error(body.message ?? t('pages.tickets.cancel_failed'));
      }
    } catch {
      toast.error(t('pages.tickets.unexpected_error'));
    } finally {
      setCancelling(false);
    }
  }, [ticket, cancelling, routes, toast, onUpdated, onClose]);

  const handleConvert = useCallback(async () => {
    if (!ticket || converting) return;
    if (!window.confirm(t('pages.tickets.confirm_convert'))) return;

    // Simple conversion — use ticket data as SO defaults
    const managerId = ticket.ticket_manager?.id;
    const clientId  = ticket.client?.id;

    if (!managerId || !clientId) {
      toast.error(t('pages.tickets.convert_missing_fields'));
      return;
    }

    setConverting(true);
    try {
      const res = await fetch(routes.convert.replace(':id', ticket.id), {
        method: 'POST',
        headers: {
          Accept: 'application/json',
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
          ...csrfHeader(),
        },
        body: JSON.stringify({
          workflow_type: 'regular',
          manager_id:    managerId,
          client_id:     clientId,
          priority:      ticket.priority?.value ?? 'normal',
          description:   ticket.description,
        }),
      });

      if (res.ok) {
        toast.success(t('pages.tickets.converted_success'));
        onUpdated?.();
        onClose();
      } else {
        const body = await res.json();
        toast.error(body.message ?? t('pages.tickets.convert_failed'));
      }
    } catch {
      toast.error(t('pages.tickets.unexpected_error'));
    } finally {
      setConverting(false);
    }
  }, [ticket, converting, routes, toast, onUpdated, onClose]);

  if (!ticket) return null;

  const drawerTitle = (
    <div className="flex items-center gap-3 flex-wrap">
      <span className="text-sm font-mono text-indigo-400">{ticket.id?.slice(0, 8)}…</span>
      <span className={`inline-flex items-center px-2.5 py-0.5 text-xs font-semibold rounded-full ${STATUS_BADGE[ticket.status?.value] ?? 'bg-brand-mid/20 text-brand-mid'}`}>
        {ticket.status?.label}
      </span>
      <span className={`inline-flex items-center px-2.5 py-0.5 text-xs font-semibold rounded-full ${PRIORITY_BADGE[ticket.priority?.value] ?? 'bg-brand-mid/20 text-brand-mid'}`}>
        {ticket.priority?.label}
      </span>
    </div>
  );

  const tabs = [
    {
      id: 'details',
      label: t('pages.tickets.tab_details'),
      component: (
        <TicketDetailsTab
          ticket={ticket}
          canCancel={canCancel}
          canConvert={canConvert}
          cancelling={cancelling}
          converting={converting}
          onCancel={handleCancel}
          onConvert={handleConvert}
        />
      ),
    },
  ];

  return (
    <WorkspaceDrawer
      isOpen={isOpen}
      onClose={onClose}
      title={drawerTitle}
      subtitle={ticket.client?.name ? `${t('pages.tickets.drawer_client_label')} ${ticket.client.name}` : null}
      tabs={tabs}
    />
  );
}

function TicketDetailsTab({ ticket, canCancel, canConvert, cancelling, converting, onCancel, onConvert }) {
  return (
    <div className="space-y-6">
      {/* Description */}
      <section>
        <h4 className="text-sm font-semibold text-brand-mid uppercase tracking-wider mb-2">
          {t('pages.tickets.section_description')}
        </h4>
        <p className="text-sm text-brand-darkest whitespace-pre-wrap">{ticket.description}</p>
      </section>

      {/* Service Type */}
      {ticket.service_type && (
        <section>
          <h4 className="text-sm font-semibold text-brand-mid uppercase tracking-wider mb-2">
            {t('pages.tickets.section_service_type')}
          </h4>
          <p className="text-sm text-brand-darkest">{ticket.service_type.name}</p>
        </section>
      )}

      {/* Manager */}
      {ticket.ticket_manager && (
        <section>
          <h4 className="text-sm font-semibold text-brand-mid uppercase tracking-wider mb-2">
            {t('pages.tickets.section_manager')}
          </h4>
          <p className="text-sm text-brand-darkest">{ticket.ticket_manager.name}</p>
        </section>
      )}

      {/* Linked Service Order */}
      {ticket.service_order && (
        <section>
          <h4 className="text-sm font-semibold text-brand-mid uppercase tracking-wider mb-2">
            {t('pages.tickets.section_service_order')}
          </h4>
          <p className="text-sm font-mono text-indigo-400">{ticket.service_order.process}</p>
        </section>
      )}

      {/* Created At */}
      <section>
        <h4 className="text-sm font-semibold text-brand-mid uppercase tracking-wider mb-2">
          {t('pages.tickets.section_created_at')}
        </h4>
        <p className="text-sm text-brand-darkest">{formatDate(ticket.created_at)}</p>
      </section>

      {/* Actions */}
      {(canCancel || canConvert) && (
        <div className="flex items-center gap-3 pt-4 border-t border-brand-mid/20">
          {canConvert && (
            <button
              type="button"
              onClick={onConvert}
              disabled={converting}
              className="px-4 py-2 rounded-lg bg-green-600 hover:bg-green-700 text-white text-sm font-medium transition-colors disabled:opacity-50"
            >
              {converting ? t('pages.tickets.converting_btn') : t('pages.tickets.convert_btn')}
            </button>
          )}
          {canCancel && (
            <button
              type="button"
              onClick={onCancel}
              disabled={cancelling}
              className="px-4 py-2 rounded-lg bg-red-100 hover:bg-red-200 text-red-700 text-sm font-medium transition-colors disabled:opacity-50"
            >
              {cancelling ? t('pages.tickets.cancelling_btn') : t('pages.tickets.cancel_btn')}
            </button>
          )}
        </div>
      )}
    </div>
  );
}
