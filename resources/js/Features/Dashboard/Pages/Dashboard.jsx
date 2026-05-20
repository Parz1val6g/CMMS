import { useState, useEffect, useCallback, useRef } from 'react';
import { router } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';
import KpiCard from '@/Components/Common/KpiCard';
import InterventionMap from '@/Features/Dashboard/Components/InterventionMap';
import TaskDrawer from '@/Features/Tasks/Components/TaskDrawer';
import ServiceOrderDrawer from '@/Features/ServiceOrders/Components/ServiceOrderDrawer';
import { csrfHeader } from '@/utils/csrf';
import {
  AlertTriangle, Clock, RefreshCw, ClipboardList, Ticket,
  ListChecks, Handshake, ChevronRight,
} from 'lucide-react';

const REFRESH_INTERVAL = 60; // seconds

// ── Period filter ────────────────────────────────────────────────────────────

const PERIODS = [
  { key: 'today', label: 'Hoje' },
  { key: 'week',  label: 'Esta semana' },
  { key: 'month', label: 'Este mês' },
];

function PeriodFilter({ period, onChange }) {
  return (
    <div className="flex items-center gap-1 rounded-lg border border-brand-mid/20 bg-brand-white p-1 shadow-sm">
      {PERIODS.map((p) => (
        <button
          key={p.key}
          type="button"
          onClick={() => onChange(p.key)}
          className={[
            'rounded-md px-3 py-1.5 text-xs font-semibold transition-colors',
            period === p.key
              ? 'bg-brand-accent text-white shadow-sm'
              : 'text-brand-mid hover:text-brand-darkest hover:bg-brand-light',
          ].join(' ')}
        >
          {p.label}
        </button>
      ))}
    </div>
  );
}

// ── Refresh indicator ────────────────────────────────────────────────────────

function RefreshIndicator({ countdown, onRefresh }) {
  return (
    <button
      type="button"
      onClick={onRefresh}
      className="flex items-center gap-1.5 rounded-lg border border-brand-mid/20 bg-brand-white px-3 py-1.5 text-xs text-brand-mid shadow-sm hover:text-brand-darkest transition-colors"
    >
      <RefreshCw size={12} className={countdown <= 5 ? 'animate-spin' : ''} />
      ↻ {countdown}s
    </button>
  );
}

// ── Needs Attention ──────────────────────────────────────────────────────────

const REASON_LABEL = {
  high_priority: 'Alta Prioridade',
  stale_order:   'Sem progresso',
  stale_task:    'Sem progresso',
};

const REASON_COLOR = {
  high_priority: 'bg-red-50 text-red-700 ring-1 ring-inset ring-red-200',
  stale_order:   'bg-orange-50 text-orange-700 ring-1 ring-inset ring-orange-200',
  stale_task:    'bg-yellow-50 text-yellow-700 ring-1 ring-inset ring-yellow-200',
};

const TYPE_ICON = { order: ClipboardList, task: ListChecks };

function AttentionRow({ item, onClick }) {
  const Icon = TYPE_ICON[item.type] ?? ClipboardList;
  return (
    <button
      type="button"
      onClick={() => onClick(item)}
      className="group flex w-full items-center gap-3 rounded-lg px-3 py-2.5 text-left transition-colors hover:bg-brand-light"
    >
      <Icon size={15} className="shrink-0 text-brand-mid" />
      <div className="min-w-0 flex-1">
        <span className="block truncate text-sm font-semibold text-brand-darkest font-mono">
          {item.reference}
        </span>
        {item.location && (
          <span className="block truncate text-xs text-brand-mid">{item.location}</span>
        )}
      </div>
      <div className="flex shrink-0 items-center gap-2">
        <span className={`rounded-full px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide ${REASON_COLOR[item.reason]}`}>
          {REASON_LABEL[item.reason]}
        </span>
        <span className="text-xs text-brand-mid tabular-nums">{item.days_open}d</span>
        <ChevronRight size={13} className="text-brand-mid/40 group-hover:text-brand-mid transition-colors" />
      </div>
    </button>
  );
}

function NeedsAttention({ items, onItemClick }) {
  return (
    <div className="flex h-full flex-col overflow-hidden rounded-xl border border-brand-mid/20 bg-brand-white shadow-sm">
      <div className="flex items-center gap-2 border-b border-brand-mid/20 px-4 py-3">
        <AlertTriangle size={15} className="text-orange-500" />
        <h2 className="text-sm font-semibold text-brand-darkest">Requer Atenção</h2>
        {items.length > 0 && (
          <span className="ml-auto rounded-full bg-orange-100 px-2 py-0.5 text-xs font-semibold text-orange-700">
            {items.length}
          </span>
        )}
      </div>
      <div className="flex-1 overflow-y-auto p-2">
        {items.length === 0 ? (
          <div className="flex flex-col items-center justify-center py-10 text-brand-mid">
            <Clock size={28} className="mb-2 opacity-30" />
            <p className="text-sm">Nenhum item crítico</p>
          </div>
        ) : (
          <div className="space-y-0.5">
            {items.map((item) => (
              <AttentionRow key={`${item.type}-${item.id}`} item={item} onClick={onItemClick} />
            ))}
          </div>
        )}
      </div>
    </div>
  );
}

// ── Main Dashboard ───────────────────────────────────────────────────────────

export default function Dashboard({ kpis, attention, mapOrders, period: initialPeriod }) {
  const [period, setPeriod]     = useState(initialPeriod ?? 'week');
  const [countdown, setCountdown] = useState(REFRESH_INTERVAL);

  // Drawer state
  const [drawerOrder,   setDrawerOrder]   = useState(null);
  const [drawerTask,    setDrawerTask]    = useState(null);
  const [drawerLoading, setDrawerLoading] = useState(false);

  // ── Period change — full Inertia reload with new period param ──────────
  const handlePeriodChange = useCallback((p) => {
    setPeriod(p);
    router.reload({ data: { period: p }, preserveScroll: true });
  }, []);

  // ── Auto-refresh — reloads Inertia page data every 60s ────────────────
  const refresh = useCallback(() => {
    router.reload({ preserveScroll: true });
    setCountdown(REFRESH_INTERVAL);
  }, []);

  useEffect(() => {
    const tick = setInterval(() => {
      setCountdown((c) => {
        if (c <= 1) { refresh(); return REFRESH_INTERVAL; }
        return c - 1;
      });
    }, 1000);
    return () => clearInterval(tick);
  }, [refresh]);

  // ── Needs Attention click — open correct drawer ────────────────────────
  const handleAttentionClick = useCallback(async (item) => {
    setDrawerLoading(true);
    setDrawerOrder(null);
    setDrawerTask(null);

    const url = item.type === 'order'
      ? `/api/service-orders/${item.id}`
      : `/api/tasks/${item.id}`;

    try {
      const res  = await fetch(url, { headers: { Accept: 'application/json', ...csrfHeader() } });
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

  // ── KPI config ─────────────────────────────────────────────────────────
  const kpiCards = [
    {
      label:       'Ordens Activas',
      value:       kpis.active_orders.value,
      delta:       kpis.active_orders.delta,
      deltaLabel:  kpis.active_orders.delta_label,
      color:       'blue',
      icon:        ClipboardList,
    },
    {
      label:       'Tickets Abertos',
      value:       kpis.open_tickets.value,
      delta:       kpis.open_tickets.delta,
      deltaLabel:  kpis.open_tickets.delta_label,
      color:       'yellow',
      icon:        Ticket,
    },
    {
      label:       'Tarefas em Atraso',
      value:       kpis.overdue_tasks.value,
      delta:       kpis.overdue_tasks.delta,
      deltaLabel:  kpis.overdue_tasks.delta_label,
      color:       'red',
      deltaInvert: true,
      icon:        ListChecks,
    },
    {
      label:       'Aprovações Pendentes',
      value:       kpis.pending_approvals.value,
      delta:       kpis.pending_approvals.delta,
      deltaLabel:  kpis.pending_approvals.delta_label,
      color:       'indigo',
      icon:        Handshake,
    },
  ];

  return (
    <AppLayout title="Dashboard">
      <div className="flex h-full flex-col overflow-hidden">
        <div className="flex-1 overflow-y-auto">
          <div className="mx-auto max-w-7xl space-y-5 px-6 py-6">

            {/* ── Header bar ──────────────────────────────────────────── */}
            <div className="flex flex-wrap items-center justify-between gap-3">
              <PeriodFilter period={period} onChange={handlePeriodChange} />
              <RefreshIndicator countdown={countdown} onRefresh={refresh} />
            </div>

            {/* ── KPI row ─────────────────────────────────────────────── */}
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

            {/* ── Bottom grid: Needs Attention (40%) + Map (60%) ──────── */}
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

          </div>
        </div>
      </div>

      {/* ── Drawers ─────────────────────────────────────────────────────── */}
      <ServiceOrderDrawer
        isOpen={drawerLoading || !!drawerOrder}
        order={drawerOrder}
        loading={drawerLoading && !drawerOrder}
        onClose={closeDrawer}
        onCompleted={() => { closeDrawer(); refresh(); }}
      />
      <TaskDrawer
        isOpen={drawerLoading || !!drawerTask}
        item={drawerTask}
        loading={drawerLoading && !drawerTask}
        onClose={closeDrawer}
      />
    </AppLayout>
  );
}
