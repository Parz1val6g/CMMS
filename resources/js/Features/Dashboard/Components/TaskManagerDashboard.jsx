import { router } from '@inertiajs/react';
import { ListChecks, Clock, CheckCircle } from 'lucide-react';
import KpiCard from '@/Components/Common/KpiCard';
import RefreshIndicator from './RefreshIndicator';
import NeedsAttention from './NeedsAttention';

export default function TaskManagerDashboard({ kpis, attention, countdown, onRefresh }) {
  const kpiCards = [
    { label: 'Tarefas Ativas',      value: kpis.active_tasks?.value,      color: 'blue',   icon: ListChecks },
    { label: 'Aguarda Aprovação',   value: kpis.awaiting_approval?.value,  color: 'indigo', icon: Clock },
    { label: 'Mini-Tarefas Abertas',value: kpis.pending_mini_tasks?.value, color: 'yellow', icon: CheckCircle },
  ];

  const handleAttentionClick = (item) => {
    router.visit(`/tasks?view=${item.id}`);
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
