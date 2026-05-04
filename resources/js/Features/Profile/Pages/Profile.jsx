import AppLayout from '@/Layouts/AppLayout';
import { Link } from '@inertiajs/react';
import Badge from '@/Components/Common/Badge';

export default function Profile({ user }) {
  const statusVariant = user.status === 'active' ? 'success' : user.status === 'pending' ? 'warning' : 'danger';

  return (
    <AppLayout title="Profile">
      <div className="h-full overflow-y-auto w-full">
        <div className="max-w-7xl mx-auto py-8 px-6">
        <h1 className="mb-6 text-2xl font-bold text-gray-900 dark:text-white">Profile</h1>

        <div className="rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
          <h5 className="mb-4 text-lg font-semibold text-gray-900 dark:text-white">User Information</h5>

          <dl className="space-y-3 text-sm">
            <div className="flex justify-between">
              <dt className="font-medium text-gray-500 dark:text-gray-400">Name:</dt>
              <dd className="text-gray-900 dark:text-white">{user.full_name}</dd>
            </div>
            <div className="flex justify-between">
              <dt className="font-medium text-gray-500 dark:text-gray-400">Email:</dt>
              <dd className="text-gray-900 dark:text-white">{user.email}</dd>
            </div>
            <div className="flex justify-between">
              <dt className="font-medium text-gray-500 dark:text-gray-400">Role:</dt>
              <dd className="capitalize text-gray-900 dark:text-white">{user.role}</dd>
            </div>
            <div className="flex justify-between">
              <dt className="font-medium text-gray-500 dark:text-gray-400">Status:</dt>
              <dd>
                <Badge variant={statusVariant}>
                  {user.status}
                </Badge>
              </dd>
            </div>
          </dl>

          <hr className="my-4 border-gray-200 dark:border-gray-700" />

          <Link
            href="/dashboard"
            className="inline-flex items-center rounded-lg bg-gray-100 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-200 transition-colors dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600"
          >
            Back
          </Link>
        </div>
        </div>
      </div>
    </AppLayout>
  );
}
