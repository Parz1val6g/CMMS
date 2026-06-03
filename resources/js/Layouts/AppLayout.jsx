import { useEffect, useState, useCallback } from 'react';
import { Head, usePage, router } from '@inertiajs/react';
import { Keyboard } from 'lucide-react';
import Sidebar from '@/Components/SideBar';
import { useToast } from '@/Components/Toast/ToastContext';
import ShortcutsHelp from '@/Components/ShortcutsHelp';
import { t } from '@/utils/i18n';

function FlashToast() {
  const { flash } = usePage().props;
  const toast = useToast();

  useEffect(() => {
    if (flash?.success) toast.success(flash.success);
    if (flash?.error)   toast.error(flash.error);
  }, [flash]);

  return null;
}

export default function AppLayout({ title, children }) {
  const { can } = usePage().props;
  const [shortcutsOpen, setShortcutsOpen] = useState(false);

  const toggleShortcuts = useCallback(() => setShortcutsOpen((prev) => !prev), []);

  // ── Global shortcuts: Ctrl+Shift+D, Ctrl+Shift+S ──
  useEffect(() => {
    const isEditable = (el) => {
      const tag = el?.tagName?.toLowerCase();
      return tag === 'input' || tag === 'textarea' || tag === 'select' || el?.isContentEditable;
    };

    const handler = (e) => {
      if (isEditable(e.target)) return;
      if (!e.ctrlKey || !e.shiftKey) return;

      // Ctrl+Shift+D → Dashboard
      if ((e.key === 'D' || e.code === 'KeyD') && can?.viewDashboard) {
        e.preventDefault();
        router.get('/dashboard');
        return;
      }
      // Ctrl+Shift+S → Settings
      if ((e.key === 'S' || e.code === 'KeyS') && can?.viewSettings) {
        e.preventDefault();
        router.get('/settings');
        return;
      }
    };

    window.addEventListener('keydown', handler);
    return () => window.removeEventListener('keydown', handler);
  }, [can]);

  return (
    <>
      <Head title={title} />

      <div className="h-screen w-screen flex flex-col overflow-hidden bg-brand-light">
        <FlashToast />

        {/* Top section: Sidebar + Main Canvas */}
        <div className="flex-1 flex flex-row overflow-hidden">
          <Sidebar />

          {/* Main content area */}
          <main className="flex-1 flex flex-col overflow-hidden">
            {children}
          </main>
        </div>

        {/* Global Footer */}
        <footer className="shrink-0 w-full border-t border-brand-mid/20 bg-brand-white px-6 py-2 text-xs text-brand-mid flex items-center justify-between">
          <span>{t('pages.layout.footer_copyright', { year: String(new Date().getFullYear()) })}</span>
          <button
            type="button"
            onClick={toggleShortcuts}
            title="Atalhos de teclado (Ctrl+Shift+H)"
            className="inline-flex items-center gap-1.5 text-brand-mid/60 hover:text-brand-mid transition-colors"
          >
            <Keyboard className="h-3.5 w-3.5" />
            <span className="hidden sm:inline">Atalhos</span>
          </button>
        </footer>
      </div>

      {/* Keyboard shortcuts help (Ctrl+/ toggles it) */}
      <ShortcutsHelp
        open={shortcutsOpen}
        onClose={() => setShortcutsOpen(false)}
        onToggle={toggleShortcuts}
        can={can}
      />
    </>
  );
}
