import { Users, ListChecks, CheckCircle } from 'lucide-react';
import KpiCard from '@/Components/Common/KpiCard';
import RefreshIndicator from './RefreshIndicator';

export default function TeamManagerDashboard({ kpis, teamWorkers = [], countdown, onRefresh }) {
  const kpiCards = [
    { label: 'Trabalhadores',        value: kpis.workers?.value,             color: 'teal',   icon: Users },
    { label: 'Mini-Tarefas Abertas', value: kpis.pending_mini_tasks?.value,  color: 'yellow', icon: ListChecks },
    { label: 'Concluídas Hoje',      value: kpis.completed_today?.value,     color: 'green',  icon: CheckCircle },
  ];

  return (
    <>
      <div className="flex justify-end">
        <RefreshIndicator countdown={countdown} onRefresh={onRefresh} />
      </div>

      <div className="grid grid-cols-1 gap-4 sm:grid-cols-3">
        {kpiCards.map((card) => (
          <KpiCard key={card.label} label={card.label} value={card.value} color={card.color} />
        ))}
      </div>

      <div className="rounded-xl border border-brand-mid/20 bg-brand-white shadow-sm">
        <div className="flex items-center gap-2 border-b border-brand-mid/20 px-4 py-3">
          <Users size={15} className="text-brand-mid" />
          <h2 className="text-sm font-semibold text-brand-darkest">Trabalhadores da Equipa</h2>
        </div>
        <div className="divide-y divide-brand-mid/10">
          {teamWorkers.length === 0 ? (
            <p className="px-4 py-8 text-center text-sm text-brand-mid">Nenhum trabalhador na equipa</p>
          ) : (
            teamWorkers.map((w) => (
              <div key={w.id} className="flex items-center gap-3 px-4 py-3">
                <div className="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-brand-light text-xs font-semibold text-brand-mid">
                  {w.name?.charAt(0)?.toUpperCase()}
                </div>
                <div className="flex-1 min-w-0">
                  <p className="text-sm font-semibold text-brand-darkest truncate">{w.name}</p>
                  {w.team && <p className="text-xs text-brand-mid truncate">{w.team}</p>}
                </div>
              </div>
            ))
          )}
        </div>
      </div>
    </>
  );
}
