import { Head } from '@inertiajs/react';
import Sidebar from '@/Components/SideBar';
import { t } from '@/utils/i18n';

export default function AppLayout({ title, children }) {
  return (
    <>
      <Head title={title} />

      <div className="h-screen w-screen flex flex-col overflow-hidden bg-brand-light">
        {/* Top section: Sidebar + Main Canvas */}
        <div className="flex-1 flex flex-row overflow-hidden">
          <Sidebar />

          {/* Main content area */}
          <main className="flex-1 flex flex-col overflow-hidden">
            {children}
          </main>
        </div>

        {/* Global Footer */}
        <footer className="shrink-0 w-full border-t border-brand-mid/20 bg-brand-white px-6 py-2 text-xs text-brand-mid">
          {t('pages.layout.footer_copyright', { year: String(new Date().getFullYear()) })}
        </footer>
      </div>
    </>
  );
}
