import { useState, useEffect } from 'react';
import WorkspaceDrawer from '@/Components/Drawer/WorkspaceDrawer';
import BaseField from '@/Components/Shared/Drawer/BaseField';
import DateDisplay from '@/Components/Common/DateDisplay';
import { t } from '@/utils/i18n';

function StatusBadge({ status }) {
    const map = {
        pending:     'bg-brand-mid/10 text-brand-mid ring-1 ring-inset ring-brand-mid/25',
        in_progress: 'bg-brand-accent/15 text-brand-accent',
        completed:   'bg-emerald-100 text-emerald-700',
        approved:    'bg-teal-100 text-teal-700',
        rejected:    'bg-red-100 text-red-700',
    };
    const labels = {
        pending:     t('pages.work_logs.drawer.status_pending'),
        in_progress: t('pages.work_logs.drawer.status_in_progress'),
        completed:   t('pages.work_logs.drawer.status_completed'),
        approved:    t('pages.work_logs.drawer.status_approved'),
        rejected:    t('pages.work_logs.drawer.status_rejected'),
    };
    const key = status?.value ?? status;
    return (
        <span className={`inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium ${map[key] ?? 'bg-brand-light text-brand-mid'}`}>
            {labels[key] ?? status?.label ?? status ?? '—'}
        </span>
    );
}

function SectionTitle({ children }) {
    return (
        <h3 className="mb-3 text-xs font-semibold uppercase tracking-wider text-brand-mid border-b border-brand-mid/20 pb-1.5">
            {children}
        </h3>
    );
}


function formatDuration(minutes) {
    if (minutes == null) return '—';
    const h = Math.floor(minutes / 60);
    const m = minutes % 60;
    if (h === 0) return `${m}min`;
    return m === 0 ? `${h}h` : `${h}h ${m}min`;
}

function ElapsedTimer({ startedAt }) {
    const [elapsed, setElapsed] = useState(null);

    useEffect(() => {
        const tick = () => {
            const diff = Math.max(0, Math.floor((Date.now() - new Date(startedAt).getTime()) / 1000));
            const h = Math.floor(diff / 3600);
            const m = Math.floor((diff % 3600) / 60);
            const s = diff % 60;
            setElapsed(
                h > 0
                    ? `${h}h ${String(m).padStart(2, '0')}min ${String(s).padStart(2, '0')}s`
                    : `${String(m).padStart(2, '0')}min ${String(s).padStart(2, '0')}s`
            );
        };
        tick();
        const id = setInterval(tick, 1000);
        return () => clearInterval(id);
    }, [startedAt]);

    return <span className="font-mono text-amber-400">{elapsed ?? '…'}</span>;
}

function GeneralTab({ item }) {
    return (
        <div className="grid grid-cols-2 gap-6">
            <BaseField label={t('pages.work_logs.drawer.field_mini_task')}>
                <span className="font-mono text-brand-accent">{item.mini_task?.reference ?? null}</span>
            </BaseField>
            <BaseField label={t('pages.work_logs.drawer.field_created_at')}>
                <DateDisplay value={item.created_at} />
            </BaseField>
            <div className="col-span-2">
                <BaseField label={t('pages.work_logs.drawer.field_description')}>
                    {item.description
                        ? <p className="whitespace-pre-wrap leading-relaxed">{item.description}</p>
                        : null}
                </BaseField>
            </div>
        </div>
    );
}

function ResourcesTab({ materials = [], equipment = [] }) {
    return (
        <div className="space-y-6">
            <div>
                <SectionTitle>{t('pages.work_logs.drawer.section_materials_used')}</SectionTitle>
                {materials.length === 0
                    ? <p className="text-sm text-brand-mid">{t('pages.work_logs.drawer.no_materials')}</p>
                    : (
                        <table className="w-full text-sm">
                            <thead>
                                <tr className="border-b border-brand-mid/20">
                                    <th className="pb-2 pr-4 text-left text-xs font-medium uppercase tracking-wide text-brand-mid">{t('pages.work_logs.drawer.th_material')}</th>
                                    <th className="pb-2 pr-4 text-right text-xs font-medium uppercase tracking-wide text-brand-mid">{t('pages.work_logs.drawer.th_qty_used')}</th>
                                    <th className="pb-2 text-right text-xs font-medium uppercase tracking-wide text-brand-mid">{t('pages.work_logs.drawer.th_unit_price')}</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-brand-mid/10">
                                {materials.map(m => (
                                    <tr key={m.id} className="hover:bg-brand-light transition-colors">
                                        <td className="py-2.5 pr-4 text-brand-darkest">{m.name}</td>
                                        <td className="py-2.5 pr-4 text-right text-brand-mid">{m.quantity_used}</td>
                                        <td className="py-2.5 text-right text-brand-mid">
                                            {m.unit_price_at_use != null ? `${Number(m.unit_price_at_use).toFixed(2)} €` : '—'}
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    )
                }
            </div>
            <div>
                <SectionTitle>{t('pages.work_logs.drawer.section_equipment_used')}</SectionTitle>
                {equipment.length === 0
                    ? <p className="text-sm text-brand-mid">{t('pages.work_logs.drawer.no_equipment')}</p>
                    : (
                        <table className="w-full text-sm">
                            <thead>
                                <tr className="border-b border-brand-mid/20">
                                    <th className="pb-2 pr-4 text-left text-xs font-medium uppercase tracking-wide text-brand-mid">{t('pages.work_logs.drawer.th_name')}</th>
                                    <th className="pb-2 pr-4 text-left text-xs font-medium uppercase tracking-wide text-brand-mid">{t('pages.work_logs.drawer.th_brand_model')}</th>
                                    <th className="pb-2 text-left text-xs font-medium uppercase tracking-wide text-brand-mid">{t('pages.work_logs.drawer.th_serial')}</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-brand-mid/10">
                                {equipment.map(eq => (
                                    <tr key={eq.id} className="hover:bg-brand-light transition-colors">
                                        <td className="py-2.5 pr-4 text-brand-darkest">{eq.name}</td>
                                        <td className="py-2.5 pr-4 text-brand-mid">
                                            {[eq.brand, eq.model].filter(Boolean).join(' / ') || '—'}
                                        </td>
                                        <td className="py-2.5 text-brand-mid font-mono text-xs">{eq.serial_number ?? '—'}</td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    )
                }
            </div>
        </div>
    );
}

function TimeTab({ startedAt, completedAt, durationMinutes }) {
    const isFinished = !!completedAt;

    return (
        <div className="space-y-6">
            <div className="grid grid-cols-2 gap-6">
                <BaseField label={t('pages.work_logs.drawer.field_start')}>
                    <DateDisplay value={startedAt} showTime />
                </BaseField>
                <BaseField label={t('pages.work_logs.drawer.field_end')}>
                    {isFinished ? <DateDisplay value={completedAt} showTime /> : <span className="text-amber-400 text-sm">{t('pages.work_logs.drawer.in_progress_label')}</span>}
                </BaseField>
            </div>

            <div className="rounded-lg bg-brand-light border border-brand-mid/20 p-6 flex flex-col items-center gap-2">
                <span className="text-xs font-medium uppercase tracking-wide text-brand-mid">
                    {isFinished ? t('pages.work_logs.drawer.total_duration') : t('pages.work_logs.drawer.elapsed_time')}
                </span>
                {isFinished
                    ? <span className="text-3xl font-mono font-bold text-emerald-400">{formatDuration(durationMinutes)}</span>
                    : startedAt
                        ? <span className="text-3xl font-bold"><ElapsedTimer startedAt={startedAt} /></span>
                        : <span className="text-brand-mid">—</span>
                }
            </div>
        </div>
    );
}

export default function WorkLogDrawer({ isOpen, onClose, item, loading }) {
    const tabs = item ? [
        { id: 'general',   label: t('pages.work_logs.drawer.tab_general'),      component: <GeneralTab item={item} /> },
        { id: 'resources', label: t('pages.work_logs.drawer.tab_resources'),   component: <ResourcesTab materials={item.materials ?? []} equipment={item.equipment ?? []} /> },
        { id: 'time',      label: t('pages.work_logs.drawer.tab_time'),      component: <TimeTab startedAt={item.started_at} completedAt={item.completed_at} durationMinutes={item.duration_minutes} /> },
    ] : [];

    return (
        <WorkspaceDrawer
            isOpen={isOpen}
            onClose={onClose}
            title={item?.reference ?? ''}
            subtitle={loading ? t('pages.common.loading') : item ? <StatusBadge status={item.status} /> : undefined}
            tabs={tabs}
        />
    );
}
