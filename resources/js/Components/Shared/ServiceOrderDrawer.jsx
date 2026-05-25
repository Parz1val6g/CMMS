import { useState } from 'react';
import { createPortal } from 'react-dom';
import { Link, usePage } from '@inertiajs/react';
import { Check, ExternalLink, Play } from 'lucide-react';
import WorkspaceDrawer from '@/Components/Drawer/WorkspaceDrawer';
import BaseField from '@/Components/Shared/Drawer/BaseField';
import { t } from '@/utils/i18n';
import { csrfHeader } from '@/utils/csrf';

const PRIORITY_BADGE = {
  urgent: 'bg-red-100 text-red-800 ring-1 ring-inset ring-red-300/60',
  high:   'bg-orange-100 text-orange-800 ring-1 ring-inset ring-orange-300/60',
  normal: 'bg-gray-100 text-gray-700 ring-1 ring-inset ring-gray-300/60',
  low:    'bg-blue-50 text-blue-700 ring-1 ring-inset ring-blue-300/60',
};

const PRIORITY_LABEL = {
  urgent: t('pages.service_orders.drawer.priority_urgent'),
  high: t('pages.service_orders.drawer.priority_high'),
  normal: t('pages.service_orders.drawer.priority_normal'),
  low: t('pages.service_orders.drawer.priority_low'),
};

const STATUS_BADGE = {
  pending:           'bg-yellow-50 text-yellow-800 ring-1 ring-inset ring-yellow-300/60',
  awaiting_approval: 'bg-amber-50 text-amber-800 ring-1 ring-inset ring-amber-300/60',
  in_progress:       'bg-blue-50 text-blue-800 ring-1 ring-inset ring-blue-300/60',
  completed:         'bg-green-50 text-green-800 ring-1 ring-inset ring-green-300/60',
  cancelled:         'bg-red-50 text-red-800 ring-1 ring-inset ring-red-300/60',
};

const STATUS_LABEL = {
  pending: t('pages.service_orders.drawer.status_pending'),
  awaiting_approval: t('pages.service_orders.drawer.status_awaiting_approval'),
  in_progress: t('pages.service_orders.drawer.status_in_progress'),
  completed: t('pages.service_orders.drawer.status_completed'),
  cancelled: t('pages.service_orders.drawer.status_cancelled'),
};

function Badge({ map, labelMap, value }) {
  const cls = map[value] ?? 'bg-gray-100 text-gray-600';
  return (
    <span className={`inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium ${cls}`}>
      {labelMap[value] ?? value ?? '—'}
    </span>
  );
}

function ConfirmDialog({ open, onConfirm, onCancel, loading, error }) {
  if (!open) return null;
  return createPortal(
    <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/40">
      <div className="bg-white rounded-xl shadow-xl p-6 w-full max-w-sm mx-4">
        <h3 className="text-base font-semibold text-gray-900 mb-2">
          {t('pages.service_orders.activate_confirm_title')}
        </h3>
        <p className="text-sm text-gray-600 mb-5">
          {t('pages.service_orders.activate_confirm_body')}
        </p>
        {error && (
          <p className="text-sm text-red-600 mb-4">
            {t('pages.service_orders.activate_failed')}
          </p>
        )}
        <div className="flex justify-end gap-3">
          <button
            type="button"
            onClick={onCancel}
            disabled={loading}
            className="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-50"
          >
            {t('pages.service_orders.activate_confirm_cancel')}
          </button>
          <button
            type="button"
            onClick={onConfirm}
            disabled={loading}
            className="px-4 py-2 text-sm font-medium text-white bg-brand-accent rounded-lg hover:opacity-90 disabled:opacity-50"
          >
            {loading ? '…' : t('pages.service_orders.activate_confirm_ok')}
          </button>
        </div>
      </div>
    </div>,
    document.body
  );
}

function DetailTab({ order }) {
  const createdAt = order?.created_at
    ? new Date(order.created_at).toLocaleDateString('pt-PT', { day: '2-digit', month: '2-digit', year: 'numeric' })
    : null;

  const executionDate = order?.execution_date
    ? new Date(order.execution_date).toLocaleDateString('pt-PT', { day: '2-digit', month: '2-digit', year: 'numeric' })
    : null;

  return (
    <div className="space-y-5">
      <div className="grid grid-cols-2 gap-x-6 gap-y-5">
        <BaseField variant="gray" label={t('pages.service_orders.drawer.field_status')}>
          <Badge map={STATUS_BADGE} labelMap={STATUS_LABEL} value={order?.status?.value ?? order?.status} />
        </BaseField>
        <BaseField variant="gray" label={t('pages.service_orders.drawer.field_priority')}>
          <Badge map={PRIORITY_BADGE} labelMap={PRIORITY_LABEL} value={order?.priority?.value ?? order?.priority} />
        </BaseField>
        <BaseField variant="gray" label={t('pages.service_orders.drawer.field_manager')}>{order?.manager?.name}</BaseField>
        <BaseField variant="gray" label={t('pages.service_orders.drawer.field_created_at')}>{createdAt}</BaseField>
        <BaseField variant="gray" label={t('pages.service_orders.drawer.field_execution_date')}>{executionDate}</BaseField>
        <BaseField variant="gray" label={t('pages.service_orders.drawer.field_service_type')}>{order?.service_type?.name}</BaseField>
        <BaseField variant="gray" label={t('pages.service_orders.drawer.field_location')}>{order?.location?.parish?.name}</BaseField>
      </div>

      {order?.description && (
        <div>
          <span className="text-xs font-medium uppercase tracking-wider text-gray-400">{t('pages.service_orders.drawer.field_description')}</span>
          <p className="mt-1 text-sm text-gray-700 leading-relaxed whitespace-pre-wrap">{order.description}</p>
        </div>
      )}

      <div className="pt-2 border-t border-gray-100">
        <Link
          href={`/service-orders`}
          className="inline-flex items-center gap-1.5 text-sm font-medium text-brand-accent hover:underline"
        >
          <ExternalLink size={14} />
          {t('pages.service_orders.drawer.view_link')}
        </Link>
      </div>
    </div>
  );
}

export default function ServiceOrderDrawer({ order, isOpen, onClose, loading, onActivated, onCompleted }) {
  const { props: pageProps } = usePage();
  const authUser = pageProps?.auth?.user;

  const [showConfirm, setShowConfirm] = useState(false);
  const [activating, setActivating] = useState(false);
  const [activateError, setActivateError] = useState(false);
  const [completing, setCompleting] = useState(false);

  const status = order?.status?.value ?? order?.status;
  const isAdmin = authUser?.roles?.some(r => r.name === 'admin');
  const isManager = authUser?.id && order?.manager?.id && String(authUser.id) === String(order.manager.id);
  const canActivate = status === 'pending' && (isAdmin || isManager);
  const canComplete = status === 'awaiting_approval' && (isAdmin || isManager);

  const handleActivate = async () => {
    setActivating(true);
    setActivateError(false);
    try {
      const res = await fetch(`/api/service-orders/${order.id}/activate`, {
        method: 'POST',
        headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest', ...csrfHeader() },
      });
      if (!res.ok) throw new Error();
      setShowConfirm(false);
      onActivated?.();
    } catch {
      setActivateError(true);
    } finally {
      setActivating(false);
    }
  };

  const handleOpenConfirm = () => {
    setActivateError(false);
    setShowConfirm(true);
  };

  const handleComplete = async () => {
    setCompleting(true);
    try {
      const res = await fetch(`/api/service-orders/${order.id}/complete`, {
        method: 'POST',
        headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest', ...csrfHeader() },
      });
      if (!res.ok) throw new Error();
      onCompleted?.();
    } finally {
      setCompleting(false);
    }
  };

  const activateButton = canActivate ? (
    <button
      type="button"
      onClick={handleOpenConfirm}
      className="inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-semibold text-white bg-brand-accent hover:opacity-90 transition-opacity"
    >
      <Play size={12} />
      {t('pages.service_orders.btn_activate')}
    </button>
  ) : null;

  const completeButton = canComplete ? (
    <button
      type="button"
      onClick={handleComplete}
      disabled={completing}
      className="inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-semibold text-white bg-green-600 hover:bg-green-700 transition-colors disabled:opacity-50"
    >
      <Check size={12} />
      {completing ? '…' : t('pages.service_orders.btn_complete')}
    </button>
  ) : null;

  const tabs = loading || !order
    ? [{ id: 'loading', label: '...', component: <div className="py-12 text-center text-sm text-gray-400">{t('common.loading')}</div> }]
    : [{ id: 'details', label: t('pages.service_orders.drawer.tab_details'), component: <DetailTab order={order} /> }];

  return (
    <>
      <WorkspaceDrawer
        isOpen={isOpen}
        onClose={onClose}
        title={order?.process ?? ''}
        subtitle={order ? (STATUS_LABEL[order.status?.value ?? order.status] ?? '') : ''}
        tabs={tabs}
        headerActions={<>{completeButton}{activateButton}</>}
      />
      <ConfirmDialog
        open={showConfirm}
        onConfirm={handleActivate}
        onCancel={() => setShowConfirm(false)}
        loading={activating}
        error={activateError}
      />
    </>
  );
}
