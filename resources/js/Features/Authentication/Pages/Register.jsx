import { Head, Link, useForm } from '@inertiajs/react';
import { t } from '@/utils/i18n';

export default function Register() {
  const { data, setData, post, processing, errors } = useForm({
    first_name: '',
    email: '',
    password: '',
    password_confirmation: '',
    accept_terms: false,
  });

  const submit = (e) => {
    e.preventDefault();
    post('/register');
  };

  return (
    <>
      <Head title={t('pages.auth.register.title')} />

      <div className="flex min-h-screen items-center justify-center bg-gradient-to-br from-brand-light to-brand-light px-3">
        <div className="w-full max-w-sm">
          <div className="rounded-2xl border-0 bg-brand-white px-6 py-8 shadow-xl">
            <div className="mb-6 text-center">
              <div className="mx-auto mb-3 flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-brand-accent to-brand-accent text-brand-white shadow-sm">
                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="currentColor" viewBox="0 0 16 16" aria-hidden="true">
                  <path d="M12.5 16a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7Zm.5-5v1h1a.5.5 0 0 1 0 1h-1v1a.5.5 0 0 1-1 0v-1h-1a.5.5 0 0 1 0-1h1v-1a.5.5 0 0 1 1 0Zm-2-6a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                  <path d="M2 13c0 1 1 1 1 1h5.256A4.493 4.493 0 0 1 8 12.5a4.49 4.49 0 0 1 1.544-3.393C9.077 9.038 8.564 9 8 9c-5 0-6 3-6 4Z" />
                </svg>
              </div>
              <h4 className="text-xl font-bold text-brand-darkest">{t('pages.auth.register.heading')}</h4>
              <p className="mt-1 text-xs text-brand-mid">{t('pages.auth.register.subtitle')}</p>
            </div>

            <form onSubmit={submit} noValidate>
              {/* First Name */}
              <div className="mb-3">
                <label htmlFor="first_name" className="mb-1 block text-xs font-semibold text-brand-mid">
                  {t('pages.auth.register.full_name_label')} <span className="text-red-500">*</span>
                </label>
                <input
                  id="first_name"
                  type="text"
                  value={data.first_name}
                  onChange={(e) => setData('first_name', e.target.value)}
                  className={`block w-full rounded-xl border-0 bg-brand-light px-4 py-3 text-sm shadow-none focus:ring-2 focus:ring-brand-accent ${errors.first_name ? 'ring-2 ring-red-500' : ''
                    }`}
                  placeholder={t('pages.auth.register.full_name_placeholder')}
                  required
                  autoFocus
                  aria-label={t('pages.auth.register.full_name_label')}
                />
                {errors.first_name && (
                  <div className="mt-1 text-xs text-red-600">{errors.first_name}</div>
                )}
              </div>

              {/* Email */}
              <div className="mb-3">
                <label htmlFor="email" className="mb-1 block text-xs font-semibold text-brand-mid">
                  {t('pages.auth.register.work_email_label')} <span className="text-red-500">*</span>
                </label>
                <input
                  id="email"
                  type="email"
                  value={data.email}
                  onChange={(e) => setData('email', e.target.value)}
                  className={`block w-full rounded-xl border-0 bg-brand-light px-4 py-3 text-sm shadow-none focus:ring-2 focus:ring-brand-accent ${errors.email ? 'ring-2 ring-red-500' : ''
                    }`}
                  placeholder={t('pages.auth.register.work_email_placeholder')}
                  required
                  aria-label={t('pages.auth.register.work_email_label')}
                />
                {errors.email && (
                  <div className="mt-1 text-xs text-red-600">{errors.email}</div>
                )}
              </div>

              {/* Password */}
              <div className="mb-3">
                <label htmlFor="password" className="mb-1 block text-xs font-semibold text-brand-mid">
                  {t('pages.auth.register.password_label')} <span className="text-red-500">*</span>
                </label>
                <input
                  id="password"
                  type="password"
                  value={data.password}
                  onChange={(e) => setData('password', e.target.value)}
                  className={`block w-full rounded-xl border-0 bg-brand-light px-4 py-3 text-sm shadow-none focus:ring-2 focus:ring-brand-accent ${errors.password ? 'ring-2 ring-red-500' : ''
                    }`}
                  placeholder={t('pages.auth.register.password_placeholder')}
                  required
                  aria-label={t('pages.auth.register.password_label')}
                />
                {errors.password && (
                  <div className="mt-1 text-xs text-red-600">{errors.password}</div>
                )}
              </div>

              {/* Confirm Password */}
              <div className="mb-4">
                <label htmlFor="password_confirmation" className="mb-1 block text-xs font-semibold text-brand-mid">
                  {t('pages.auth.register.confirm_password_label')} <span className="text-red-500">*</span>
                </label>
                <input
                  id="password_confirmation"
                  type="password"
                  value={data.password_confirmation}
                  onChange={(e) => setData('password_confirmation', e.target.value)}
                  className="block w-full rounded-xl border-0 bg-brand-light px-4 py-3 text-sm shadow-none focus:ring-2 focus:ring-brand-accent"
                  required
                  aria-label={t('pages.auth.register.confirm_password_label')}
                />
              </div>

              {/* Terms */}
              <div className="mb-4 flex items-start gap-2">
                <input
                  id="accept_terms"
                  type="checkbox"
                  checked={data.accept_terms}
                  onChange={(e) => setData('accept_terms', e.target.checked)}
                  className="mt-0.5 rounded border-brand-mid/20 text-brand-accent focus:ring-brand-accent"
                />
                <label htmlFor="accept_terms" className="text-xs text-brand-mid">
                  {t('pages.auth.register.agree_terms')}{' '}
                  <a href="#" className="font-semibold text-brand-accent no-underline hover:underline">{t('pages.auth.register.terms_of_service')}</a> and{' '}
                  <a href="#" className="font-semibold text-brand-accent no-underline hover:underline">{t('pages.auth.register.privacy_policy')}</a>.
                </label>
              </div>

              {/* Submit */}
              <div className="mb-4">
                <button
                  type="submit"
                  disabled={processing}
                  className="w-full rounded-xl bg-brand-accent px-4 py-3 text-sm font-semibold text-brand-white shadow-sm hover:bg-brand-accent/90 disabled:opacity-50 transition-colors"
                >
                  {processing ? t('pages.auth.register.creating_account') : t('pages.auth.register.submit')}
                </button>
              </div>
            </form>

            {/* Sign In Link */}
            <div className="mt-3 text-center">
              <span className="text-xs font-medium text-brand-mid">{t('pages.auth.register.has_account')}</span>{' '}
              <Link href="/login" className="text-xs font-bold text-brand-accent no-underline hover:underline">
                {t('pages.auth.register.sign_in_link')}
              </Link>
            </div>
          </div>
        </div>
      </div>
    </>
  );
}
