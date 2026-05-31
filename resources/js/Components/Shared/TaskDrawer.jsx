import { useState, useEffect, useCallback, useMemo } from 'react';
import { usePage } from '@inertiajs/react';
import { Check, XCircle, Plus, X as XIcon, ExternalLink } from 'lucide-react';
import WorkspaceDrawer from '@/Components/Drawer/WorkspaceDrawer';
import ServiceOrderDrawer from '@/Components/Shared/ServiceOrderDrawer';
import DialogModal from '@/Components/Common/DialogModal';
import BaseField from '@/Components/Shared/Drawer/BaseField';
import FormField from '@/Components/Common/FormField';
import { t } from '@/utils/i18n';
import { csrfHeader } from '@/utils/csrf';
import { useToast } from '@/Components/Toast/ToastContext';

function StatusBadge({ status }) {
    const map = {
        pending:           'bg-brand-mid/10 text-brand-mid ring-1 ring-inset ring-brand-mid/25',
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

function GeneralTab({ item, canViewServiceOrders, onOpenServiceOrder }) {
    const sectors = item.sectors?.map(s => s.name).join(', ') || null;
    const createdAt = item.created_at
        ? new Date(item.created_at).toLocaleDateString('pt-PT', { day: '2-digit', month: '2-digit', year: 'numeric' })
        : null;
    const startDate = item.start_date
        ? new Date(item.start_date + 'T00:00:00').toLocaleDateString('pt-PT', { day: '2-digit', month: '2-digit', year: 'numeric' })
        : null;
    const endDate = item.end_date
        ? new Date(item.end_date + 'T00:00:00').toLocaleDateString('pt-PT', { day: '2-digit', month: '2-digit', year: 'numeric' })
        : null;

    const soProcess = item.service_order?.process;
    const soId = item.service_order?.id;
    const canViewSO = canViewServiceOrders && soId;

    return (
        <div className="grid grid-cols-2 gap-6">
            <BaseField label={t('pages.tasks.drawer.field_service_order')}>
                {canViewSO ? (
                    <button
                        type="button"
                        onClick={() => onOpenServiceOrder(soId)}
                        className="inline-flex items-center gap-1 font-mono text-brand-accent hover:text-brand-darkest hover:underline transition-colors cursor-pointer"
                    >
                        {soProcess}
                        <ExternalLink size={13} />
                    </button>
                ) : (
                    soProcess ?? null
                )}
            </BaseField>
            <BaseField label={t('pages.tasks.drawer.field_manager')}>
                {item.manager?.name ?? null}
            </BaseField>
            <BaseField label={t('pages.tasks.drawer.field_sectors')}>
                {sectors}
            </BaseField>
            <BaseField label={t('pages.tasks.drawer.field_created_at')}>
                {createdAt}
            </BaseField>
            <BaseField label={t('forms.tasks.start_date')}>
                {startDate}
            </BaseField>
            <BaseField label={t('forms.tasks.end_date')}>
                {endDate}
            </BaseField>
            <div className="col-span-2">
                <BaseField label={t('pages.tasks.drawer.field_description')}>
                    {item.description
                        ? <p className="whitespace-pre-wrap leading-relaxed">{item.description}</p>
                        : null}
                </BaseField>
            </div>
        </div>
    );
}

function MaterialQuantityInput({ options = [], value = [], onChange, stockMap = {}, label = 'Materiais' }) {
    const selectedIds = value.map(s => s.material_id);
    const unselected = options.filter(o => !selectedIds.includes(o.value));
    const getOpt = (id) => options.find(o => o.value === id);

    const inStock = (o) => {
        const stock = stockMap[o.value] ?? o.stock;
        return stock != null && stock > 0;
    };

    const availableToAdd = unselected.filter(inStock);

    const handleAdd = (e) => {
        const materialId = e.target.value;
        if (!materialId) return;
        e.target.value = '';
        onChange([...value, { material_id: materialId, planned_quantity: '' }]);
    };

    const handleQty = (materialId, qty, maxStock, step = 1) => {
        const parsed = parseFloat(qty);
        if (isNaN(parsed)) {
            onChange(value.map(s => s.material_id === materialId ? { ...s, planned_quantity: '' } : s));
            return;
        }
        // Snap to nearest valid step, then clamp to [0, maxStock]
        const snapped = step >= 1 ? Math.round(parsed) : Math.round(parsed / step) * step;
        const clamped = Math.min(Math.max(0, snapped), maxStock ?? Infinity);
        // Format: no decimals for step=1, fixed decimals otherwise
        const formatted = step >= 1 ? String(Math.round(clamped)) : String(parseFloat(clamped.toFixed(2)));
        onChange(value.map(s => s.material_id === materialId ? { ...s, planned_quantity: formatted } : s));
    };

    const handleRemove = (materialId) => {
        onChange(value.filter(s => s.material_id !== materialId));
    };

    return (
        <div className="mb-4">
            <label className="block text-sm font-medium text-brand-mid mb-1.5">{label}</label>

            {availableToAdd.length > 0 && (
                <select
                    className="block w-full rounded-lg border border-brand-mid/20 bg-brand-white px-3 py-2 text-sm text-brand-darkest focus:ring-1 focus:border-brand-accent focus:ring-brand-accent mb-2"
                    defaultValue=""
                    onChange={handleAdd}
                >
                    <option value="" disabled>Adicionar material…</option>
                    {availableToAdd.map(o => (
                        <option key={o.value} value={o.value}>{o.label}</option>
                    ))}
                </select>
            )}

            {value.map(item => {
                const opt = getOpt(item.material_id);
                if (!opt) return null;
                const stock = stockMap[item.material_id] ?? opt.stock;
                return (
                    <div key={item.material_id} className="flex items-center gap-2 rounded-lg border border-brand-mid/20 bg-brand-white px-3 py-2 mb-1.5">
                        <span className="flex-1 text-sm text-brand-darkest truncate">{opt.label}</span>
                        {stock != null && (
                            <span className="shrink-0 text-xs text-brand-mid">max: {stock}{opt.unit ? ' ' + opt.unit : ''}</span>
                        )}
                        <input
                            type="number"
                            min="0"
                            max={stock ?? undefined}
                            step={opt.step ?? 1}
                            value={item.planned_quantity}
                            onChange={e => handleQty(item.material_id, e.target.value, stock, opt.step ?? 1)}
                            placeholder="0"
                            className="w-20 shrink-0 rounded-md border border-brand-mid/20 bg-brand-light px-2 py-1 text-sm text-brand-darkest focus:outline-none focus:ring-1 focus:ring-brand-accent"
                        />
                        {opt.unit && (
                            <span className="shrink-0 text-xs text-brand-mid w-6">{opt.unit}</span>
                        )}
                        <button
                            type="button"
                            onClick={() => handleRemove(item.material_id)}
                            className="shrink-0 text-brand-mid hover:text-red-500 transition-colors"
                        >
                            <XIcon size={14} />
                        </button>
                    </div>
                );
            })}

            {value.length === 0 && availableToAdd.length === 0 && (
                <p className="text-xs text-brand-mid">Sem materiais com stock disponível.</p>
            )}
        </div>
    );
}

function MiniTasksTab({ miniTasks = [], taskId, schema, onCreated, hasPeriod = true }) {
    const [showForm, setShowForm] = useState(false);
    const [saving, setSaving] = useState(false);
    const [errors, setErrors] = useState({});
    const [formValues, setFormValues] = useState({});
    const [availability, setAvailability] = useState(null);
    const [availabilityLoading, setAvailabilityLoading] = useState(false);
    const toast = useToast();

    // Fields from schema, excluding task_id and material_ids (handled separately)
    const allFields = useMemo(() => {
        const inputs = Array.isArray(schema) ? schema : (schema?.inputs ?? []);
        return inputs.filter(f => (f.name ?? f.key) !== 'task_id');
    }, [schema]);

    const materialField = useMemo(
        () => allFields.find(f => (f.name ?? f.key) === 'material_ids'),
        [allFields]
    );

    const fields = useMemo(
        () => allFields.filter(f => (f.name ?? f.key) !== 'material_ids'),
        [allFields]
    );

    const handleChange = useCallback((name, value) => {
        if (name === 'date_range') {
            // Keep the combined object for the controlled DatePicker,
            // and split into start_date / end_date for the availability check + submit.
            setFormValues(prev => ({
                ...prev,
                date_range: value,
                start_date: value?.start ?? '',
                end_date:   value?.end   ?? '',
            }));
        } else {
            setFormValues(prev => ({ ...prev, [name]: value }));
        }
    }, []);

    // Fetch availability whenever start_date or end_date changes
    useEffect(() => {
        const { start_date, end_date } = formValues;
        if (!start_date || !end_date) { setAvailability(null); return; }
        let cancelled = false;
        setAvailabilityLoading(true);
        fetch(`/api/mini-tasks/availability?start_date=${start_date}&end_date=${end_date}`, {
            headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        })
            .then(r => r.ok ? r.json() : Promise.reject())
            .then(data => { if (!cancelled) setAvailability(data); })
            .catch(() => {})
            .finally(() => { if (!cancelled) setAvailabilityLoading(false); });
        return () => { cancelled = true; };
    }, [formValues.start_date, formValues.end_date]);

    // Enrich worker/team/equipment options with availability data
    const enrichedFields = useMemo(() => {
        if (!availability) return fields;
        return fields.map(field => {
            const name = field.name ?? field.key;
            if (!field.options) return field;
            let options = field.options;
            if (name === 'worker_ids') {
                options = options.map(o => ({ ...o, unavailable: availability.busy_worker_ids?.includes(o.value) }));
            } else if (name === 'team_ids') {
                options = options.map(o => ({ ...o, unavailable: availability.busy_team_ids?.includes(o.value) }));
            } else if (name === 'equipment_ids') {
                options = options.map(o => ({ ...o, unavailable: availability.busy_equipment_ids?.includes(o.value) }));
            }
            return { ...field, options };
        });
    }, [fields, availability]);

    const handleOpen = () => {
        setShowForm(true);
        setErrors({});
        setFormValues({});
        setAvailability(null);
    };

    const handleClose = () => {
        setShowForm(false);
        setErrors({});
        setFormValues({});
        setAvailability(null);
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        setSaving(true);
        setErrors({});
        try {
            const res = await fetch('/api/mini-tasks', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    ...csrfHeader(),
                },
                body: JSON.stringify((() => {
                                    // eslint-disable-next-line no-unused-vars
                                    const { date_range: _dr, ...rest } = formValues;
                                    return {
                                        ...rest,
                                        task_id: taskId,
                                        materials: (formValues.materials ?? [])
                                            .map(m => ({ material_id: m.material_id, planned_quantity: parseFloat(m.planned_quantity) || 0 }))
                                            .filter(m => m.planned_quantity > 0),
                                    };
                                })()),
            });
            const body = await res.json();
            if (res.ok) {
                handleClose();
                onCreated?.();
            } else {
                if (body.errors) setErrors(body.errors);
                else toast.error(body.message ?? t('pages.tasks.drawer.mini_task_create_error'));
            }
        } catch {
            toast.error(t('pages.tasks.drawer.mini_task_create_error'));
        } finally {
            setSaving(false);
        }
    };

    return (
        <div className="space-y-4">
            {/* Header */}
            <div className="flex items-center justify-between">
                <span className="text-xs font-medium uppercase tracking-wide text-brand-mid">
                    {t('pages.tasks.drawer.tab_mini_tasks')}
                </span>
                {schema && (
                    <button
                        type="button"
                        onClick={showForm ? handleClose : handleOpen}
                        disabled={!hasPeriod}
                        title={!hasPeriod ? t('pages.tasks.drawer.no_period_tooltip') : undefined}
                        className="inline-flex items-center gap-1 rounded-lg px-2.5 py-1 text-xs font-semibold text-brand-accent border border-brand-accent/30 hover:bg-brand-accent/10 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        {showForm ? <XIcon size={12} /> : <Plus size={12} />}
                        {showForm ? t('pages.tasks.drawer.cancel') : t('pages.tasks.drawer.new_mini_task')}
                    </button>
                )}
            </div>

            {/* Inline create form */}
            {showForm && (
                <form onSubmit={handleSubmit} className="rounded-lg border border-brand-mid/20 bg-brand-light/40 p-4 space-y-3">
                    {Object.keys(errors).length > 0 && (
                        <div className="rounded-lg bg-red-50 p-3 text-xs text-red-600">
                            {Object.entries(errors).map(([k, msgs]) => (
                                <p key={k}>{(Array.isArray(msgs) ? msgs : [msgs]).join(', ')}</p>
                            ))}
                        </div>
                    )}
                    {availabilityLoading && (
                        <p className="text-xs text-brand-mid">A verificar disponibilidade…</p>
                    )}
                    {enrichedFields.map(field => {
                        const name = field.name ?? field.key;
                        const fieldError = name === 'date_range'
                            ? [
                                ...(Array.isArray(errors.start_date) ? errors.start_date : errors.start_date ? [errors.start_date] : []),
                                ...(Array.isArray(errors.end_date)   ? errors.end_date   : errors.end_date   ? [errors.end_date]   : []),
                              ].join(' ') || undefined
                            : (errors[name]?.join?.(' ') ?? errors[name]);
                        return (
                            <div key={name}>
                                <FormField
                                    field={field}
                                    value={formValues[name]}
                                    error={fieldError}
                                    onChange={val => handleChange(name, val)}
                                />
                            </div>
                        );
                    })}
                    <MaterialQuantityInput
                        options={materialField?.options ?? []}
                        value={formValues.materials ?? []}
                        onChange={items => handleChange('materials', items)}
                        stockMap={availability?.material_stock ?? {}}
                        label={materialField?.label ?? 'Materiais'}
                    />
                    <div className="flex gap-2 pt-1">
                        <button
                            type="submit"
                            disabled={saving}
                            className="flex-1 rounded-lg bg-brand-accent px-4 py-2 text-sm font-medium text-white hover:opacity-90 disabled:opacity-50 transition-opacity"
                        >
                            {saving ? '…' : t('pages.tasks.drawer.save_mini_task')}
                        </button>
                        <button
                            type="button"
                            onClick={handleClose}
                            className="rounded-lg border border-brand-mid/20 px-4 py-2 text-sm font-medium text-brand-mid hover:bg-brand-light transition-colors"
                        >
                            {t('pages.tasks.drawer.cancel')}
                        </button>
                    </div>
                </form>
            )}

            {/* List */}
            {miniTasks.length === 0 && !showForm ? (
                <p className="text-sm text-brand-mid text-center py-8">
                    {t('pages.tasks.drawer.no_mini_tasks')}
                </p>
            ) : miniTasks.length > 0 ? (
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
                                    <td className="py-2.5"><StatusBadge status={mt.status} /></td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>
            ) : null}
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

export default function TaskDrawer({ isOpen, onClose, item, loading, onCompleted, miniTaskCreateSchema }) {
    const { props: { auth, can } } = usePage();
    const authUser = auth?.user;
    const toast = useToast();

    const [completing, setCompleting] = useState(false);
    const [showRejectModal, setShowRejectModal] = useState(false);
    const [rejectReason, setRejectReason] = useState('');
    const [rejecting, setRejecting] = useState(false);

    const [soDrawerOpen, setSoDrawerOpen] = useState(false);
    const [soOrder, setSoOrder] = useState(null);
    const [soLoading, setSoLoading] = useState(false);

    const handleOpenServiceOrder = useCallback(async (serviceOrderId) => {
        setSoDrawerOpen(true);
        setSoLoading(true);
        setSoOrder(null);
        try {
            const res = await fetch(`/api/service-orders/${serviceOrderId}`, {
                headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest', ...csrfHeader() },
            });
            if (!res.ok) throw new Error();
            const body = await res.json();
            setSoOrder(body.data ?? body);
        } catch {
            toast.error(t('pages.tasks.drawer.reject_error'));
            setSoDrawerOpen(false);
        } finally {
            setSoLoading(false);
        }
    }, [toast]);

    const handleCloseServiceOrder = useCallback(() => {
        setSoDrawerOpen(false);
    }, []);

    const status = item?.status?.value ?? item?.status;
    const isManager = authUser?.id && item?.manager?.id && String(authUser.id) === String(item.manager.id);
    const canComplete = status === 'awaiting_approval' && (can?.completeTask || isManager);
    const canReject = status === 'awaiting_approval' && (can?.completeTask || isManager);

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
        { id: 'general',     label: t('pages.tasks.drawer.tab_general'),    component: <GeneralTab item={item} canViewServiceOrders={can?.viewServiceOrders} onOpenServiceOrder={handleOpenServiceOrder} /> },
        { id: 'mini_tasks',  label: t('pages.tasks.drawer.tab_mini_tasks'),  component: <MiniTasksTab miniTasks={item.mini_tasks} taskId={item.id} schema={miniTaskCreateSchema} onCreated={onCompleted} hasPeriod={!!(item.start_date && item.end_date)} /> },
        { id: 'rejections',  label: t('pages.tasks.drawer.tab_rejections'),  component: <RejectionsTab taskId={item.id} /> },
    ] : [];

    return (
        <>
            <WorkspaceDrawer
                isOpen={isOpen}
                onClose={onClose}
                title={item?.reference ?? ''}
                subtitle={loading ? t('pages.common.loading') : item ? <StatusBadge status={item.status} /> : undefined}
                tabs={tabs}
                headerActions={headerActions}
            />
            <ServiceOrderDrawer
                order={soOrder}
                isOpen={soDrawerOpen}
                loading={soLoading}
                onClose={handleCloseServiceOrder}
                stacked
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
