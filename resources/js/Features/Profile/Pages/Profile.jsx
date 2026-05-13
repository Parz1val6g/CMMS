import AppLayout from '@/Layouts/AppLayout';
import { Link } from '@inertiajs/react';
import { t } from '@/utils/i18n';
import Badge from '@/Components/Common/Badge';

export default function Profile({ user }) {
  const statusVariant = user.status === 'active' ? 'success' : user.status === 'pending' ? 'warning' : 'danger';

  return (
    <AppLayout title={t('pages.profile.page_title')}>
      <div className="h-full overflow-y-auto w-full">
        <div className="max-w-7xl mx-auto py-8 px-6">
        <h1 className="mb-6 text-2xl font-bold text-brand-darkest">{t('pages.profile.page_title')}</h1>

        <div className="rounded-xl border border-brand-mid/20 bg-brand-white p-6 shadow-sm">
          <h5 className="mb-4 text-lg font-semibold text-brand-darkest">{t('pages.profile.section_user_info')}</h5>

          <dl className="space-y-3 text-sm">
            <div className="flex justify-between">
              <dt className="font-medium text-brand-mid">{t('pages.profile.label_name')}</dt>
              <dd className="text-brand-darkest">{user.full_name}</dd>
            </div>
            <div className="flex justify-between">
              <dt className="font-medium text-brand-mid">{t('pages.profile.label_email')}</dt>
              <dd className="text-brand-darkest">{user.email}</dd>
            </div>
            <div className="flex justify-between">
              <dt className="font-medium text-brand-mid">{t('pages.profile.label_role')}</dt>
              <dd className="capitalize text-brand-darkest">{user.role}</dd>
            </div>
            <div className="flex justify-between">
              <dt className="font-medium text-brand-mid">{t('pages.profile.label_status')}</dt>
              <dd>
                <Badge variant={statusVariant}>
                  {user.status}
                </Badge>
              </dd>
            </div>
          </dl>

          <hr className="my-4 border-brand-mid/20" />

          <Link
            href="/dashboard"
            className="inline-flex items-center rounded-lg bg-gray-100 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-200 transition-colors"
          >
            {t('pages.profile.btn_back')}
          </Link>
        </div>
        </div>
      </div>
    </AppLayout>
  );
}
