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
    const cls = map[status?.value ?? status] ?? 'bg-slate-700 text-slate-300';
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
            <Field label="Referência">
                <span className="font-mono text-indigo-400">{item.reference}</span>
            </Field>
            <Field label="Estado">
                <StatusBadge status={item.status} />
            </Field>
            <Field label="Ordem de Serviço">
                {item.service_order?.process ?? null}
            </Field>
            <Field label="Gestor">
                {item.manager?.name ?? null}
            </Field>
            <Field label="Sector(es)">
                {sectors}
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

function MiniTasksTab({ miniTasks = [] }) {
    const statusLabels = {
        pending:     'Pendente',
        in_progress: 'Em Progresso',
        completed:   'Concluído',
        blocked:     'Bloqueado',
        cancelled:   'Cancelado',
    };

    if (!miniTasks.length) {
        return (
            <p className="text-sm text-slate-500 text-center py-12">
                Nenhuma mini-tarefa associada.
            </p>
        );
    }

    return (
        <div className="overflow-x-auto">
            <table className="w-full text-sm">
                <thead>
                    <tr className="border-b border-slate-700">
                        <th className="pb-2 pr-4 text-left text-xs font-medium uppercase tracking-wide text-slate-500">Referência</th>
                        <th className="pb-2 text-left text-xs font-medium uppercase tracking-wide text-slate-500">Estado</th>
                    </tr>
                </thead>
                <tbody className="divide-y divide-slate-700/50">
                    {miniTasks.map(mt => (
                        <tr key={mt.id} className="hover:bg-slate-700/20 transition-colors">
                            <td className="py-2.5 pr-4 font-mono text-indigo-400">{mt.reference}</td>
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

export default function TaskDrawer({ isOpen, onClose, item, loading }) {
    const tabs = item ? [
        { id: 'general',    label: 'Geral',         component: <GeneralTab item={item} /> },
        { id: 'mini_tasks', label: 'Mini-Tarefas',  component: <MiniTasksTab miniTasks={item.mini_tasks} /> },
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
