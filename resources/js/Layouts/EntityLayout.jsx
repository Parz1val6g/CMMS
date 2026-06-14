import { useCallback, useState } from 'react';
import { Head, router, usePage } from '@inertiajs/react';
import { LogOut, Handshake, Keyboard } from 'lucide-react';
import RolePalette from '@/Components/RolePalette';
import ShortcutsHelp from '@/Components/ShortcutsHelp';
import { t } from '@/utils/i18n';

function EntityHeader({ user }) {
  const handleLogout = () => {
    router.post('/logout');
  };

  return (
    <header className="shrink-0 w-full bg-white border-b border-gray-200 px-6 py-3 flex items-center justify-between shadow-sm">
      <div className="flex items-center gap-2.5">
        <Handshake className="text-brand-accent" size={20} />
        <span className="text-gray-800 font-semibold text-sm tracking-wide">{t('pages.layout.entity_portal.brand')}</span>
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
          {t('pages.layout.entity_portal.logout')}
        </button>
      </div>
    </header>
  );
}

export default function EntityLayout({ title, children }) {
  const { auth, can, availableRoles, activeRole } = usePage().props;
  const [shortcutsOpen, setShortcutsOpen] = useState(false);
  const [paletteOpen, setPaletteOpen] = useState(false);

  const toggleShortcuts = useCallback(() => setShortcutsOpen((prev) => !prev), []);

  const handleSwitchRole = useCallback((roleName) => {
    router.post('/switch-role', { role: roleName });
  }, []);

  const userName = auth?.user
    ? `${auth.user.first_name} ${auth.user.last_name}`
    : '';

  const hasMultipleRoles = availableRoles && availableRoles.length > 1;

  return (
    <>
      <Head title={title} />
      <div className="h-screen w-screen flex flex-col overflow-hidden bg-brand-light">
        <EntityHeader user={auth?.user} />
        <main className="flex-1 overflow-auto p-6 lg:p-8">
          {children}
        </main>
        <footer className="shrink-0 w-full border-t border-gray-200 bg-white px-6 py-3 text-xs text-gray-400 flex items-center justify-between">
          <span>{t('pages.layout.entity_portal.footer', { year: new Date().getFullYear() })}</span>
          <button
            type="button"
            onClick={toggleShortcuts}
            title="Atalhos de teclado (Ctrl+Shift+H)"
            className="inline-flex items-center gap-1.5 text-gray-400 hover:text-gray-600 transition-colors"
          >
            <Keyboard className="h-3.5 w-3.5" />
            <span className="hidden sm:inline">Atalhos</span>
          </button>
        </footer>
      </div>

      {hasMultipleRoles && (
        <RolePalette
          open={paletteOpen}
          onClose={() => setPaletteOpen(false)}
          onSelect={handleSwitchRole}
          onRequestOpen={() => setPaletteOpen(true)}
          availableRoles={availableRoles}
          activeRole={activeRole}
          userName={userName}
        />
      )}

      <ShortcutsHelp
        open={shortcutsOpen}
        onClose={() => setShortcutsOpen(false)}
        onToggle={toggleShortcuts}
        can={{}}
      />
    </>
  );
}
