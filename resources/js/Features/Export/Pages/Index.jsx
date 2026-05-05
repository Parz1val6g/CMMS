import AppLayout from '@/Layouts/AppLayout';

export default function ExportsIndex() {
  const breadcrumbs = [
    { name: 'Dashboard', url: '/dashboard' },
    { name: 'Exports', url: '/exports' },
  ];

  return (
    <AppLayout title="Exports" breadcrumbs={breadcrumbs}>
      <div className="flex-1 flex flex-col p-6">
        <div className="mb-6">
          <h1 className="text-2xl font-bold text-white">Exports</h1>
          <p className="text-sm text-slate-400 mt-1">
            Export data to CSV format
          </p>
        </div>

        <div className="flex-1 rounded-lg border border-dashed border-indigo-500/30 bg-slate-800/50 flex items-center justify-center">
          <div className="text-center">
            <svg className="mx-auto h-12 w-12 text-indigo-400/60" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={1.5}>
              <path strokeLinecap="round" strokeLinejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
            </svg>
            <h3 className="mt-4 text-lg font-semibold text-indigo-300">Work in Progress</h3>
            <p className="mt-2 text-sm text-slate-400 max-w-sm">
              The export interface is being built. API endpoints for Service Orders and Work Logs CSV export are already available.
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
