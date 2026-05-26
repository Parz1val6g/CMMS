import AppLayout from '@/Layouts/AppLayout';
import { Link } from '@inertiajs/react';
import { t } from '@/utils/i18n';
import Badge from '@/Components/Common/Badge';

const ROLE_VARIANT = {
  admin:              'danger',
  manager:            'primary',
  equipment_manager:  'info',
  supervisor:         'warning',
  worker:             'secondary',
  client:             'success',
  entidade:           'info',
  task_manager:       'primary',
  mini_task_manager:  'secondary',
  work_log_manager:   'secondary',
  sector_manager:     'warning',
  ticket_manager:     'info',
  team_manager:       'primary',
  attendant:          'success',
};

function Avatar({ name }) {
  const initials = name
    .split(' ')
    .filter(Boolean)
    .slice(0, 2)
    .map((w) => w[0].toUpperCase())
    .join('');

  return (
    <div className="flex h-24 w-24 items-center justify-center rounded-full bg-brand-accent text-white text-2xl font-bold shadow-md ring-4 ring-white">
      {initials}
    </div>
  );
}

function DetailField({ label, value }) {
  return (
    <div className="flex flex-col gap-0.5">
      <span className="text-xs font-semibold uppercase tracking-wide text-gray-400">{label}</span>
      <span className="text-sm font-medium text-gray-900">{value || '—'}</span>
    </div>
  );
}

function StatCard({ label, value, icon }) {
  return (
    <div className="flex flex-col gap-1 rounded-xl border border-gray-100 bg-gray-50 p-4">
      <div className="flex items-center gap-2 text-gray-400">
        <span className="text-lg">{icon}</span>
        <span className="text-xs font-semibold uppercase tracking-wide">{label}</span>
      </div>
      <span className="text-xl font-bold text-gray-800">{value ?? '—'}</span>
    </div>
  );
}

function formatDate(iso) {
  if (!iso) return null;
  return new Date(iso).toLocaleDateString(undefined, {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
  });
}

function statusConfig(status) {
  if (status === 'active')   return { variant: 'success', label: t('pages.profile.status_active') };
  if (status === 'pending')  return { variant: 'warning', label: t('pages.profile.status_pending') };
  return { variant: 'danger', label: t('pages.profile.status_inactive') };
}

export default function Profile({ user }) {
  const status = statusConfig(user.status);

  return (
    <AppLayout title={t('pages.profile.page_title')}>
      <div className="h-full overflow-y-auto w-full bg-gray-50">
        <div className="max-w-7xl mx-auto py-8 px-4 sm:px-6">

          {/* Page header */}
          <div className="mb-6 flex flex-wrap items-center gap-3">
            <Link
              href="/dashboard"
              className="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-sm font-medium text-gray-600 shadow-sm hover:bg-gray-50 transition-colors"
            >
              <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                <path fillRule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clipRule="evenodd" />
              </svg>
              {t('pages.profile.btn_back')}
            </Link>
            <h1 className="text-2xl font-bold text-brand-darkest">{t('pages.profile.page_title')}</h1>
          </div>

          {/* Two-column grid */}
          <div className="grid grid-cols-1 gap-6 lg:grid-cols-3">

            {/* ── Left column: Identity card ── */}
            <div className="flex flex-col gap-4 lg:col-span-1">
              <div className="rounded-2xl border border-gray-100 bg-white shadow-sm p-6 flex flex-col items-center text-center gap-4">
                <Avatar name={user.full_name} />

                <div className="flex flex-col gap-0.5">
                  <h2 className="text-xl font-bold text-gray-900">{user.full_name}</h2>
                  <p className="text-sm text-gray-500">{user.email}</p>
                </div>

                {/* Status */}
                <Badge variant={status.variant} pill>
                  {status.label}
                </Badge>

                {/* Roles */}
                {user.roles && user.roles.length > 0 && (
                  <div className="flex flex-wrap justify-center gap-1.5">
                    {user.roles.map((role) => (
                      <Badge key={role} variant={ROLE_VARIANT[role] ?? 'secondary'} pill>
                        {role}
                      </Badge>
                    ))}
                  </div>
                )}

                {/* Action buttons */}
                <div className="mt-2 flex w-full flex-col gap-2">
                  <Link
                    href="/settings"
                    className="inline-flex w-full items-center justify-center rounded-lg bg-brand-accent px-4 py-2 text-sm font-medium text-white hover:opacity-90 transition-opacity"
                  >
                    {t('pages.profile.btn_edit')}
                  </Link>
                  <Link
                    href="/settings"
                    className="inline-flex w-full items-center justify-center rounded-lg border border-brand-accent/30 bg-brand-accent/5 px-4 py-2 text-sm font-medium text-brand-accent hover:bg-brand-accent/10 transition-colors"
                  >
                    {t('pages.profile.btn_change_password')}
                  </Link>
                </div>
              </div>
            </div>

            {/* ── Right column: Details + Stats ── */}
            <div className="flex flex-col gap-6 lg:col-span-2">

              {/* Details card */}
              <div className="rounded-2xl border border-gray-100 bg-white shadow-sm p-6">
                <h3 className="mb-5 text-sm font-semibold uppercase tracking-wider text-gray-400">
                  {t('pages.profile.section_details')}
                </h3>
                <div className="grid grid-cols-1 gap-5 sm:grid-cols-2 md:grid-cols-3">
                  <DetailField label={t('pages.profile.label_name')} value={user.full_name} />
                  <DetailField label={t('pages.profile.label_email')} value={user.email} />
                  <DetailField
                    label={t('pages.profile.label_phone')}
                    value={user.phone || t('pages.profile.no_phone')}
                  />
                  <DetailField
                    label={t('pages.profile.label_locale')}
                    value={user.locale || t('pages.profile.no_locale')}
                  />
                  <DetailField label={t('pages.profile.label_status')} value={status.label} />
                  <DetailField
                    label={t('pages.profile.label_member_since')}
                    value={formatDate(user.created_at)}
                  />
                </div>
              </div>

              {/* Quick Stats card */}
              <div className="rounded-2xl border border-gray-100 bg-white shadow-sm p-6">
                <h3 className="mb-5 text-sm font-semibold uppercase tracking-wider text-gray-400">
                  {t('pages.profile.section_stats')}
                </h3>
                <div className="grid grid-cols-1 gap-4 sm:grid-cols-3">
                  <StatCard
                    label={t('pages.profile.label_permissions')}
                    value={user.permissions_count ?? 0}
                    icon="🔑"
                  />
                  <StatCard
                    label={t('pages.profile.label_last_access')}
                    value={formatDate(user.created_at)}
                    icon="🕐"
                  />
                  <StatCard
                    label={t('pages.profile.label_registered_actions')}
                    value={null}
                    icon="📋"
                  />
                </div>
              </div>

            </div>
          </div>

        </div>
      </div>
    </AppLayout>
  );
}
