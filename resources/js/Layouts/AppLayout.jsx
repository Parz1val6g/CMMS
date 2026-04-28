import { Head } from '@inertiajs/react';
import Sidebar from '@/Components/Common/Sidebar';
import Topbar from '@/Components/Common/Topbar';

export default function AppLayout({ title, breadcrumbs = [], children }) {
  return (
    <>
      <Head title={title} />

      <div className="flex h-screen w-screen overflow-hidden bg-gray-100 dark:bg-gray-900">
        {/* Sidebar */}
        <Sidebar />

        {/* Main content area */}
        <div className="flex flex-1 flex-col overflow-hidden">
          {/* Topbar */}
          <Topbar breadcrumbs={breadcrumbs} />

          {/* Page content */}
          <main className="flex-1 overflow-y-auto p-4 md:p-6 lg:p-8">
            {children}
          </main>
        </div>
      </div>
    </>
  );
}
