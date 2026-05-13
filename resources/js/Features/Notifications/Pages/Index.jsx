import { t } from '@/utils/i18n';
import AppLayout from '@/Layouts/AppLayout';

export default function NotificationsIndex() {
  const breadcrumbs = [
    { name: t('pages.sidebar.dashboard'), url: '/dashboard' },
    { name: t('pages.sidebar.notifications'), url: '/notifications' },
  ];

  return (
    <AppLayout title={t('pages.sidebar.notifications')} breadcrumbs={breadcrumbs}>
      <div className="flex-1 flex flex-col p-6">
        <div className="mb-6">
          <h1 className="text-2xl font-bold text-brand-darkest">{t('pages.dev_pages.title_notifications')}</h1>
          <p className="text-sm text-brand-mid mt-1">
            {t('pages.dev_pages.subtitle_notifications')}
          </p>
        </div>

        <div className="flex-1 rounded-lg border border-dashed border-brand-accent/30 bg-brand-white flex items-center justify-center">
          <div className="text-center">
            <svg className="mx-auto h-12 w-12 text-brand-accent/60" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={1.5}>
              <path strokeLinecap="round" strokeLinejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
            </svg>
            <h3 className="mt-4 text-lg font-semibold text-brand-accent">{t('pages.dev_pages.wip_title')}</h3>
            <p className="mt-2 text-sm text-brand-mid max-w-sm">
              {t('pages.dev_pages.wip_desc_notifications')}
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
