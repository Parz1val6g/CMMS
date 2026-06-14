import { useState, useCallback } from 'react';
import { router } from '@inertiajs/react';
import { ClipboardList, Ticket, ListChecks, Handshake } from 'lucide-react';
import KpiCard from '@/Components/Common/KpiCard';
import PeriodFilter from './PeriodFilter';
import RefreshIndicator from './RefreshIndicator';
import NeedsAttention from './NeedsAttention';
import InterventionMap from './InterventionMap';
import ServiceOrderDrawer from '@/Components/Shared/ServiceOrderDrawer';
import TaskDrawer from '@/Components/Shared/TaskDrawer';
import { csrfHeader } from '@/utils/csrf';

export default function ManagerDashboard({ kpis, attention, mapOrders, period, onPeriodChange, countdown, onRefresh }) {
  const [drawerOrder,   setDrawerOrder]   = useState(null);
  const [drawerTask,    setDrawerTask]    = useState(null);
  const [drawerLoading, setDrawerLoading] = useState(false);

  const handleAttentionClick = useCallback(async (item) => {
    setDrawerLoading(true);
    setDrawerOrder(null);
    setDrawerTask(null);

    const url = item.type === 'order'
      ? `/api/service-orders/${item.id}`
      : `/api/tasks/${item.id}`;

    try {
      const res  = await fetch(url, { credentials: 'include', headers: { Accept: 'application/json', ...csrfHeader() } });
      const json = await res.json();
      const data = json.data ?? json;
      if (item.type === 'order') setDrawerOrder(data);
      else                       setDrawerTask(data);
    } finally {
      setDrawerLoading(false);
    }
  }, []);

  const closeDrawer = useCallback(() => {
    setDrawerOrder(null);
    setDrawerTask(null);
    setDrawerLoading(false);
  }, []);

  const kpiCards = [
    { label: 'Ordens Activas',     value: kpis.active_orders?.value,   delta: kpis.active_orders?.delta,   deltaLabel: kpis.active_orders?.delta_label,   color: 'blue',   icon: ClipboardList },
    { label: 'Tickets Abertos',    value: kpis.open_tickets?.value,    delta: kpis.open_tickets?.delta,    deltaLabel: kpis.open_tickets?.delta_label,    color: 'yellow', icon: Ticket },
    { label: 'Tarefas em Atraso',  value: kpis.overdue_tasks?.value,   delta: null,                        deltaLabel: null,                              color: 'red',    icon: ListChecks, deltaInvert: true },
    { label: 'Aguarda Revisão',    value: kpis.awaiting_review?.value, delta: null,                        deltaLabel: null,                              color: 'indigo', icon: Handshake },
  ];

  return (
    <>
      <div className="flex flex-wrap items-center justify-between gap-3">
        <PeriodFilter period={period} onChange={onPeriodChange} />
        <RefreshIndicator countdown={countdown} onRefresh={onRefresh} />
      </div>

      <div className="grid grid-cols-2 gap-4 lg:grid-cols-4">
        {kpiCards.map((card) => (
          <KpiCard
            key={card.label}
            label={card.label}
            value={card.value}
            color={card.color}
            delta={card.delta}
            deltaLabel={card.deltaLabel}
            deltaInvert={card.deltaInvert}
          />
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
            <InterventionMap orders={mapOrders} />
          </div>
        </div>
      </div>

      <ServiceOrderDrawer
        isOpen={drawerLoading || !!drawerOrder}
        order={drawerOrder}
        loading={drawerLoading && !drawerOrder}
        onClose={closeDrawer}
        onCompleted={() => { closeDrawer(); onRefresh(); }}
      />
      <TaskDrawer
        isOpen={drawerLoading || !!drawerTask}
        item={drawerTask}
        loading={drawerLoading && !drawerTask}
        onClose={closeDrawer}
      />
    </>
  );
}
