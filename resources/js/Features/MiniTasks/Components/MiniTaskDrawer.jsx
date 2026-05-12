import WorkspaceDrawer from '@/Components/Drawer/WorkspaceDrawer';

function Field({ label, children }) {
    return (
        <div className="flex flex-col gap-1">
            <span className="text-xs font-medium uppercase tracking-wide text-slate-500">{label}</span>
            <span className="text-sm text-slate-200">{children ?? <span className="text-slate-600">—</span>}</span>
        </div>
    );
}

function StatusBadge({ status }) {
    const map = {
        pending:     'bg-slate-700 text-slate-300',
        in_progress: 'bg-indigo-900/60 text-indigo-300',
        completed:   'bg-emerald-900/60 text-emerald-300',
        blocked:     'bg-red-900/60 text-red-300',
        cancelled:   'bg-zinc-800 text-zinc-400',
    };
    const labels = {
        pending:     'Pendente',
        in_progress: 'Em Progresso',
        completed:   'Concluído',
        blocked:     'Bloqueado',
        cancelled:   'Cancelado',
    };
    const key = status?.value ?? status;
    return (
        <span className={`inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium ${map[key] ?? 'bg-slate-700 text-slate-300'}`}>
            {labels[key] ?? status?.label ?? status ?? '—'}
        </span>
    );
}

function SectionTitle({ children }) {
    return (
        <h3 className="mb-3 text-xs font-semibold uppercase tracking-wider text-slate-400 border-b border-slate-700 pb-1.5">
            {children}
        </h3>
    );
}

function GeneralTab({ item }) {
    const createdAt = item.created_at
        ? new Date(item.created_at).toLocaleDateString('pt-PT', { day: '2-digit', month: '2-digit', year: 'numeric' })
        : null;

    return (
        <div className="grid grid-cols-2 gap-6">
            <Field label="Referência">
                <span className="font-mono text-indigo-400">{item.reference}</span>
            </Field>
            <Field label="Estado">
                <StatusBadge status={item.status} />
            </Field>
            <Field label="Tarefa">
                <span className="font-mono text-indigo-400">{item.task?.reference ?? null}</span>
            </Field>
            <Field label="Supervisor">
                {item.supervisor?.name ?? null}
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

function TeamTab({ workers = [], teams = [] }) {
    return (
        <div className="space-y-6">
            <div>
                <SectionTitle>Trabalhadores</SectionTitle>
                {workers.length === 0
                    ? <p className="text-sm text-slate-500">Nenhum trabalhador atribuído.</p>
                    : (
                        <ul className="space-y-1.5">
                            {workers.map(w => (
                                <li key={w.id} className="text-sm text-slate-200 flex items-center gap-2">
                                    <span className="inline-flex h-7 w-7 items-center justify-center rounded-full bg-indigo-900/60 text-xs font-medium text-indigo-300">
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
                <SectionTitle>Equipas</SectionTitle>
                {teams.length === 0
                    ? <p className="text-sm text-slate-500">Nenhuma equipa atribuída.</p>
                    : (
                        <ul className="space-y-1.5">
                            {teams.map(t => (
                                <li key={t.id} className="text-sm text-slate-200">{t.name}</li>
                            ))}
                        </ul>
                    )
                }
            </div>
        </div>
    );
}

function MaterialsTab({ materials = [] }) {
    return (
        <div className="space-y-6">
            <div>
                <SectionTitle>Materiais Planeados</SectionTitle>
                {materials.length === 0
                    ? <p className="text-sm text-slate-500">Nenhum material planeado.</p>
                    : (
                        <table className="w-full text-sm">
                            <thead>
                                <tr className="border-b border-slate-700">
                                    <th className="pb-2 pr-4 text-left text-xs font-medium uppercase tracking-wide text-slate-500">Material</th>
                                    <th className="pb-2 pr-4 text-right text-xs font-medium uppercase tracking-wide text-slate-500">Qtd. Planeada</th>
                                    <th className="pb-2 text-left text-xs font-medium uppercase tracking-wide text-slate-500">Un.</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-slate-700/50">
                                {materials.map(m => (
                                    <tr key={m.id} className="hover:bg-slate-700/20 transition-colors">
                                        <td className="py-2.5 pr-4 text-slate-200">{m.name}</td>
                                        <td className="py-2.5 pr-4 text-right text-slate-300">{m.planned_quantity}</td>
                                        <td className="py-2.5 text-slate-400">{m.unit}</td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    )
                }
            </div>
            <div>
                <SectionTitle>Equipamentos Planeados</SectionTitle>
                <p className="text-sm text-slate-500 italic">
                    Planeamento de equipamentos por mini-tarefa ainda não disponível.
                </p>
            </div>
        </div>
    );
}

export default function MiniTaskDrawer({ isOpen, onClose, item, loading }) {
    const tabs = item ? [
        { id: 'general',   label: 'Geral',      component: <GeneralTab item={item} /> },
        { id: 'team',      label: 'Equipa',     component: <TeamTab workers={item.workers} teams={item.teams} /> },
        { id: 'materials', label: 'Materiais',  component: <MaterialsTab materials={item.materials} /> },
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
