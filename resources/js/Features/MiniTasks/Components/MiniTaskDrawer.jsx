import WorkspaceDrawer from '@/Components/Drawer/WorkspaceDrawer';
import BaseField from '@/Components/Shared/Drawer/BaseField';
import { t } from '@/utils/i18n';

function StatusBadge({ status }) {
    const map = {
        pending:     'bg-brand-light text-brand-mid',
        in_progress: 'bg-brand-accent/15 text-brand-accent',
        completed:   'bg-emerald-900/60 text-emerald-300',
        blocked:     'bg-red-900/60 text-red-300',
        cancelled:   'bg-brand-mid/10 text-brand-mid',
    };
    const labels = {
        pending:     t('pages.mini_tasks.drawer.status_pending'),
        in_progress: t('pages.mini_tasks.drawer.status_in_progress'),
        completed:   t('pages.mini_tasks.drawer.status_completed'),
        blocked:     t('pages.mini_tasks.drawer.status_blocked'),
        cancelled:   t('pages.mini_tasks.drawer.status_cancelled'),
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

function formatDate(dateStr) {
    if (!dateStr) return null;
    return new Date(dateStr + 'T00:00:00').toLocaleDateString('pt-PT', { day: '2-digit', month: '2-digit', year: 'numeric' });
}

function GeneralTab({ item }) {
    const createdAt = item.created_at
        ? new Date(item.created_at).toLocaleDateString('pt-PT', { day: '2-digit', month: '2-digit', year: 'numeric' })
        : null;

    return (
        <div className="grid grid-cols-2 gap-6">
            <BaseField label={t('pages.mini_tasks.drawer.field_reference')}>
                <span className="font-mono text-indigo-400">{item.reference}</span>
            </BaseField>
            <BaseField label={t('pages.mini_tasks.drawer.field_status')}>
                <StatusBadge status={item.status} />
            </BaseField>
            <BaseField label={t('pages.mini_tasks.drawer.field_task')}>
                <span className="font-mono text-indigo-400">{item.task?.reference ?? null}</span>
            </BaseField>
            <BaseField label={t('pages.mini_tasks.drawer.field_supervisor')}>
                {item.supervisor?.name ?? null}
            </BaseField>
            <BaseField label={t('pages.mini_tasks.drawer.field_start_date')}>
                {formatDate(item.start_date)}
            </BaseField>
            <BaseField label={t('pages.mini_tasks.drawer.field_end_date')}>
                {formatDate(item.end_date)}
            </BaseField>
            <BaseField label={t('pages.mini_tasks.drawer.field_created_at')}>
                {createdAt}
            </BaseField>
            <div className="col-span-2">
                <BaseField label={t('pages.mini_tasks.drawer.field_description')}>
                    {item.description
                        ? <p className="whitespace-pre-wrap leading-relaxed">{item.description}</p>
                        : null}
                </BaseField>
            </div>
        </div>
    );
}

function TeamTab({ workers = [], teams = [] }) {
    return (
        <div className="space-y-6">
            <div>
                <SectionTitle>{t('pages.mini_tasks.drawer.section_workers')}</SectionTitle>
                {workers.length === 0
                    ? <p className="text-sm text-brand-mid">{t('pages.mini_tasks.drawer.no_workers')}</p>
                    : (
                        <ul className="space-y-1.5">
                            {workers.map(w => (
                                <li key={w.id} className="text-sm text-brand-darkest flex items-center gap-2">
                                    <span className="inline-flex h-7 w-7 items-center justify-center rounded-full bg-brand-accent/15 text-xs font-medium text-brand-accent">
                                        {w.name?.charAt(0) ?? '?'}
                                    </span>
                                    {w.name}
                                </li>
                            ))}
                        </ul>
                    )
                }
            </div>
            <div>
                <SectionTitle>{t('pages.mini_tasks.drawer.section_teams')}</SectionTitle>
                {teams.length === 0
                    ? <p className="text-sm text-brand-mid">{t('pages.mini_tasks.drawer.no_teams')}</p>
                    : (
                        <ul className="space-y-1.5">
                            {teams.map(t => (
                                <li key={t.id} className="text-sm text-brand-darkest">{t.name}</li>
                            ))}
                        </ul>
                    )
                }
            </div>
        </div>
    );
}

function MaterialsTab({ materials = [], equipment = [] }) {
    return (
        <div className="space-y-6">
            <div>
                <SectionTitle>{t('pages.mini_tasks.drawer.section_materials_planned')}</SectionTitle>
                {materials.length === 0
                    ? <p className="text-sm text-brand-mid">{t('pages.mini_tasks.drawer.no_materials')}</p>
                    : (
                        <table className="w-full text-sm">
                            <thead>
                                <tr className="border-b border-brand-mid/20">
                                    <th className="pb-2 pr-4 text-left text-xs font-medium uppercase tracking-wide text-brand-mid">{t('pages.mini_tasks.drawer.th_material')}</th>
                                    <th className="pb-2 pr-4 text-right text-xs font-medium uppercase tracking-wide text-brand-mid">{t('pages.mini_tasks.drawer.th_planned_qty')}</th>
                                    <th className="pb-2 text-left text-xs font-medium uppercase tracking-wide text-brand-mid">{t('pages.mini_tasks.drawer.th_unit')}</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-brand-mid/10">
                                {materials.map(m => (
                                    <tr key={m.id} className="hover:bg-brand-light transition-colors">
                                        <td className="py-2.5 pr-4 text-brand-darkest">{m.name}</td>
                                        <td className="py-2.5 pr-4 text-right text-brand-mid">{m.planned_quantity}</td>
                                        <td className="py-2.5 text-brand-mid">{m.unit}</td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    )
                }
            </div>
            <div>
                <SectionTitle>{t('pages.mini_tasks.drawer.section_equipment_planned')}</SectionTitle>
                {equipment.length === 0
                    ? <p className="text-sm text-brand-mid">{t('pages.mini_tasks.drawer.no_equipment')}</p>
                    : (
                        <ul className="space-y-1.5">
                            {equipment.map(e => (
                                <li key={e.id} className="text-sm text-brand-darkest flex items-center gap-2">
                                    <span className="inline-flex h-7 w-7 items-center justify-center rounded-full bg-brand-mid/20 text-xs font-medium text-brand-mid">
                                        E
                                    </span>
                                    {e.name}
                                </li>
                            ))}
                        </ul>
                    )
                }
            </div>
        </div>
    );
}

export default function MiniTaskDrawer({ isOpen, onClose, item, loading }) {
    const tabs = item ? [
        { id: 'general',   label: t('pages.mini_tasks.drawer.tab_general'),      component: <GeneralTab item={item} /> },
        { id: 'team',      label: t('pages.mini_tasks.drawer.tab_team'),     component: <TeamTab workers={item.workers} teams={item.teams} /> },
        { id: 'materials', label: t('pages.mini_tasks.drawer.tab_materials'),  component: <MaterialsTab materials={item.materials} equipment={item.equipment} /> },
    ] : [];

    return (
        <WorkspaceDrawer
            isOpen={isOpen}
            onClose={onClose}
            title={item?.reference ?? ''}
            subtitle={loading ? t('pages.common.loading') : undefined}
            tabs={tabs}
        />
    );
}
