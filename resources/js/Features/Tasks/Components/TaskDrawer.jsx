import { useState, useEffect } from 'react';
import { usePage } from '@inertiajs/react';
import { Check, XCircle } from 'lucide-react';
import WorkspaceDrawer from '@/Components/Drawer/WorkspaceDrawer';
import DialogModal from '@/Components/Common/DialogModal';
import { t } from '@/utils/i18n';
import { csrfHeader } from '@/utils/csrf';
import { useToast } from '@/Components/Toast/ToastContext';

function Field({ label, children }) {
    return (
        <div className="flex flex-col gap-1">
            <span className="text-xs font-medium uppercase tracking-wide text-brand-mid">{label}</span>
            <span className="text-sm text-brand-darkest">{children ?? <span className="text-brand-mid">—</span>}</span>
        </div>
    );
}

function StatusBadge({ status }) {
    const map = {
        pending:           'bg-brand-light text-brand-mid',
        awaiting_approval: 'bg-amber-900/40 text-amber-300',
        in_progress:       'bg-brand-accent/15 text-brand-accent',
        completed:         'bg-emerald-900/60 text-emerald-300',
        blocked:           'bg-red-900/60 text-red-300',
        cancelled:         'bg-zinc-800 text-zinc-400',
    };
    const labels = {
        pending:           t('pages.tasks.drawer.status_pending'),
        awaiting_approval: t('pages.tasks.drawer.status_awaiting_approval'),
        in_progress:       t('pages.tasks.drawer.status_in_progress'),
        completed:         t('pages.tasks.drawer.status_completed'),
        blocked:           t('pages.tasks.drawer.status_blocked'),
        cancelled:         t('pages.tasks.drawer.status_cancelled'),
    };
    const cls = map[status?.value ?? status] ?? 'bg-brand-light text-brand-mid';
    const label = labels[status?.value ?? status] ?? (status?.label ?? status ?? '—');
    return (
        <span className={`inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium ${cls}`}>
            {label}
        </span>
    );
}

function GeneralTab({ item }) {
    const sectors = item.sectors?.map(s => s.name).join(', ') || null;
    const createdAt = item.created_at
        ? new Date(item.created_at).toLocaleDateString('pt-PT', { day: '2-digit', month: '2-digit', year: 'numeric' })
        : null;

    return (
        <div className="grid grid-cols-2 gap-6">
            <Field label={t('pages.tasks.drawer.field_reference')}>
                <span className="font-mono text-brand-accent">{item.reference}</span>
            </Field>
            <Field label={t('pages.tasks.drawer.field_status')}>
                <StatusBadge status={item.status} />
            </Field>
            <Field label={t('pages.tasks.drawer.field_service_order')}>
                {item.service_order?.process ?? null}
            </Field>
            <Field label={t('pages.tasks.drawer.field_manager')}>
                {item.manager?.name ?? null}
            </Field>
            <Field label={t('pages.tasks.drawer.field_sectors')}>
                {sectors}
            </Field>
            <Field label={t('pages.tasks.drawer.field_created_at')}>
                {createdAt}
            </Field>
            <div className="col-span-2">
                <Field label={t('pages.tasks.drawer.field_description')}>
                    {item.description
                        ? <p className="whitespace-pre-wrap leading-relaxed">{item.description}</p>
                        : null}
                </Field>
            </div>
        </div>
    );
}

function MiniTasksTab({ miniTasks = [] }) {

    if (!miniTasks.length) {
        return (
            <p className="text-sm text-brand-mid text-center py-12">
                {t('pages.tasks.drawer.no_mini_tasks')}
            </p>
        );
    }

    return (
        <div className="overflow-x-auto">
            <table className="w-full text-sm">
                <thead>
                    <tr className="border-b border-brand-mid/20">
                        <th className="pb-2 pr-4 text-left text-xs font-medium uppercase tracking-wide text-brand-mid">{t('pages.tasks.drawer.th_reference')}</th>
                        <th className="pb-2 text-left text-xs font-medium uppercase tracking-wide text-brand-mid">{t('pages.tasks.drawer.th_status')}</th>
                    </tr>
                </thead>
                <tbody className="divide-y divide-brand-mid/10">
                    {miniTasks.map(mt => (
                        <tr key={mt.id} className="hover:bg-brand-light transition-colors">
                            <td className="py-2.5 pr-4 font-mono text-brand-accent">{mt.reference}</td>
                            <td className="py-2.5">
                                <StatusBadge status={mt.status} />
                            </td>
                        </tr>
                    ))}
                </tbody>
            </table>
        </div>
    );
}

function RejectionsTab({ taskId }) {
    const [rejections, setRejections] = useState(null);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        let cancelled = false;
        setLoading(true);
        fetch(`/api/tasks/${taskId}/rejections`, {
            headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        })
            .then(res => res.ok ? res.json() : Promise.reject(res))
            .then(json => { if (!cancelled) setRejections(json.data ?? []); })
            .catch(() => { if (!cancelled) setRejections([]); })
            .finally(() => { if (!cancelled) setLoading(false); });
        return () => { cancelled = true; };
    }, [taskId]);

    if (loading) {
        return <p className="text-sm text-brand-mid text-center py-12">{t('pages.common.loading')}</p>;
    }

    if (!rejections.length) {
        return (
            <p className="text-sm text-brand-mid text-center py-12">
                {t('pages.tasks.drawer.no_rejections')}
            </p>
        );
    }

    const formatDate = (iso) => {
        if (!iso) return '—';
        return new Date(iso).toLocaleDateString('pt-PT', {
            day: '2-digit', month: '2-digit', year: 'numeric',
            hour: '2-digit', minute: '2-digit',
        });
    };

    return (
        <div className="overflow-x-auto">
            <table className="w-full text-sm">
                <thead>
                    <tr className="border-b border-brand-mid/20">
                        <th className="pb-2 pr-4 text-left text-xs font-medium uppercase tracking-wide text-brand-mid">{t('pages.tasks.drawer.th_date')}</th>
                        <th className="pb-2 pr-4 text-left text-xs font-medium uppercase tracking-wide text-brand-mid">{t('pages.tasks.drawer.th_rejected_by')}</th>
                        <th className="pb-2 text-left text-xs font-medium uppercase tracking-wide text-brand-mid">{t('pages.tasks.drawer.th_reason')}</th>
                    </tr>
                </thead>
                <tbody className="divide-y divide-brand-mid/10">
                    {rejections.map(r => (
                        <tr key={r.id} className="hover:bg-brand-light transition-colors">
                            <td className="py-2.5 pr-4 text-brand-mid">{formatDate(r.created_at)}</td>
                            <td className="py-2.5 pr-4">{r.rejected_by?.name ?? '—'}</td>
                            <td className="py-2.5">{r.reason}</td>
                        </tr>
                    ))}
                </tbody>
            </table>
        </div>
    );
}

export default function TaskDrawer({ isOpen, onClose, item, loading, onCompleted }) {
    const { props: pageProps } = usePage();
    const authUser = pageProps?.auth?.user;
    const toast = useToast();

    const [completing, setCompleting] = useState(false);
    const [showRejectModal, setShowRejectModal] = useState(false);
    const [rejectReason, setRejectReason] = useState('');
    const [rejecting, setRejecting] = useState(false);

    const status = item?.status?.value ?? item?.status;
    const isAdmin = authUser?.roles?.some(r => r.name === 'admin');
    const isManager = authUser?.id && item?.manager?.id && String(authUser.id) === String(item.manager.id);
    const canComplete = status === 'awaiting_approval' && (isAdmin || isManager);
    const canReject = status === 'awaiting_approval' && (isAdmin || isManager);

    const handleComplete = async () => {
        setCompleting(true);
        try {
            const res = await fetch(`/api/tasks/${item.id}/complete`, {
                method: 'POST',
                headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest', ...csrfHeader() },
            });
            if (!res.ok) throw new Error();
            onCompleted?.();
        } finally {
            setCompleting(false);
        }
    };

    const handleReject = async () => {
        setRejecting(true);
        try {
            const res = await fetch(`/api/tasks/${item.id}/reject`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest', ...csrfHeader() },
                body: JSON.stringify({ reason: rejectReason }),
            });
            if (!res.ok) {
                const body = await res.json().catch(() => ({}));
                throw new Error(body.message || t('pages.tasks.drawer.reject_error'));
            }
            setShowRejectModal(false);
            setRejectReason('');
            onCompleted?.();
        } catch (err) {
            toast.error(err.message || t('pages.tasks.drawer.reject_error'));
        } finally {
            setRejecting(false);
        }
    };

    const rejectButton = canReject ? (
        <button
            type="button"
            onClick={() => { setRejectReason(''); setShowRejectModal(true); }}
            className="inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-semibold text-white bg-red-600 hover:bg-red-700 transition-colors"
        >
            <XCircle size={12} />
            {t('pages.tasks.drawer.btn_reject')}
        </button>
    ) : null;

    const completeButton = canComplete ? (
        <button
            type="button"
            onClick={handleComplete}
            disabled={completing}
            className="inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-semibold text-white bg-brand-accent hover:opacity-90 transition-opacity disabled:opacity-50"
        >
            <Check size={12} />
            {completing ? '…' : t('pages.tasks.drawer.btn_complete')}
        </button>
    ) : null;

    const headerActions = (
        <div className="flex items-center gap-2">
            {rejectButton}
            {completeButton}
        </div>
    );

    const tabs = item ? [
        { id: 'general',     label: t('pages.tasks.drawer.tab_general'),    component: <GeneralTab item={item} /> },
        { id: 'mini_tasks',  label: t('pages.tasks.drawer.tab_mini_tasks'),  component: <MiniTasksTab miniTasks={item.mini_tasks} /> },
        { id: 'rejections',  label: t('pages.tasks.drawer.tab_rejections'),  component: <RejectionsTab taskId={item.id} /> },
    ] : [];

    return (
        <>
            <WorkspaceDrawer
                isOpen={isOpen}
                onClose={onClose}
                title={item?.reference ?? ''}
                subtitle={loading ? t('pages.common.loading') : undefined}
                tabs={tabs}
                headerActions={headerActions}
            />
            <DialogModal
                open={showRejectModal}
                onClose={() => { if (!rejecting) { setShowRejectModal(false); setRejectReason(''); } }}
                type="confirm"
                title={t('pages.tasks.drawer.reject_title')}
                description={t('pages.tasks.drawer.reject_description')}
                buttons={[
                    { label: t('pages.datamanager.cancel_btn'), onClick: () => { setShowRejectModal(false); setRejectReason(''); }, variant: 'secondary', disabled: rejecting },
                    { label: rejecting ? t('pages.tasks.drawer.btn_rejecting') : t('pages.tasks.drawer.btn_reject'), onClick: handleReject, variant: 'primary', disabled: !rejectReason.trim() || rejecting },
                ]}
            >
                <label className="flex flex-col gap-1.5">
                    <span className="text-xs font-medium text-brand-darkest">{t('pages.tasks.drawer.reject_reason_label')}</span>
                    <textarea
                        className="w-full rounded-lg border border-brand-mid/20 bg-brand-light/30 px-3 py-2 text-sm text-brand-darkest placeholder:text-brand-mid focus:outline-none focus:ring-2 focus:ring-brand-accent/30"
                        rows={4}
                        placeholder={t('pages.tasks.drawer.reject_reason_placeholder')}
                        value={rejectReason}
                        onChange={(e) => setRejectReason(e.target.value)}
                        disabled={rejecting}
                        autoFocus
                    />
                </label>
            </DialogModal>
        </>
    );
}
