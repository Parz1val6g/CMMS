import AppLayout from '@/Layouts/AppLayout';
import InterventionMap from '@/Features/Dashboard/Components/InterventionMap';
import KpiCard from '@/Components/Common/KpiCard';
import SectionHeader from '@/Components/Common/SectionHeader';
import { AlertCircle } from 'lucide-react';
import { t } from '@/utils/i18n';

function CriticalOrderItem({ order }) {
  return (
    <li className="px-6 py-4 hover:bg-brand-light transition-colors">
      <div className="flex items-center justify-between gap-4">
        <div className="min-w-0 flex-1">
          <p className="truncate text-sm font-medium text-blue-600">
            {order.process}
          </p>
          <p className="mt-1 flex items-center gap-1 text-sm text-brand-mid">
            <svg className="h-4 w-4 shrink-0 text-brand-mid" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
              <path strokeLinecap="round" strokeLinejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
            {order.location?.parish?.name ?? t('pages.dashboard.unknown_location')}
          </p>
        </div>
        <div className="flex shrink-0 flex-col items-end gap-1">
          <span className="inline-flex rounded-full bg-red-100 px-2 text-xs font-semibold leading-5 text-red-800">
            {t('pages.dashboard.high_priority')}
          </span>
          <p className="text-sm text-brand-mid">
            {t('pages.dashboard.created_at')} {order.created_at}
          </p>
        </div>
      </div>
    </li>
  );
}

export default function Dashboard({ kpis, criticalOrders, mapOrders }) {
  return (
    <AppLayout title={t('pages.dashboard.title')}>
      <div className="h-full overflow-y-auto w-full">
        <div className="max-w-7xl mx-auto py-8 px-6 space-y-6">
        {/* KPI Cards */}
        <div className="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
          <KpiCard
            label={t('pages.dashboard.kpi_active_orders')}
            value={kpis.active_orders}
            color="blue"
          />
          <KpiCard
            label={t('pages.dashboard.kpi_pending_tasks')}
            value={kpis.pending_tasks}
            color="yellow"
          />
          <KpiCard
            label={t('pages.dashboard.kpi_field_teams')}
            value={kpis.active_mini_tasks}
            color="green"
          />
          <KpiCard
            label={t('pages.dashboard.kpi_hours_today')}
            value={kpis.today_work_hours}
            unit={t('pages.dashboard.hrs_unit')}
            color="indigo"
          />
        </div>

        {/* Bottom grid: Critical Orders + Intervention Map */}
        <div className="grid grid-cols-1 gap-6 lg:grid-cols-2">
          {/* Critical Orders */}
          <div className="overflow-hidden rounded-lg bg-brand-white shadow-sm">
            <SectionHeader title={t('pages.dashboard.critical_orders_title')} icon={AlertCircle} color="red" />
            {criticalOrders.length > 0 ? (
              <ul className="divide-y divide-brand-mid/20">
                {criticalOrders.map((order) => (
                  <CriticalOrderItem key={order.id} order={order} />
                ))}
              </ul>
            ) : (
              <p className="px-6 py-8 text-center text-sm text-brand-mid">
                {t('pages.dashboard.no_critical_orders')}
              </p>
            )}
          </div>

          {/* Intervention Map */}
          <div className="overflow-hidden rounded-lg bg-brand-white shadow-sm">
            <SectionHeader title={t('pages.dashboard.map_title')} />
            <div className="p-1" style={{ height: 420 }}>
              <InterventionMap orders={mapOrders} />
            </div>
          </div>
          </div>
        </div>
      </div>
    </AppLayout>
  );
}
