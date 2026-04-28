import { Head, Link, useForm } from '@inertiajs/react';

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
      <Head title="Register" />

      <div className="flex min-h-screen items-center justify-center bg-gradient-to-br from-gray-50 to-gray-100 px-3 dark:from-gray-900 dark:to-gray-950">
        <div className="w-full max-w-sm">
          <div className="rounded-2xl border-0 bg-white px-6 py-8 shadow-xl dark:bg-gray-800">
            <div className="mb-6 text-center">
              <div className="mx-auto mb-3 flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-indigo-500 to-indigo-600 text-white shadow-sm">
                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="currentColor" viewBox="0 0 16 16" aria-hidden="true">
                  <path d="M12.5 16a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7Zm.5-5v1h1a.5.5 0 0 1 0 1h-1v1a.5.5 0 0 1-1 0v-1h-1a.5.5 0 0 1 0-1h1v-1a.5.5 0 0 1 1 0Zm-2-6a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                  <path d="M2 13c0 1 1 1 1 1h5.256A4.493 4.493 0 0 1 8 12.5a4.49 4.49 0 0 1 1.544-3.393C9.077 9.038 8.564 9 8 9c-5 0-6 3-6 4Z" />
                </svg>
              </div>
              <h4 className="text-xl font-bold text-gray-900 dark:text-white">Create Account</h4>
              <p className="mt-1 text-xs text-gray-500 dark:text-gray-400">Start using the platform</p>
            </div>

            <form onSubmit={submit} noValidate>
              {/* First Name */}
              <div className="mb-3">
                <label htmlFor="first_name" className="mb-1 block text-xs font-semibold text-gray-500 dark:text-gray-400">
                  Full Name
                </label>
                <input
                  id="first_name"
                  type="text"
                  value={data.first_name}
                  onChange={(e) => setData('first_name', e.target.value)}
                  className={`block w-full rounded-xl border-0 bg-gray-100 px-4 py-3 text-sm shadow-none focus:ring-2 focus:ring-indigo-500 dark:bg-gray-700 dark:text-gray-200 ${
                    errors.first_name ? 'ring-2 ring-red-500' : ''
                  }`}
                  placeholder="John Doe"
                  required
                  autoFocus
                  aria-label="Full Name"
                />
                {errors.first_name && (
                  <div className="mt-1 text-xs text-red-600 dark:text-red-400">{errors.first_name}</div>
                )}
              </div>

              {/* Email */}
              <div className="mb-3">
                <label htmlFor="email" className="mb-1 block text-xs font-semibold text-gray-500 dark:text-gray-400">
                  Work Email
                </label>
                <input
                  id="email"
                  type="email"
                  value={data.email}
                  onChange={(e) => setData('email', e.target.value)}
                  className={`block w-full rounded-xl border-0 bg-gray-100 px-4 py-3 text-sm shadow-none focus:ring-2 focus:ring-indigo-500 dark:bg-gray-700 dark:text-gray-200 ${
                    errors.email ? 'ring-2 ring-red-500' : ''
                  }`}
                  placeholder="name@company.com"
                  required
                  aria-label="Work Email"
                />
                {errors.email && (
                  <div className="mt-1 text-xs text-red-600 dark:text-red-400">{errors.email}</div>
                )}
              </div>

              {/* Password */}
              <div className="mb-3">
                <label htmlFor="password" className="mb-1 block text-xs font-semibold text-gray-500 dark:text-gray-400">
                  Password
                </label>
                <input
                  id="password"
                  type="password"
                  value={data.password}
                  onChange={(e) => setData('password', e.target.value)}
                  className={`block w-full rounded-xl border-0 bg-gray-100 px-4 py-3 text-sm shadow-none focus:ring-2 focus:ring-indigo-500 dark:bg-gray-700 dark:text-gray-200 ${
                    errors.password ? 'ring-2 ring-red-500' : ''
                  }`}
                  placeholder="Minimum 8 characters"
                  required
                  aria-label="Password"
                />
                {errors.password && (
                  <div className="mt-1 text-xs text-red-600 dark:text-red-400">{errors.password}</div>
                )}
              </div>

              {/* Confirm Password */}
              <div className="mb-4">
                <label htmlFor="password_confirmation" className="mb-1 block text-xs font-semibold text-gray-500 dark:text-gray-400">
                  Confirm Password
                </label>
                <input
                  id="password_confirmation"
                  type="password"
                  value={data.password_confirmation}
                  onChange={(e) => setData('password_confirmation', e.target.value)}
                  className="block w-full rounded-xl border-0 bg-gray-100 px-4 py-3 text-sm shadow-none focus:ring-2 focus:ring-indigo-500 dark:bg-gray-700 dark:text-gray-200"
                  required
                  aria-label="Confirm Password"
                />
              </div>

              {/* Terms */}
              <div className="mb-4 flex items-start gap-2">
                <input
                  id="accept_terms"
                  type="checkbox"
                  checked={data.accept_terms}
                  onChange={(e) => setData('accept_terms', e.target.checked)}
                  className="mt-0.5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 dark:border-gray-600"
                />
                <label htmlFor="accept_terms" className="text-xs text-gray-500 dark:text-gray-400">
                  I agree to the{' '}
                  <a href="#" className="font-semibold text-indigo-600 no-underline hover:underline dark:text-indigo-400">Terms of Service</a> and{' '}
                  <a href="#" className="font-semibold text-indigo-600 no-underline hover:underline dark:text-indigo-400">Privacy Policy</a>.
                </label>
              </div>

              {/* Submit */}
              <div className="mb-4">
                <button
                  type="submit"
                  disabled={processing}
                  className="w-full rounded-xl bg-indigo-600 px-4 py-3 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700 disabled:opacity-50 transition-colors"
                >
                  {processing ? 'Creating account...' : 'Create Account'}
                </button>
              </div>
            </form>

            {/* Sign In Link */}
            <div className="mt-3 text-center">
              <span className="text-xs font-medium text-gray-500 dark:text-gray-400">Already have an account?</span>{' '}
              <Link href="/login" className="text-xs font-bold text-indigo-600 no-underline hover:underline dark:text-indigo-400">
                Sign In
              </Link>
            </div>
          </div>
        </div>
      </div>
    </>
  );
}
