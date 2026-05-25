import { router } from '@inertiajs/react';
import { ListChecks, Users, UserCheck } from 'lucide-react';
import KpiCard from '@/Components/Common/KpiCard';
import RefreshIndicator from './RefreshIndicator';
import NeedsAttention from './NeedsAttention';
import InterventionMap from './InterventionMap';

export default function SectorManagerDashboard({ kpis, attention, mapOrders, countdown, onRefresh }) {
  const kpiCards = [
    { label: 'Tarefas Ativas',   value: kpis.active_tasks?.value, color: 'blue',   icon: ListChecks },
    { label: 'Equipas',          value: kpis.teams?.value,        color: 'teal',   icon: Users },
    { label: 'Trabalhadores',    value: kpis.workers?.value,      color: 'indigo', icon: UserCheck },
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

      <div className="grid grid-cols-1 gap-4 lg:grid-cols-5" style={{ height: 440 }}>
        <div className="lg:col-span-2">
          <NeedsAttention items={attention} onItemClick={handleAttentionClick} />
        </div>
        <div className="lg:col-span-3 overflow-hidden rounded-xl border border-brand-mid/20 bg-brand-white shadow-sm">
          <div className="flex items-center gap-2 border-b border-brand-mid/20 px-4 py-3">
            <h2 className="text-sm font-semibold text-brand-darkest">Mapa de Intervenções</h2>
          </div>
          <div style={{ height: 392 }}>
            <InterventionMap orders={mapOrders ?? []} />
          </div>
        </div>
      </div>
    </>
  );
}
