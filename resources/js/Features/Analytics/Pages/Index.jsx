import AppLayout from '@/Layouts/AppLayout';

export default function AnalyticsIndex() {
  const breadcrumbs = [
    { name: 'Dashboard', url: '/dashboard' },
    { name: 'Analytics', url: '/analytics' },
  ];

  return (
    <AppLayout title="Analytics" breadcrumbs={breadcrumbs}>
      <div className="flex-1 flex flex-col p-6">
        <div className="mb-6">
          <h1 className="text-2xl font-bold text-white">Analytics & Reports</h1>
          <p className="text-sm text-slate-400 mt-1">
            Performance metrics, dashboards, and custom reports
          </p>
        </div>

        <div className="flex-1 rounded-lg border border-dashed border-indigo-500/30 bg-slate-800/50 flex items-center justify-center">
          <div className="text-center">
            <svg className="mx-auto h-12 w-12 text-indigo-400/60" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={1.5}>
              <path strokeLinecap="round" strokeLinejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />
            </svg>
            <h3 className="mt-4 text-lg font-semibold text-indigo-300">Work in Progress</h3>
            <p className="mt-2 text-sm text-slate-400 max-w-sm">
              Analytics dashboards and custom report builder are under development. Service order metrics, worker productivity, and material usage reports coming soon.
            </p>
            <span className="inline-flex items-center gap-1 mt-4 rounded-full bg-indigo-900/50 px-3 py-1 text-xs font-medium text-indigo-300 ring-1 ring-indigo-700/50">
              Dev Preview
            </span>
          </div>
        </div>
      </div>
    </AppLayout>
  );
}
