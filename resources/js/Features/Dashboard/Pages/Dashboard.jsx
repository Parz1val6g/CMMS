import AppLayout from '@/Layouts/AppLayout';
import InterventionMap from '@/Features/Dashboard/Components/InterventionMap';

function KpiCard({ label, value, unit, color }) {
  const borderColor = {
    blue: 'border-blue-500',
    yellow: 'border-yellow-500',
    green: 'border-green-500',
    indigo: 'border-indigo-500',
  }[color] ?? 'border-blue-500';

  return (
    <div className={`rounded-lg border-l-4 ${borderColor} bg-white p-6 shadow-sm dark:bg-gray-800`}>
      <h3 className="text-sm font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
        {label}
      </h3>
      <div className="mt-2 flex items-baseline gap-1">
        <span className="text-3xl font-extrabold text-gray-900 dark:text-white">
          {value}
        </span>
        {unit && (
          <span className="text-sm text-gray-500 dark:text-gray-400">{unit}</span>
        )}
      </div>
    </div>
  );
}

function CriticalOrderItem({ order }) {
  return (
    <li className="px-6 py-4 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
      <div className="flex items-center justify-between gap-4">
        <div className="min-w-0 flex-1">
          <p className="truncate text-sm font-medium text-blue-600 dark:text-blue-400">
            {order.process}
          </p>
          <p className="mt-1 flex items-center gap-1 text-sm text-gray-500 dark:text-gray-400">
            <svg className="h-4 w-4 shrink-0 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
              <path strokeLinecap="round" strokeLinejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
            {order.location?.parish?.name ?? 'Local desconhecido'}
          </p>
        </div>
        <div className="flex shrink-0 flex-col items-end gap-1">
          <span className="inline-flex rounded-full bg-red-100 px-2 text-xs font-semibold leading-5 text-red-800 dark:bg-red-900/50 dark:text-red-300">
            Alta Prioridade
          </span>
          <p className="text-sm text-gray-500 dark:text-gray-400">
            Criado: {order.created_at}
          </p>
        </div>
      </div>
    </li>
  );
}

export default function Dashboard({ kpis, criticalOrders, mapOrders, googleMapsApiKey }) {
  const breadcrumbs = [
    { name: 'Dashboard', url: '/dashboard' },
  ];

  return (
    <AppLayout title="Dashboard Operacional" breadcrumbs={breadcrumbs}>
      <div className="space-y-6">
        {/* KPI Cards */}
        <div className="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
          <KpiCard
            label="Ordens em Curso"
            value={kpis.active_orders}
            color="blue"
          />
          <KpiCard
            label="Tarefas Pendentes"
            value={kpis.pending_tasks}
            color="yellow"
          />
          <KpiCard
            label="Equipas no Terreno (MTs)"
            value={kpis.active_mini_tasks}
            color="green"
          />
          <KpiCard
            label="Horas Registadas Hoje"
            value={kpis.today_work_hours}
            unit="hrs"
            color="indigo"
          />
        </div>

        {/* Bottom grid: Critical Orders + Intervention Map */}
        <div className="grid grid-cols-1 gap-6 lg:grid-cols-2">
          {/* Critical Orders */}
          <div className="overflow-hidden rounded-lg bg-white shadow-sm dark:bg-gray-800">
            <div className="border-b border-gray-200 bg-gray-50 px-6 py-4 dark:border-gray-700 dark:bg-gray-800/80">
              <h3 className="flex items-center gap-2 text-lg font-medium text-red-600 dark:text-red-400">
                <svg className="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
                  <path strokeLinecap="round" strokeLinejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.693-.833-2.464 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z" />
                </svg>
                Ordens Críticas (Prioridade Alta)
              </h3>
            </div>
            {criticalOrders.length > 0 ? (
              <ul className="divide-y divide-gray-200 dark:divide-gray-700">
                {criticalOrders.map((order) => (
                  <CriticalOrderItem key={order.id} order={order} />
                ))}
              </ul>
            ) : (
              <p className="px-6 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                Não existem ordens críticas pendentes. Excelente trabalho!
              </p>
            )}
          </div>

          {/* Intervention Map */}
          <div className="overflow-hidden rounded-lg bg-white shadow-sm dark:bg-gray-800">
            <div className="border-b border-gray-200 bg-gray-50 px-6 py-4 dark:border-gray-700 dark:bg-gray-800/80">
              <h3 className="flex items-center gap-2 text-lg font-medium text-gray-900 dark:text-white">
                <svg className="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
                  <path strokeLinecap="round" strokeLinejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                Mapa de Intervenções
              </h3>
            </div>
            <div className="p-1" style={{ height: 420 }}>
              <InterventionMap orders={mapOrders} apiKey={googleMapsApiKey} />
            </div>
          </div>
        </div>
      </div>
    </AppLayout>
  );
}
