import { useState, useRef, useEffect } from 'react';
import { usePage, router } from '@inertiajs/react';
import { ChevronDown, Check } from 'lucide-react';
import { t } from '@/utils/i18n';

function RoleSwitcher() {
  const { props: { availableRoles, activeRole } } = usePage();
  const [open, setOpen] = useState(false);
  const ref = useRef(null);

  useEffect(() => {
    const handler = (e) => {
      if (ref.current && !ref.current.contains(e.target)) {
        setOpen(false);
      }
    };
    document.addEventListener('mousedown', handler);
    return () => document.removeEventListener('mousedown', handler);
  }, []);

  if (!availableRoles || availableRoles.length <= 1) {
    return null;
  }

  const activeLabel = availableRoles.find((r) => r.name === activeRole)?.label
    || availableRoles[0]?.label
    || t('common.topbar.select_role');

  const handleSwitch = (roleName) => {
    setOpen(false);
    router.post('/switch-role', { role: roleName });
  };

  return (
    <div className="relative" ref={ref}>
      <button
        type="button"
        onClick={() => setOpen((prev) => !prev)}
        className="flex items-center gap-2 rounded-lg border border-gray-300 px-3 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700"
      >
        <span className="max-w-[140px] truncate">{activeLabel}</span>
        <ChevronDown className={`h-4 w-4 transition-transform ${open ? 'rotate-180' : ''}`} />
      </button>

      {open && (
        <div className="absolute right-0 z-50 mt-1 w-56 rounded-lg border border-gray-200 bg-white py-1 shadow-lg dark:border-gray-600 dark:bg-gray-800">
          {availableRoles.map((role) => (
            <button
              key={role.name}
              type="button"
              onClick={() => handleSwitch(role.name)}
              className={`flex w-full items-center gap-3 px-4 py-2 text-left text-sm transition hover:bg-gray-50 dark:hover:bg-gray-700 ${
                role.name === activeRole
                  ? 'font-semibold text-blue-600 dark:text-blue-400'
                  : 'text-gray-700 dark:text-gray-300'
              }`}
            >
              <span className="flex-1 truncate">{role.label}</span>
              {role.name === activeRole && (
                <Check className="h-4 w-4 shrink-0 text-blue-600 dark:text-blue-400" />
              )}
            </button>
          ))}
        </div>
      )}
    </div>
  );
}

export default function TopBar() {
  const { props: { auth } } = usePage();

  return (
    <header className="shrink-0 flex items-center justify-between border-b border-gray-200 bg-white px-6 py-3 dark:border-gray-700 dark:bg-gray-800">
      <div className="flex items-center gap-3">
        {auth?.user && (
          <span className="text-sm text-gray-600 dark:text-gray-400">
            {auth.user.first_name} {auth.user.last_name}
          </span>
        )}
      </div>
      <RoleSwitcher />
    </header>
  );
}
