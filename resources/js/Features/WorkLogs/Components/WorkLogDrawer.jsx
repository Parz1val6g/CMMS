import { useState, useEffect } from 'react';
import WorkspaceDrawer from '@/Components/Drawer/WorkspaceDrawer';

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
        pending:     'bg-slate-700 text-brand-mid',
        in_progress: 'bg-brand-accent/15 text-brand-accent',
        completed:   'bg-emerald-900/60 text-emerald-300',
        approved:    'bg-teal-900/60 text-teal-300',
        rejected:    'bg-red-900/60 text-red-300',
    };
    const labels = {
        pending:     'Pendente',
        in_progress: 'Em Progresso',
        completed:   'Concluído',
        approved:    'Aprovado',
        rejected:    'Rejeitado',
    };
    const key = status?.value ?? status;
    return (
        <span className={`inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium ${map[key] ?? 'bg-slate-700 text-brand-mid'}`}>
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

function formatDateTime(iso) {
    if (!iso) return '—';
    return new Date(iso).toLocaleString('pt-PT', {
        day: '2-digit', month: '2-digit', year: 'numeric',
        hour: '2-digit', minute: '2-digit',
    });
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
    const createdAt = item.created_at
        ? new Date(item.created_at).toLocaleDateString('pt-PT', { day: '2-digit', month: '2-digit', year: 'numeric' })
        : null;

    return (
        <div className="grid grid-cols-2 gap-6">
            <Field label="Referência">
                <span className="font-mono text-brand-accent">{item.reference}</span>
            </Field>
            <Field label="Estado">
                <StatusBadge status={item.status} />
            </Field>
            <Field label="Mini-Tarefa">
                <span className="font-mono text-brand-accent">{item.mini_task?.reference ?? null}</span>
            </Field>
            <Field label="Criado em">
                {createdAt}
            </Field>
            <div className="col-span-2">
                <Field label="Descrição">
                    {item.description
                        ? <p className="whitespace-pre-wrap leading-relaxed">{item.description}</p>
                        : null}
                </Field>
            </div>
        </div>
    );
}

function ResourcesTab({ materials = [], equipment = [] }) {
    return (
        <div className="space-y-6">
            <div>
                <SectionTitle>Materiais Utilizados</SectionTitle>
                {materials.length === 0
                    ? <p className="text-sm text-brand-mid">Nenhum material registado.</p>
                    : (
                        <table className="w-full text-sm">
                            <thead>
                                <tr className="border-b border-brand-mid/20">
                                    <th className="pb-2 pr-4 text-left text-xs font-medium uppercase tracking-wide text-brand-mid">Material</th>
                                    <th className="pb-2 pr-4 text-right text-xs font-medium uppercase tracking-wide text-brand-mid">Qtd. Usada</th>
                                    <th className="pb-2 text-right text-xs font-medium uppercase tracking-wide text-brand-mid">Preço Unit.</th>
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
                <SectionTitle>Equipamentos Utilizados</SectionTitle>
                {equipment.length === 0
                    ? <p className="text-sm text-brand-mid">Nenhum equipamento registado.</p>
                    : (
                        <table className="w-full text-sm">
                            <thead>
                                <tr className="border-b border-brand-mid/20">
                                    <th className="pb-2 pr-4 text-left text-xs font-medium uppercase tracking-wide text-brand-mid">Nome</th>
                                    <th className="pb-2 pr-4 text-left text-xs font-medium uppercase tracking-wide text-brand-mid">Marca / Modelo</th>
                                    <th className="pb-2 text-left text-xs font-medium uppercase tracking-wide text-brand-mid">Nº Série</th>
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
                <Field label="Início">
                    {formatDateTime(startedAt)}
                </Field>
                <Field label="Fim">
                    {isFinished ? formatDateTime(completedAt) : <span className="text-amber-400 text-sm">Em curso</span>}
                </Field>
            </div>

            <div className="rounded-lg bg-brand-light border border-brand-mid/20 p-6 flex flex-col items-center gap-2">
                <span className="text-xs font-medium uppercase tracking-wide text-brand-mid">
                    {isFinished ? 'Duração Total' : 'Tempo Decorrido'}
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
        { id: 'general',   label: 'Geral',      component: <GeneralTab item={item} /> },
        { id: 'resources', label: 'Recursos',   component: <ResourcesTab materials={item.materials ?? []} equipment={item.equipment ?? []} /> },
        { id: 'time',      label: 'Tempo',      component: <TimeTab startedAt={item.started_at} completedAt={item.completed_at} durationMinutes={item.duration_minutes} /> },
    ] : [];

    return (
        <WorkspaceDrawer
            isOpen={isOpen}
            onClose={onClose}
            title={item?.reference ?? ''}
            subtitle={loading ? 'A carregar...' : undefined}
            tabs={tabs}
        />
    );
}
