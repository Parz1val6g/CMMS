import { usePage } from '@inertiajs/react';

export default function TopBar() {
  const { props: { auth } } = usePage();

  return (
    <header className="shrink-0 flex items-center border-b border-gray-200 bg-white px-6 py-3 dark:border-gray-700 dark:bg-gray-800">
      {auth?.user && (
        <span className="text-sm text-gray-600 dark:text-gray-400">
          {auth.user.first_name} {auth.user.last_name}
        </span>
      )}
    </header>
  );
}
