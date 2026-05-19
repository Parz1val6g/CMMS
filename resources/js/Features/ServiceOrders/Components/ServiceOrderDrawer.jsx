import { Link } from '@inertiajs/react';
import WorkspaceDrawer from '@/Components/Drawer/WorkspaceDrawer';
import { ExternalLink } from 'lucide-react';

const PRIORITY_BADGE = {
  urgent: 'bg-red-100 text-red-800 ring-1 ring-inset ring-red-300/60',
  high:   'bg-orange-100 text-orange-800 ring-1 ring-inset ring-orange-300/60',
  normal: 'bg-gray-100 text-gray-700 ring-1 ring-inset ring-gray-300/60',
  low:    'bg-blue-50 text-blue-700 ring-1 ring-inset ring-blue-300/60',
};

const PRIORITY_LABEL = { urgent: 'Urgente', high: 'Alta', normal: 'Normal', low: 'Baixa' };

const STATUS_BADGE = {
  pending:     'bg-yellow-50 text-yellow-800 ring-1 ring-inset ring-yellow-300/60',
  in_progress: 'bg-blue-50 text-blue-800 ring-1 ring-inset ring-blue-300/60',
  completed:   'bg-green-50 text-green-800 ring-1 ring-inset ring-green-300/60',
  cancelled:   'bg-red-50 text-red-800 ring-1 ring-inset ring-red-300/60',
};

const STATUS_LABEL = {
  pending: 'Pendente', in_progress: 'Em Progresso',
  completed: 'Concluído', cancelled: 'Cancelado',
};

function Field({ label, children }) {
  return (
    <div className="flex flex-col gap-0.5">
      <span className="text-xs font-medium uppercase tracking-wider text-gray-400">{label}</span>
      <span className="text-sm text-gray-800 font-medium">{children ?? <span className="text-gray-400 italic">—</span>}</span>
    </div>
  );
}

function Badge({ map, labelMap, value }) {
  const cls = map[value] ?? 'bg-gray-100 text-gray-600';
  return (
    <span className={`inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium ${cls}`}>
      {labelMap[value] ?? value ?? '—'}
    </span>
  );
}

function DetailTab({ order }) {
  const date = order?.created_at
    ? new Date(order.created_at).toLocaleDateString('pt-PT', { day: '2-digit', month: '2-digit', year: 'numeric' })
    : null;

  return (
    <div className="space-y-5">
      <div className="grid grid-cols-2 gap-x-6 gap-y-5">
        <Field label="Estado">
          <Badge map={STATUS_BADGE} labelMap={STATUS_LABEL} value={order?.status?.value ?? order?.status} />
        </Field>
        <Field label="Prioridade">
          <Badge map={PRIORITY_BADGE} labelMap={PRIORITY_LABEL} value={order?.priority?.value ?? order?.priority} />
        </Field>
        <Field label="Gestor">{order?.manager?.name}</Field>
        <Field label="Criado em">{date}</Field>
        <Field label="Tipo de Serviço">{order?.service_type?.name}</Field>
        <Field label="Localização">{order?.location?.parish?.name}</Field>
      </div>

      {order?.description && (
        <div>
          <span className="text-xs font-medium uppercase tracking-wider text-gray-400">Descrição</span>
          <p className="mt-1 text-sm text-gray-700 leading-relaxed whitespace-pre-wrap">{order.description}</p>
        </div>
      )}

      <div className="pt-2 border-t border-gray-100">
        <Link
          href={`/service-orders`}
          className="inline-flex items-center gap-1.5 text-sm font-medium text-brand-accent hover:underline"
        >
          <ExternalLink size={14} />
          Ver em Ordens de Serviço
        </Link>
      </div>
    </div>
  );
}

export default function ServiceOrderDrawer({ order, isOpen, onClose, loading }) {
  const tabs = loading || !order
    ? [{ id: 'loading', label: '...', component: <div className="py-12 text-center text-sm text-gray-400">A carregar...</div> }]
    : [{ id: 'details', label: 'Detalhes', component: <DetailTab order={order} /> }];

  return (
    <WorkspaceDrawer
      isOpen={isOpen}
      onClose={onClose}
      title={order?.process ?? ''}
      subtitle={order ? (STATUS_LABEL[order.status?.value ?? order.status] ?? '') : ''}
      tabs={tabs}
    />
  );
}
