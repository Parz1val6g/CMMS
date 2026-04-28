import { Link, usePage } from '@inertiajs/react';
import { useMemo } from 'react';

export default function SidebarOption({ item, collapsed }) {
  const { url } = usePage();

  const isActive = useMemo(() => {
    const path = item.href === '/dashboard' ? '/' : item.href;
    return url === path || url.startsWith(path + '/');
  }, [url, item.href]);

  return (
    <Link
      href={item.href}
      className={`group flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition-colors ${
        collapsed ? 'justify-center px-2' : ''
      } ${
        isActive
          ? 'bg-blue-600 text-white shadow-sm'
          : 'text-gray-300 hover:bg-gray-700 hover:text-white'
      }`}
      title={collapsed ? item.label : undefined}
    >
      <svg className="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
        <path strokeLinecap="round" strokeLinejoin="round" d={item.icon} />
      </svg>
      {!collapsed && <span className="truncate">{item.label}</span>}
    </Link>
  );
}
