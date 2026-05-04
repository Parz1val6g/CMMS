import { Head, Link, useForm } from '@inertiajs/react';

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
      <Head title="Sign In" />

      <div className="flex min-h-screen items-center justify-center bg-gradient-to-br from-gray-50 to-gray-100 px-3 dark:from-gray-900 dark:to-gray-950">
        <div className="w-full max-w-sm">
          <div className="rounded-2xl border-0 bg-white px-6 py-8 shadow-xl dark:bg-gray-800">
            <div className="mb-6 text-center">
              <div className="mx-auto mb-3 flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-indigo-500 to-indigo-600 text-white shadow-sm">
                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="currentColor" viewBox="0 0 16 16" aria-hidden="true">
                  <path d="M11 6a3 3 0 1 1-6 0 3 3 0 0 1 6 0z" />
                  <path fillRule="evenodd" d="M0 8a8 8 0 1 1 16 0A8 8 0 0 1 0 8zm8-7a7 7 0 0 0-5.468 11.37C3.242 11.226 4.805 10 8 10s4.757 1.225 5.468 2.37A7 7 0 0 0 8 1z" />
                </svg>
              </div>
              <h4 className="mb-0 text-xl font-bold text-gray-900 dark:text-white">Sign In</h4>
            </div>

            <form onSubmit={submit} noValidate>
              {/* Email */}
              <div className="mb-3">
                <label htmlFor="email" className="mb-1 block text-xs font-semibold text-gray-500 dark:text-gray-400">
                  Email or Username <span className="text-red-500">*</span>
                </label>
                <input
                  id="email"
                  type="email"
                  value={data.email}
                  onChange={(e) => setData('email', e.target.value)}
                  className={`block w-full rounded-xl border-0 bg-gray-100 px-4 py-3 text-sm shadow-none focus:ring-2 focus:ring-indigo-500 dark:bg-gray-700 dark:text-gray-200 ${errors.email ? 'ring-2 ring-red-500' : ''
                    }`}
                  required
                  autoFocus
                  aria-label="Email or Username"
                />
                {errors.email && (
                  <div className="mt-1 text-xs text-red-600 dark:text-red-400">{errors.email}</div>
                )}
              </div>

              {/* Password */}
              <div className="mb-4">
                <label htmlFor="password" className="mb-1 block text-xs font-semibold text-gray-500 dark:text-gray-400">
                  Password <span className="text-red-500">*</span>
                </label>
                <input
                  id="password"
                  type="password"
                  value={data.password}
                  onChange={(e) => setData('password', e.target.value)}
                  className={`block w-full rounded-xl border-0 bg-gray-100 px-4 py-3 text-sm shadow-none focus:ring-2 focus:ring-indigo-500 dark:bg-gray-700 dark:text-gray-200 ${errors.password ? 'ring-2 ring-red-500' : ''
                    }`}
                  required
                  aria-label="Password"
                />
                {errors.password && (
                  <div className="mt-1 text-xs text-red-600 dark:text-red-400">{errors.password}</div>
                )}
              </div>

              {/* Submit */}
              <div className="mb-4 mt-2">
                <button
                  type="submit"
                  disabled={processing}
                  className="w-full rounded-xl bg-indigo-600 px-4 py-3 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700 disabled:opacity-50 transition-colors"
                >
                  {processing ? 'Signing in...' : 'Sign In'}
                </button>
              </div>
            </form>

            {/* Register Link */}
            <div className="mt-3 text-center">
              <Link href="/register" className="text-xs font-medium text-gray-500 no-underline hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                Create an account
              </Link>
            </div>
          </div>
        </div>
      </div>
    </>
  );
}
