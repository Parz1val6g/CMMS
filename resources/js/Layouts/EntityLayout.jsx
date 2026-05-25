import { Head, router, usePage } from '@inertiajs/react';
import { LogOut, Handshake } from 'lucide-react';
import { t } from '@/utils/i18n';

function EntityHeader({ user }) {
  const handleLogout = () => {
    router.post('/logout');
  };

  return (
    <header className="shrink-0 w-full bg-white border-b border-gray-200 px-6 py-3 flex items-center justify-between shadow-sm">
      <div className="flex items-center gap-2.5">
        <Handshake className="text-brand-accent" size={20} />
        <span className="text-gray-800 font-semibold text-sm tracking-wide">{t('layout.entity_portal.brand')}</span>
      </div>
      <div className="flex items-center gap-5">
        <span className="text-gray-700 text-sm font-medium">
          {user?.first_name} {user?.last_name}
        </span>
        <button
          onClick={handleLogout}
          className="flex items-center gap-1.5 text-gray-500 hover:text-gray-800 text-sm transition-colors"
        >
          <LogOut size={14} />
          {t('layout.entity_portal.logout')}
        </button>
      </div>
    </header>
  );
}

export default function EntityLayout({ title, children }) {
  const { auth } = usePage().props;

  return (
    <>
      <Head title={title} />
      <div className="h-screen w-screen flex flex-col overflow-hidden bg-gray-50">
        <EntityHeader user={auth?.user} />
        <main className="flex-1 overflow-auto p-6 lg:p-8">
          {children}
        </main>
        <footer className="shrink-0 w-full border-t border-gray-200 bg-white px-6 py-3 text-xs text-gray-400">
          {t('layout.entity_portal.footer', { year: new Date().getFullYear() })}
        </footer>
      </div>
    </>
  );
}
