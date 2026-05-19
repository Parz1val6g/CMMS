import { useState, useCallback } from 'react';
import { router } from '@inertiajs/react';
import { t } from '@/utils/i18n';
import { labelFor, badgeStyle } from '@/utils/enums';
import { formatDate } from '@/utils/format';

function DetailRow({ label, value }) {
  return (
    <div className="d-flex mb-2">
      <span className="text-brand-mid fw-semibold" style={{ minWidth: 110 }}>{label}</span>
      <span className="text-brand-white">{value ?? t('pages.loan_orders.value_missing')}</span>
    </div>
  );
}

function DetailsTab({ lo }) {
  return (
    <div className="p-3">
      <h6 className="text-brand-accent fw-bold mb-3">{t('pages.loan_orders.section_description')}</h6>
      <p className="text-brand-white mb-4">{lo.description || t('pages.loan_orders.value_missing')}</p>

      {lo.entity && (
        <>
          <h6 className="text-brand-accent fw-bold mb-3 mt-4">{t('pages.loan_orders.section_entity')}</h6>
          <DetailRow label={t('pages.loan_orders.card_client_label')} value={lo.entity.name} />
          {lo.entity.entity_type && (
            <DetailRow label={t('pages.loan_orders.entity_type_label')} value={labelFor(lo.entity.entity_type)} />
          )}
          {lo.entity.nif && (
            <DetailRow label={t('pages.loan_orders.label_nif')} value={lo.entity.nif} />
          )}
        </>
      )}

      <h6 className="text-brand-accent fw-bold mb-3 mt-4">{t('pages.loan_orders.section_manager')}</h6>
      <DetailRow label={t('pages.loan_orders.card_manager_label')} value={lo.manager?.name} />

      {lo.location && (
        <>
          <h6 className="text-brand-accent fw-bold mb-3 mt-4">{t('pages.loan_orders.section_location')}</h6>
          <DetailRow label={t('pages.loan_orders.parish_label')} value={lo.location.parish?.name} />
          <DetailRow label={t('pages.loan_orders.street_label')} value={lo.location.street} />
          <DetailRow label={t('pages.loan_orders.reference_label')} value={lo.location.reference_point} />
        </>
      )}

      <h6 className="text-brand-accent fw-bold mb-3 mt-4">{t('pages.loan_orders.section_status')}</h6>
      <div className="mb-2">
        <span className={badgeStyle(lo.status)}>{labelFor(lo.status)}</span>
      </div>

      <DetailRow label={t('pages.loan_orders.section_created_at')} value={formatDate(lo.created_at)} />
      {lo.approved_at && <DetailRow label={t('pages.loan_orders.section_approved_at')} value={formatDate(lo.approved_at)} />}
      {lo.checked_out_at && <DetailRow label={t('pages.loan_orders.section_checked_out_at')} value={formatDate(lo.checked_out_at)} />}
      {lo.returned_at && <DetailRow label={t('pages.loan_orders.section_returned_at')} value={formatDate(lo.returned_at)} />}
      {lo.cancelled_at && <DetailRow label={t('pages.loan_orders.section_cancelled_at')} value={formatDate(lo.cancelled_at)} />}
    </div>
  );
}

function EquipmentCard({ eq }) {
  const eqBadgeStyle = badgeStyle(eq.is_loanable ? 'available' : 'unavailable');
  return (
    <div className="card bg-brand-dark border-brand-mid mb-2 shadow-sm">
      <div className="card-body py-2 px-3">
        <div className="d-flex justify-content-between align-items-start">
          <div>
            <h6 className="text-brand-white fw-bold mb-1">{eq.name}</h6>
            <small className="text-brand-mid d-block">
              {t('pages.loan_orders.brand_label')} {eq.brand || t('pages.loan_orders.value_missing')}
            </small>
            {eq.model && (
              <small className="text-brand-mid d-block">
                {t('pages.loan_orders.model_label')} {eq.model}
              </small>
            )}
            <small className="text-brand-mid d-block">
              {t('pages.loan_orders.serial_label')} {eq.serial_number || t('pages.loan_orders.value_missing')}
            </small>
            {eq.start_date && (
              <small className="text-brand-mid d-block">
                {t('pages.loan_orders.start_date')}: {formatDate(eq.start_date)}
                {eq.end_date && ` — ${formatDate(eq.end_date)}`}
              </small>
            )}
            {eq.needs_operator && (
              <small className="text-brand-accent d-block">{t('pages.loan_orders.needs_operator')}</small>
            )}
          </div>
          <span className={`badge ${eqBadgeStyle}`}>
            {eq.is_loanable ? t('pages.loan_orders.status_loanable') : t('pages.loan_orders.status_not_loanable')}
          </span>
        </div>
        {eq.description && (
          <p className="text-brand-mid small mt-1 mb-0">{eq.description}</p>
        )}
      </div>
    </div>
  );
}

function EquipmentTab({ lo }) {
  const equipments = lo.equipments ?? [];
  if (!equipments.length) {
    return <div className="p-3 text-brand-mid">{t('pages.loan_orders.no_equipment_assigned')}</div>;
  }
  return (
    <div className="p-3">
      {equipments.map((eq, i) => (
        <EquipmentCard key={eq.id ?? i} eq={eq} />
      ))}
    </div>
  );
}

function TasksTab({ lo }) {
  const tasks = lo.tasks ?? [];
  if (!tasks.length) {
    return <div className="p-3 text-brand-mid">{t('pages.loan_orders.no_tasks')}</div>;
  }
  return (
    <div className="p-3">
      {tasks.map((task) => (
        <div key={task.id} className="card bg-brand-dark border-brand-mid mb-2 shadow-sm">
          <div className="card-body py-2 px-3">
            <div className="d-flex justify-content-between align-items-start">
              <div>
                <h6 className="text-brand-white fw-bold mb-1">
                  {task.reference}
                  <span className={`badge ${badgeStyle(task.status)} ms-2`}>{labelFor(task.status)}</span>
                </h6>
                <small className="text-brand-mid d-block">{task.description}</small>
                {task.manager && (
                  <small className="text-brand-mid d-block mt-1">
                    {t('pages.loan_orders.card_manager_label')} {task.manager.name}
                  </small>
                )}
              </div>
            </div>
          </div>
        </div>
      ))}
    </div>
  );
}

function HistoryTab({ lo }) {
  const events = [];
  if (lo.created_at) events.push({ date: lo.created_at, label: t('pages.loan_orders.section_created_at') });
  if (lo.approved_at) {
    const approvedDetail = lo.approved_by
      ? `${t('pages.loan_orders.section_approved_at')} — ${t('pages.loan_orders.section_approved_by')}: ${lo.approved_by.name}`
      : t('pages.loan_orders.section_approved_at');
    events.push({ date: lo.approved_at, label: approvedDetail });
  }
  if (lo.checked_out_at) events.push({ date: lo.checked_out_at, label: t('pages.loan_orders.section_checked_out_at') });
  if (lo.returned_at) events.push({ date: lo.returned_at, label: t('pages.loan_orders.section_returned_at') });
  if (lo.cancelled_at) {
    const cancelledDetail = lo.cancelled_by
      ? `${t('pages.loan_orders.section_cancelled_at')} — ${t('pages.loan_orders.section_cancelled_by')}: ${lo.cancelled_by.name}`
      : t('pages.loan_orders.section_cancelled_at');
    events.push({ date: lo.cancelled_at, label: cancelledDetail });
  }

  return (
    <div className="p-3">
      {events.length === 0 && <div className="text-brand-mid">{t('pages.loan_orders.value_missing')}</div>}
      {events.map((ev, i) => (
        <div key={i} className="d-flex align-items-start mb-3">
          <div className="me-3 mt-1" style={{ width: 10, height: 10, borderRadius: '50%', background: 'var(--brand-accent)', flexShrink: 0 }} />
          <div>
            <div className="text-brand-white fw-semibold small">{ev.label}</div>
            <div className="text-brand-mid small">{formatDate(ev.date)}</div>
          </div>
        </div>
      ))}
      {lo.notes_cancel && (
        <div className="mt-3 p-2 bg-brand-darkest rounded">
          <small className="text-brand-mid fw-semibold">{t('pages.loan_orders.section_notes_cancel')}:</small>
          <p className="text-brand-white small mb-0 mt-1">{lo.notes_cancel}</p>
        </div>
      )}
      {lo.notes_checkout && (
        <div className="mt-3 p-2 bg-brand-darkest rounded">
          <small className="text-brand-mid fw-semibold">{t('pages.loan_orders.section_notes_checkout')}:</small>
          <p className="text-brand-white small mb-0 mt-1">{lo.notes_checkout}</p>
        </div>
      )}
      {lo.notes_return && (
        <div className="mt-3 p-2 bg-brand-darkest rounded">
          <small className="text-brand-mid fw-semibold">{t('pages.loan_orders.section_notes_return')}:</small>
          <p className="text-brand-white small mb-0 mt-1">{lo.notes_return}</p>
        </div>
      )}
    </div>
  );
}

function ActionButtons({ lo, onAction }) {
  const [cancelling, setCancelling] = useState(false);
  const [returning, setReturning] = useState(false);
  const [approving, setApproving] = useState(false);
  const [checkingOut, setCheckingOut] = useState(false);

  const handleCancel = useCallback(() => {
    if (!confirm(t('pages.loan_orders.action_cancel_confirm'))) return;
    setCancelling(true);
    router.post(`/api/loan-orders/${lo.id}/cancel`, {}, {
      preserveScroll: true,
      onSuccess: () => { setCancelling(false); onAction(); },
      onError: () => { setCancelling(false); alert('Failed to cancel'); },
    });
  }, [lo.id, onAction]);

  const handleInitiateReturn = useCallback(() => {
    if (!confirm(t('pages.loan_orders.action_initiate_return_confirm'))) return;
    setReturning(true);
    router.post(`/api/loan-orders/${lo.id}/return`, {}, {
      preserveScroll: true,
      onSuccess: () => { setReturning(false); onAction(); },
      onError: () => { setReturning(false); alert('Failed to initiate return'); },
    });
  }, [lo.id, onAction]);

  const handleApprove = useCallback(() => {
    if (!confirm(t('pages.loan_orders.action_approve_confirm'))) return;
    setApproving(true);
    router.post(`/api/loan-orders/${lo.id}/approve`, {}, {
      preserveScroll: true,
      onSuccess: () => { setApproving(false); onAction(); },
      onError: () => { setApproving(false); alert('Failed to approve'); },
    });
  }, [lo.id, onAction]);

  const handleCheckout = useCallback(() => {
    if (!confirm(t('pages.loan_orders.action_checkout_confirm'))) return;
    setCheckingOut(true);
    router.post(`/api/loan-orders/${lo.id}/checkout`, {}, {
      preserveScroll: true,
      onSuccess: () => { setCheckingOut(false); onAction(); },
      onError: () => { setCheckingOut(false); alert('Failed to checkout'); },
    });
  }, [lo.id, onAction]);

  const isPending = lo.status === 'pending';
  const isApproved = lo.status === 'approved';
  const isCheckedOut = lo.status === 'checked_out';

  return (
    <div className="px-3 py-2 border-top border-brand-mid d-flex gap-2 flex-wrap">
      {isPending && (
        <button className="btn btn-outline-success btn-sm flex-fill" onClick={handleApprove} disabled={approving}>
          {approving ? '...' : t('pages.loan_orders.action_approve')}
        </button>
      )}
      {isApproved && (
        <button className="btn btn-outline-accent btn-sm flex-fill" onClick={handleCheckout} disabled={checkingOut}>
          {checkingOut ? '...' : t('pages.loan_orders.action_checkout')}
        </button>
      )}
      {isCheckedOut && (
        <button className="btn btn-outline-accent btn-sm flex-fill" onClick={handleInitiateReturn} disabled={returning}>
          {returning ? '...' : t('pages.loan_orders.action_initiate_return')}
        </button>
      )}
      {isPending && (
        <button className="btn btn-outline-danger btn-sm flex-fill" onClick={handleCancel} disabled={cancelling}>
          {cancelling ? '...' : t('pages.loan_orders.action_cancel')}
        </button>
      )}
    </div>
  );
}

export default function LoanOrderDrawerTabs(loanOrder, { onAction }) {
  return [
    {
      id: 'details',
      label: t('pages.loan_orders.tab_details'),
      component: <DetailsTab lo={loanOrder} />,
    },
    {
      id: 'equipment',
      label: t('pages.loan_orders.tab_equipment'),
      component: <EquipmentTab lo={loanOrder} />,
    },
    {
      id: 'tasks',
      label: t('pages.loan_orders.tab_tasks'),
      component: <TasksTab lo={loanOrder} />,
    },
    {
      id: 'history',
      label: t('pages.loan_orders.tab_history'),
      component: (
        <>
          <HistoryTab lo={loanOrder} />
          <ActionButtons lo={loanOrder} onAction={onAction} />
        </>
      ),
    },
  ];
}
