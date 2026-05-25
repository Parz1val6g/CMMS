import { ClipboardList, Ticket, Plus } from 'lucide-react';
import KpiCard from '@/Components/Common/KpiCard';
import PeriodFilter from './PeriodFilter';
import RefreshIndicator from './RefreshIndicator';

const STATUS_LABEL = {
  pending:          'Pendente',
  in_progress:      'Em Progresso',
  awaiting_approval:'Aguarda Revisão',
  completed:        'Concluída',
  cancelled:        'Cancelada',
};

const STATUS_COLOR = {
  pending:          'bg-yellow-50 text-yellow-700',
  in_progress:      'bg-blue-50 text-blue-700',
  awaiting_approval:'bg-indigo-50 text-indigo-700',
  completed:        'bg-green-50 text-green-700',
  cancelled:        'bg-gray-50 text-gray-500',
};

const PRIORITY_COLOR = {
  low:      'bg-gray-100 text-gray-500',
  medium:   'bg-yellow-50 text-yellow-700',
  high:     'bg-orange-50 text-orange-700',
  urgent:   'bg-red-50 text-red-700',
};

export default function AttendantDashboard({ kpis, recentOrders = [], period, onPeriodChange, countdown, onRefresh }) {
  const kpiCards = [
    { label: 'SOs Pendentes',    value: kpis.pending_orders?.value, color: 'yellow', icon: ClipboardList },
    { label: 'Tickets Abertos',  value: kpis.open_tickets?.value,   delta: kpis.open_tickets?.delta, deltaLabel: kpis.open_tickets?.delta_label, color: 'indigo', icon: Ticket },
    { label: 'Novas SOs',        value: kpis.new_orders?.value,     delta: kpis.new_orders?.delta,   deltaLabel: kpis.new_orders?.delta_label,   color: 'blue',   icon: Plus },
  ];

  return (
    <>
      <div className="flex flex-wrap items-center justify-between gap-3">
        <PeriodFilter period={period} onChange={onPeriodChange} />
        <RefreshIndicator countdown={countdown} onRefresh={onRefresh} />
      </div>

      <div className="grid grid-cols-1 gap-4 sm:grid-cols-3">
        {kpiCards.map((card) => (
          <KpiCard
            key={card.label}
            label={card.label}
            value={card.value}
            color={card.color}
            delta={card.delta}
            deltaLabel={card.deltaLabel}
          />
        ))}
      </div>

      <div className="rounded-xl border border-brand-mid/20 bg-brand-white shadow-sm">
        <div className="flex items-center gap-2 border-b border-brand-mid/20 px-4 py-3">
          <ClipboardList size={15} className="text-brand-mid" />
          <h2 className="text-sm font-semibold text-brand-darkest">Ordens de Serviço Recentes</h2>
        </div>
        <div className="divide-y divide-brand-mid/10">
          {recentOrders.length === 0 ? (
            <p className="px-4 py-8 text-center text-sm text-brand-mid">Nenhuma ordem de serviço</p>
          ) : (
            recentOrders.map((o) => (
              <div key={o.id} className="flex items-center gap-3 px-4 py-3">
                <span className="font-mono text-sm font-semibold text-brand-darkest w-28 shrink-0">{o.process}</span>
                <span className="flex-1 truncate text-sm text-brand-mid">{o.description}</span>
                {o.service_type && (
                  <span className="text-xs text-brand-mid shrink-0">{o.service_type}</span>
                )}
                <span className={`rounded-full px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide shrink-0 ${STATUS_COLOR[o.status] ?? 'bg-gray-50 text-gray-500'}`}>
                  {STATUS_LABEL[o.status] ?? o.status}
                </span>
                <span className={`rounded-full px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide shrink-0 ${PRIORITY_COLOR[o.priority] ?? ''}`}>
                  {o.priority}
                </span>
                <span className="text-xs text-brand-mid shrink-0 tabular-nums">{o.created_at}</span>
              </div>
            ))
          )}
        </div>
      </div>
    </>
  );
}
