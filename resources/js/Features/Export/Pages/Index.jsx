import { t } from '@/utils/i18n';
import AppLayout from '@/Layouts/AppLayout';

export default function ExportsIndex() {
  const breadcrumbs = [
    { name: t('pages.sidebar.dashboard'), url: '/dashboard' },
    { name: t('pages.sidebar.exports'), url: '/exports' },
  ];

  return (
    <AppLayout title={t('pages.sidebar.exports')} breadcrumbs={breadcrumbs}>
      <div className="flex-1 flex flex-col p-6">
        <div className="mb-6">
          <h1 className="text-2xl font-bold text-brand-darkest">{t('pages.dev_pages.title_exports')}</h1>
          <p className="text-sm text-brand-mid mt-1">
            {t('pages.dev_pages.subtitle_exports')}
          </p>
        </div>

        <div className="flex-1 rounded-lg border border-dashed border-brand-accent/30 bg-brand-white flex items-center justify-center">
          <div className="text-center">
            <svg className="mx-auto h-12 w-12 text-brand-accent/60" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={1.5}>
              <path strokeLinecap="round" strokeLinejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
            </svg>
            <h3 className="mt-4 text-lg font-semibold text-brand-accent">{t('pages.dev_pages.wip_title')}</h3>
            <p className="mt-2 text-sm text-brand-mid max-w-sm">
              {t('pages.dev_pages.wip_desc_exports')}
            </p>
            <span className="inline-flex items-center gap-1 mt-4 rounded-full bg-brand-accent/15 px-3 py-1 text-xs font-medium text-brand-accent ring-1 ring-brand-accent/30">
              {t('pages.dev_pages.dev_badge')}
            </span>
          </div>
        </div>
      </div>
    </AppLayout>
  );
}
