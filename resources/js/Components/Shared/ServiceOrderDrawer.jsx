import { useState } from 'react';
import { createPortal } from 'react-dom';
import { Link, usePage } from '@inertiajs/react';
import { Check, ExternalLink, Play } from 'lucide-react';
import WorkspaceDrawer from '@/Components/Drawer/WorkspaceDrawer';
import BaseField from '@/Components/Shared/Drawer/BaseField';
import LocationMap from '@/Components/Shared/LocationMap';
import { badgeStyle, labelFor } from '@/utils/enums';
import { t } from '@/utils/i18n';
import { formatAbsolute } from '@/utils/format';
import { useOptimisticMutation } from '@/composables/useOptimisticMutation';

function Badge({ value }) {
  return (
    <span className={`inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold ${badgeStyle(value)}`}>
      {labelFor(value)}
    </span>
  );
}

function ConfirmDialog({ open, onConfirm, onCancel }) {
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
        <div className="flex justify-end gap-3">
          <button
            type="button"
            onClick={onCancel}
            className="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50"
          >
            {t('pages.service_orders.activate_confirm_cancel')}
          </button>
          <button
            type="button"
            onClick={onConfirm}
            className="px-4 py-2 text-sm font-medium text-white bg-brand-accent rounded-lg hover:opacity-90"
          >
            {t('pages.service_orders.activate_confirm_ok')}
          </button>
        </div>
      </div>
    </div>,
    document.body
  );
}

function DetailTab({ order }) {
  const createdAt = formatAbsolute(order?.created_at) || null;
  const startDate = formatAbsolute(order?.start_date) || null;
  const endDate = formatAbsolute(order?.end_date) || null;

  return (
    <div className="space-y-5">
      <div className="grid grid-cols-2 gap-x-6 gap-y-5">
        <BaseField variant="gray" label={t('pages.service_orders.drawer.field_client')}>{order?.client?.name}</BaseField>
        <BaseField variant="gray" label={t('pages.service_orders.drawer.field_manager')}>{order?.manager?.name}</BaseField>
        <BaseField variant="gray" label={t('pages.service_orders.drawer.field_created_at')}>{createdAt}</BaseField>
        <BaseField variant="gray" label={t('pages.service_orders.drawer.field_start_date')}>{startDate}</BaseField>
        <BaseField variant="gray" label={t('pages.service_orders.drawer.field_end_date')}>{endDate}</BaseField>
        <BaseField variant="gray" label={t('pages.service_orders.drawer.field_service_type')}>{order?.service_type?.name}</BaseField>
      </div>

      {order?.location && (
        <div>
          <span className="text-xs font-medium uppercase tracking-wider text-gray-400">{t('pages.service_orders.drawer.field_location')}</span>
          <div className="mt-1">
            <LocationMap location={order.location} />
          </div>
        </div>
      )}

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

export default function ServiceOrderDrawer({ order, isOpen, onClose, loading, onActivated, onCompleted, stacked = false }) {
  const { props: { auth, can } } = usePage();
  const authUser = auth?.user;
  const { mutate } = useOptimisticMutation();

  const [showConfirm, setShowConfirm] = useState(false);
  const [optimisticStatus, setOptimisticStatus] = useState(null);

  const rawStatus = order?.status?.value ?? order?.status;
  const effectiveStatus = optimisticStatus ?? rawStatus;
  const isManager = authUser?.id && order?.manager?.id && String(authUser.id) === String(order.manager.id);
  const canActivate = effectiveStatus === 'pending' && (can?.activateServiceOrder || isManager);
  const canComplete = effectiveStatus === 'awaiting_approval' && (can?.completeServiceOrder || isManager);

  const handleActivate = async () => {
    const prev = rawStatus;
    setShowConfirm(false);
    setOptimisticStatus('in_progress');
    await mutate({
      url: `/api/service-orders/${order.id}/activate`,
      onSuccess: () => { setOptimisticStatus(null); onActivated?.(); },
      onError: () => setOptimisticStatus(prev),
      errorMessage: t('pages.service_orders.activate_failed'),
    });
  };

  const handleComplete = async () => {
    const prev = rawStatus;
    setOptimisticStatus('completed');
    await mutate({
      url: `/api/service-orders/${order.id}/complete`,
      onSuccess: () => { setOptimisticStatus(null); onCompleted?.(); },
      onError: () => setOptimisticStatus(prev),
      errorMessage: t('pages.service_orders.complete_failed'),
    });
  };

  const activateButton = canActivate ? (
    <button
      type="button"
      onClick={() => setShowConfirm(true)}
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
      className="inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-semibold text-white bg-green-600 hover:bg-green-700 transition-colors"
    >
      <Check size={12} />
      {t('pages.service_orders.btn_complete')}
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
        subtitle={order ? (
          <div>
            <div className="flex items-center gap-2 flex-wrap mb-1">
              <Badge value={effectiveStatus} />
              <Badge value={order.priority?.value ?? order.priority} />
            </div>
            {order.client?.name && (
              <span className="text-sm text-brand-mid">{t('pages.service_orders.drawer_client_label')} {order.client.name}</span>
            )}
          </div>
        ) : ''}
        tabs={tabs}
        headerActions={<>{completeButton}{activateButton}</>}
        stacked={stacked}
      />
      <ConfirmDialog
        open={showConfirm}
        onConfirm={handleActivate}
        onCancel={() => setShowConfirm(false)}
      />
    </>
  );
}
