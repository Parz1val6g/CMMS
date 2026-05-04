import { Head } from '@inertiajs/react';
import Sidebar from '@/Components/SideBar';

export default function AppLayout({ title, children }) {
  return (
    <>
      <Head title={title} />

      <div className="h-screen w-screen flex flex-col overflow-hidden bg-slate-900">
        {/* Top section: Sidebar + Main Canvas */}
        <div className="flex-1 flex flex-row overflow-hidden">
          <Sidebar />

          {/* Main content area */}
          <main className="flex-1 flex flex-col overflow-hidden">
            {children}
          </main>
        </div>

        {/* Global Footer */}
        <footer className="shrink-0 w-full border-t border-slate-800 bg-slate-950 px-6 py-2 text-xs text-slate-500">
          &copy; {new Date().getFullYear()} ERP Gestão — All rights reserved.
        </footer>
      </div>
    </>
  );
}
