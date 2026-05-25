import { router } from '@inertiajs/react';
import { ListChecks, ScrollText, CheckCircle } from 'lucide-react';
import KpiCard from '@/Components/Common/KpiCard';
import RefreshIndicator from './RefreshIndicator';
import NeedsAttention from './NeedsAttention';

export default function WorkerDashboard({ kpis, attention, countdown, onRefresh }) {
  const kpiCards = [
    { label: 'Mini-Tarefas Abertas', value: kpis.pending_mini_tasks?.value, color: 'blue',   icon: ListChecks },
    { label: 'Work Logs em Aberto',  value: kpis.open_work_logs?.value,     color: 'yellow', icon: ScrollText },
    { label: 'Concluídas Hoje',      value: kpis.completed_today?.value,    color: 'green',  icon: CheckCircle },
  ];

  const handleAttentionClick = (item) => {
    router.visit(`/work-logs?view=${item.id}`);
  };

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

      <div style={{ height: 400 }}>
        <NeedsAttention items={attention} onItemClick={handleAttentionClick} />
      </div>
    </>
  );
}
