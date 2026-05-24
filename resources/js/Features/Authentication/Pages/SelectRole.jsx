import { Head, router, useForm } from '@inertiajs/react';

export default function SelectRole({ availableRoles }) {
  const { data, setData, post } = useForm({ role: '' });

  const handleSelect = (roleName) => {
    setData('role', roleName);
    post('/api/auth/switch-role', {
      onSuccess: () => router.visit('/dashboard'),
    });
  };

  return (
    <div className="flex min-h-screen items-center justify-center bg-gray-100 dark:bg-gray-900">
      <Head title="Select Role" />
      <div className="w-full max-w-md rounded-lg bg-white p-8 shadow-md dark:bg-gray-800">
        <h1 className="mb-6 text-center text-2xl font-bold text-gray-900 dark:text-white">
          Select Your Role
        </h1>
        <p className="mb-6 text-center text-gray-600 dark:text-gray-400">
          You have multiple roles. Choose which one to use for this session.
        </p>
        <div className="space-y-3">
          {availableRoles.map((role) => (
            <button
              key={role.name}
              type="button"
              onClick={() => handleSelect(role.name)}
              className="w-full rounded-lg border border-gray-300 px-4 py-3 text-left transition hover:bg-blue-50 dark:border-gray-600 dark:hover:bg-blue-900/20"
            >
              <span className="font-medium text-gray-900 dark:text-white">
                {role.label}
              </span>
            </button>
          ))}
        </div>
      </div>
    </div>
  );
}
