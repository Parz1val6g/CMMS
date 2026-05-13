import { Head, Link, useForm } from '@inertiajs/react';
import { t } from '@/utils/i18n';

export default function Login() {
  const { data, setData, post, processing, errors } = useForm({
    email: '',
    password: '',
  });

  const submit = (e) => {
    e.preventDefault();
    post('/login');
  };

  return (
    <>
      <Head title={t('pages.auth.login.title')} />

      <div className="flex min-h-screen items-center justify-center bg-gradient-to-br from-brand-light to-brand-light px-3">
        <div className="w-full max-w-sm">
          <div className="rounded-2xl border-0 bg-brand-white px-6 py-8 shadow-xl">
            <div className="mb-6 text-center">
              <div className="mx-auto mb-3 flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-brand-accent to-brand-accent text-brand-white shadow-sm">
                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="currentColor" viewBox="0 0 16 16" aria-hidden="true">
                  <path d="M11 6a3 3 0 1 1-6 0 3 3 0 0 1 6 0z" />
                  <path fillRule="evenodd" d="M0 8a8 8 0 1 1 16 0A8 8 0 0 1 0 8zm8-7a7 7 0 0 0-5.468 11.37C3.242 11.226 4.805 10 8 10s4.757 1.225 5.468 2.37A7 7 0 0 0 8 1z" />
                </svg>
              </div>
              <h4 className="mb-0 text-xl font-bold text-brand-darkest">{t('pages.auth.login.title')}</h4>
            </div>

            <form onSubmit={submit} noValidate>
              {/* Email */}
              <div className="mb-3">
                <label htmlFor="email" className="mb-1 block text-xs font-semibold text-brand-mid">
                  {t('pages.auth.login.email_label')} <span className="text-red-500">*</span>
                </label>
                <input
                  id="email"
                  type="email"
                  value={data.email}
                  onChange={(e) => setData('email', e.target.value)}
                  className={`block w-full rounded-xl border-0 bg-brand-light px-4 py-3 text-sm shadow-none focus:ring-2 focus:ring-brand-accent ${errors.email ? 'ring-2 ring-red-500' : ''
                    }`}
                  required
                  autoFocus
                  aria-label={t('pages.auth.login.email_label')}
                />
                {errors.email && (
                  <div className="mt-1 text-xs text-red-600">{errors.email}</div>
                )}
              </div>

              {/* Password */}
              <div className="mb-4">
                <label htmlFor="password" className="mb-1 block text-xs font-semibold text-brand-mid">
                  {t('pages.auth.login.password_label')} <span className="text-red-500">*</span>
                </label>
                <input
                  id="password"
                  type="password"
                  value={data.password}
                  onChange={(e) => setData('password', e.target.value)}
                  className={`block w-full rounded-xl border-0 bg-brand-light px-4 py-3 text-sm shadow-none focus:ring-2 focus:ring-brand-accent ${errors.password ? 'ring-2 ring-red-500' : ''
                    }`}
                  required
                  aria-label={t('pages.auth.login.password_label')}
                />
                {errors.password && (
                  <div className="mt-1 text-xs text-red-600">{errors.password}</div>
                )}
              </div>

              {/* Submit */}
              <div className="mb-4 mt-2">
                <button
                  type="submit"
                  disabled={processing}
                  className="w-full rounded-xl bg-brand-accent px-4 py-3 text-sm font-semibold text-brand-white shadow-sm hover:bg-brand-accent/90 disabled:opacity-50 transition-colors"
                >
                  {processing ? t('pages.auth.login.signing_in') : t('pages.auth.login.submit')}
                </button>
              </div>
            </form>

            {/* Register Link */}
            <div className="mt-3 text-center">
              <Link href="/register" className="text-xs font-medium text-brand-mid no-underline hover:text-brand-darkest">
                {t('pages.auth.login.create_account')}
              </Link>
            </div>
          </div>
        </div>
      </div>
    </>
  );
}
