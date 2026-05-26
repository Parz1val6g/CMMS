import { useState, useCallback } from 'react';
import { router } from '@inertiajs/react';
import BaseField from '@/Components/Shared/Drawer/BaseField';
import { t } from '@/utils/i18n';
import { labelFor } from '@/utils/enums';
import { formatDate } from '@/utils/format';
import { CheckCircle, XCircle, LogOut, RotateCcw, Package, Loader2 } from 'lucide-react';

/* ── Shared badge palette (light-background, WCAG AA) ─────────────── */
const STATUS_BADGE = {
  pending:     'bg-yellow-50 text-yellow-800 ring-1 ring-inset ring-yellow-300/60',
  approved:    'bg-blue-50 text-blue-800 ring-1 ring-inset ring-blue-300/60',
  checked_out: 'bg-purple-50 text-purple-800 ring-1 ring-inset ring-purple-300/60',
  returned:    'bg-green-50 text-green-800 ring-1 ring-inset ring-green-300/60',
  cancelled:   'bg-red-50 text-red-800 ring-1 ring-inset ring-red-300/60',
};

function StatusBadge({ value }) {
  const cls = STATUS_BADGE[String(value ?? '').toLowerCase()]
    ?? 'bg-gray-100 text-gray-700 ring-1 ring-inset ring-gray-300/60';
  return (
    <span className={`inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium ${cls}`}>
      {labelFor(value)}
    </span>
  );
}

/* ── Section header ───────────────────────────────────────────────── */
function SectionTitle({ children }) {
  return (
    <h3 className="text-xs font-semibold text-gray-500 uppercase tracking-widest mb-3 mt-6 first:mt-0 pb-1.5 border-b border-gray-100">
      {children}
    </h3>
  );
}

/* ════════════════════════════════════════════════════════════════════
   TAB: DETALHES
   ════════════════════════════════════════════════════════════════════ */
function DetailsTab({ lo }) {
  return (
    <div className="space-y-0">

      {/* Descrição */}
      <SectionTitle>{t('pages.loan_orders.section_description')}</SectionTitle>
      <p className="text-sm text-gray-700 leading-relaxed">
        {lo.description || <span className="text-gray-400 italic">{t('pages.loan_orders.value_missing')}</span>}
      </p>

      {/* Entidade */}
      {lo.entity && (
        <>
          <SectionTitle>{t('pages.loan_orders.section_entity')}</SectionTitle>
          <div className="grid grid-cols-2 gap-x-6 gap-y-4">
            <BaseField variant="gray" label={t('pages.loan_orders.card_client_label')} value={lo.entity.name} />
            {lo.entity.entity_type && (
              <BaseField variant="gray" label={t('pages.loan_orders.entity_type_label')} value={labelFor(lo.entity.entity_type)} />
            )}
            {lo.entity.nif && (
              <BaseField variant="gray" label={t('pages.loan_orders.label_nif')} value={lo.entity.nif} />
            )}
          </div>
        </>
      )}

      {/* Gestor */}
      <SectionTitle>{t('pages.loan_orders.section_manager')}</SectionTitle>
      <div className="grid grid-cols-2 gap-x-6 gap-y-4">
        <BaseField variant="gray"
          label={t('pages.loan_orders.card_manager_label')}
          value={lo.manager?.name ?? <span className="text-gray-400 italic">{t('pages.loan_orders.value_missing')}</span>}
        />
      </div>

      {/* Localização */}
      {lo.location && (
        <>
          <SectionTitle>{t('pages.loan_orders.section_location')}</SectionTitle>
          <div className="grid grid-cols-2 gap-x-6 gap-y-4">
            <BaseField variant="gray" label={t('pages.loan_orders.parish_label')} value={lo.location.parish?.name} />
            <BaseField variant="gray" label={t('pages.loan_orders.street_label')} value={lo.location.street} />
            {lo.location.landmark && (
              <BaseField variant="gray" label={t('pages.loan_orders.reference_label')} value={lo.location.landmark} />
            )}
          </div>
        </>
      )}

      {/* Datas */}
      <SectionTitle>{t('pages.loan_orders.section_status')}</SectionTitle>
      <div className="grid grid-cols-2 gap-x-6 gap-y-4">
        <BaseField variant="gray" label={t('pages.loan_orders.section_created_at')} value={formatDate(lo.created_at)} />
        {lo.approved_at  && <BaseField variant="gray" label={t('pages.loan_orders.section_approved_at')}    value={formatDate(lo.approved_at)} />}
        {lo.checked_out_at && <BaseField variant="gray" label={t('pages.loan_orders.section_checked_out_at')} value={formatDate(lo.checked_out_at)} />}
        {lo.returned_at  && <BaseField variant="gray" label={t('pages.loan_orders.section_returned_at')}    value={formatDate(lo.returned_at)} />}
        {lo.cancelled_at && <BaseField variant="gray" label={t('pages.loan_orders.section_cancelled_at')}   value={formatDate(lo.cancelled_at)} />}
      </div>
    </div>
  );
}

/* ════════════════════════════════════════════════════════════════════
   TAB: EQUIPAMENTO
   ════════════════════════════════════════════════════════════════════ */
const EQ_BADGE = {
  true:  'bg-blue-50 text-blue-700 ring-1 ring-inset ring-blue-200',
  false: 'bg-gray-100 text-gray-500 ring-1 ring-inset ring-gray-200',
};

function EquipmentCard({ eq }) {
  const available = Boolean(eq.is_loanable);
  const badgeCls = EQ_BADGE[String(available)];

  return (
    <div className="rounded-xl border border-gray-100 bg-gray-50 p-4 mb-3 last:mb-0">
      {/* Name + badge */}
      <div className="flex items-start justify-between gap-3 mb-3">
        <div className="flex items-center gap-2 min-w-0">
          <Package size={15} className="text-gray-400 shrink-0" />
          <span className="text-sm font-semibold text-gray-900 truncate">{eq.name}</span>
        </div>
        <span className={`shrink-0 inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium ${badgeCls}`}>
          {available ? t('pages.loan_orders.status_loanable') : t('pages.loan_orders.status_not_loanable')}
        </span>
      </div>

      {/* Metadata row */}
      <div className="flex flex-wrap gap-x-5 gap-y-1.5 text-xs text-gray-500">
        {eq.brand && (
          <span><span className="text-gray-400">{t('pages.loan_orders.brand_label')}</span> {eq.brand}</span>
        )}
        {eq.model && (
          <span><span className="text-gray-400">{t('pages.loan_orders.model_label')}</span> {eq.model}</span>
        )}
        <span>
          <span className="text-gray-400">{t('pages.loan_orders.serial_label')}</span>{' '}
          {eq.serial_number || '—'}
        </span>
        {eq.start_date && (
          <span>
            <span className="text-gray-400">{t('pages.loan_orders.period_label')}</span>{' '}
            {formatDate(eq.start_date)}
            {eq.end_date && ` — ${formatDate(eq.end_date)}`}
          </span>
        )}
      </div>

      {/* Operator indicator */}
      {eq.needs_operator && (
        <div className="mt-2.5 inline-flex items-center gap-1.5 rounded-md bg-brand-accent/10 px-2.5 py-1 text-xs font-medium text-brand-accent">
          <CheckCircle size={12} />
          {t('pages.loan_orders.needs_operator')}
        </div>
      )}

      {/* Description */}
      {eq.description && (
        <p className="mt-2.5 text-xs text-gray-500 leading-relaxed border-t border-gray-100 pt-2.5">
          {eq.description}
        </p>
      )}
    </div>
  );
}

function EquipmentTab({ lo }) {
  const equipments = lo.equipments ?? [];
  if (!equipments.length) {
    return (
      <div className="flex flex-col items-center justify-center py-12 text-gray-400">
        <Package size={32} className="mb-2 opacity-40" />
        <p className="text-sm">{t('pages.loan_orders.no_equipment_assigned')}</p>
      </div>
    );
  }
  return (
    <div>
      {equipments.map((eq, i) => (
        <EquipmentCard key={eq.id ?? i} eq={eq} />
      ))}
    </div>
  );
}

/* ════════════════════════════════════════════════════════════════════
   TAB: TAREFAS
   ════════════════════════════════════════════════════════════════════ */
function TasksTab({ lo }) {
  const tasks = lo.tasks ?? [];
  if (!tasks.length) {
    return (
      <div className="flex flex-col items-center justify-center py-12 text-gray-400">
        <CheckCircle size={32} className="mb-2 opacity-40" />
        <p className="text-sm">{t('pages.loan_orders.no_tasks')}</p>
      </div>
    );
  }
  return (
    <div className="space-y-3">
      {tasks.map((task) => (
        <div key={task.id} className="rounded-xl border border-gray-100 bg-gray-50 p-4">
          <div className="flex items-center gap-2 mb-2">
            <span className="text-sm font-semibold text-gray-900">{task.reference}</span>
            <StatusBadge value={task.status} />
          </div>
          {task.description && (
            <p className="text-xs text-gray-500 mb-2">{task.description}</p>
          )}
          {task.manager && (
            <p className="text-xs text-gray-400">
              {t('pages.loan_orders.card_manager_label')} <span className="text-gray-600">{task.manager.name}</span>
            </p>
          )}
        </div>
      ))}
    </div>
  );
}

/* ════════════════════════════════════════════════════════════════════
   TAB: HISTÓRICO + AÇÕES
   ════════════════════════════════════════════════════════════════════ */
function HistoryTab({ lo }) {
  const events = [];

  if (lo.created_at) {
    events.push({ date: lo.created_at, label: t('pages.loan_orders.section_created_at'), type: 'neutral' });
  }
  if (lo.approved_at) {
    const label = lo.approved_by
      ? `${t('pages.loan_orders.section_approved_at')} — ${t('pages.loan_orders.section_approved_by')}: ${lo.approved_by.name}`
      : t('pages.loan_orders.section_approved_at');
    events.push({ date: lo.approved_at, label, type: 'success' });
  }
  if (lo.checked_out_at) {
    events.push({ date: lo.checked_out_at, label: t('pages.loan_orders.section_checked_out_at'), type: 'info' });
  }
  if (lo.returned_at) {
    events.push({ date: lo.returned_at, label: t('pages.loan_orders.section_returned_at'), type: 'success' });
  }
  if (lo.cancelled_at) {
    const label = lo.cancelled_by
      ? `${t('pages.loan_orders.section_cancelled_at')} — ${t('pages.loan_orders.section_cancelled_by')}: ${lo.cancelled_by.name}`
      : t('pages.loan_orders.section_cancelled_at');
    events.push({ date: lo.cancelled_at, label, type: 'danger' });
  }

  const dotColor = { neutral: 'bg-gray-300', success: 'bg-green-400', info: 'bg-blue-400', danger: 'bg-red-400' };

  return (
    <div>
      {events.length === 0 ? (
        <p className="text-sm text-gray-400 italic">{t('pages.loan_orders.value_missing')}</p>
      ) : (
        <ol className="relative border-l border-gray-200 ml-2 space-y-5">
          {events.map((ev, i) => (
            <li key={i} className="ml-5">
              <span className={`absolute -left-[5px] flex h-2.5 w-2.5 rounded-full ring-2 ring-white ${dotColor[ev.type]}`} />
              <p className="text-sm font-medium text-gray-700">{ev.label}</p>
              <time className="text-xs text-gray-400">{formatDate(ev.date)}</time>
            </li>
          ))}
        </ol>
      )}

      {/* Notes */}
      {[
        { key: 'notes_cancel',   label: t('pages.loan_orders.section_notes_cancel') },
        { key: 'notes_checkout', label: t('pages.loan_orders.section_notes_checkout') },
        { key: 'notes_return',   label: t('pages.loan_orders.section_notes_return') },
      ].map(({ key, label }) =>
        lo[key] ? (
          <div key={key} className="mt-5 rounded-lg bg-gray-50 border border-gray-100 px-4 py-3">
            <p className="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">{label}</p>
            <p className="text-sm text-gray-700 leading-relaxed">{lo[key]}</p>
          </div>
        ) : null
      )}
    </div>
  );
}

function ActionButtons({ lo, onAction, entityMode = false }) {
  const [loading, setLoading] = useState({});

  const act = useCallback((key, url, confirmMsg, errMsg) => {
    if (!confirm(confirmMsg)) return;
    setLoading((s) => ({ ...s, [key]: true }));
    router.post(url, {}, {
      preserveScroll: true,
      onSuccess: () => { setLoading((s) => ({ ...s, [key]: false })); onAction(); },
      onError:   () => { setLoading((s) => ({ ...s, [key]: false })); alert(errMsg); },
    });
  }, [onAction]);

  const isPending    = lo.status === 'pending';
  const isApproved   = lo.status === 'approved';
  const isCheckedOut = lo.status === 'checked_out';

  // Entity users can only cancel their own pending orders
  if (entityMode) {
    if (!isPending) return null;
    return (
      <div className="mt-6 pt-5 border-t border-gray-100">
        <p className="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">{t('pages.loan_orders.section_actions')}</p>
        <div className="flex flex-wrap gap-3">
          <button
            onClick={() => act('cancel', `/api/loan-orders/${lo.id}/cancel`,
              t('pages.loan_orders.action_cancel_confirm'), t('pages.loan_orders.action_cancel_failed'))}
            disabled={loading.cancel}
            className="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white hover:bg-red-50 hover:border-red-300 hover:text-red-700 disabled:opacity-60 text-gray-700 text-sm font-medium px-4 py-2 transition-colors"
          >
            {loading.cancel ? <Loader2 size={14} className="animate-spin" /> : <XCircle size={14} />}
            {t('pages.loan_orders.action_cancel')}
          </button>
        </div>
      </div>
    );
  }

  if (!isPending && !isApproved && !isCheckedOut) return null;

  return (
    <div className="mt-6 pt-5 border-t border-gray-100">
      <p className="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">{t('pages.loan_orders.section_actions')}</p>
      <div className="flex flex-wrap gap-3">

        {isPending && (
          <button
            onClick={() => act('approve', `/api/loan-orders/${lo.id}/approve`,
              t('pages.loan_orders.action_approve_confirm'), t('pages.loan_orders.action_approve_failed'))}
            disabled={loading.approve}
            className="inline-flex items-center gap-2 rounded-lg bg-green-600 hover:bg-green-700 disabled:opacity-60 text-white text-sm font-medium px-4 py-2 transition-colors"
          >
            {loading.approve ? <Loader2 size={14} className="animate-spin" /> : <CheckCircle size={14} />}
            {t('pages.loan_orders.action_approve')}
          </button>
        )}

        {isApproved && (
          <button
            onClick={() => act('checkout', `/api/loan-orders/${lo.id}/checkout`,
              t('pages.loan_orders.action_checkout_confirm'), t('pages.loan_orders.action_checkout_failed'))}
            disabled={loading.checkout}
            className="inline-flex items-center gap-2 rounded-lg bg-brand-accent hover:bg-brand-accent/90 disabled:opacity-60 text-white text-sm font-medium px-4 py-2 transition-colors"
          >
            {loading.checkout ? <Loader2 size={14} className="animate-spin" /> : <LogOut size={14} />}
            {t('pages.loan_orders.action_checkout')}
          </button>
        )}

        {isCheckedOut && (
          <button
            onClick={() => act('return', `/api/loan-orders/${lo.id}/return`,
              t('pages.loan_orders.action_initiate_return_confirm'), t('pages.loan_orders.action_initiate_return_failed'))}
            disabled={loading.return}
            className="inline-flex items-center gap-2 rounded-lg bg-blue-600 hover:bg-blue-700 disabled:opacity-60 text-white text-sm font-medium px-4 py-2 transition-colors"
          >
            {loading.return ? <Loader2 size={14} className="animate-spin" /> : <RotateCcw size={14} />}
            {t('pages.loan_orders.action_initiate_return')}
          </button>
        )}

        {isPending && (
          <button
            onClick={() => act('cancel', `/api/loan-orders/${lo.id}/cancel`,
              t('pages.loan_orders.action_cancel_confirm'), t('pages.loan_orders.action_cancel_failed'))}
            disabled={loading.cancel}
            className="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white hover:bg-gray-50 disabled:opacity-60 text-gray-700 text-sm font-medium px-4 py-2 transition-colors"
          >
            {loading.cancel ? <Loader2 size={14} className="animate-spin" /> : <XCircle size={14} />}
            {t('pages.loan_orders.action_cancel')}
          </button>
        )}
      </div>
    </div>
  );
}

/* ════════════════════════════════════════════════════════════════════
   EXPORT — Tab definitions consumed by WorkspaceDrawer
   ════════════════════════════════════════════════════════════════════ */
export default function LoanOrderDrawerTabs(loanOrder, { onAction, entityMode = false }) {
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
          <ActionButtons lo={loanOrder} onAction={onAction} entityMode={entityMode} />
        </>
      ),
    },
  ];
}
