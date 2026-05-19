import { usePage, Link } from '@inertiajs/react';
import { memo } from 'react';
import { getSections, getBottomItems } from '@/Layouts/data/sidebar';
import { t } from '@/utils/i18n';

// ── Sub-components ──────────────────────────────────────────────────────

const NavItem = memo(function NavItem({ item }) {
  const { url } = usePage();
  const isActive = url === item.href || url.startsWith(item.href + '/');
  const Icon = item.icon;

  return (
    <Link
      href={item.href}
      className={`group flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition-colors
        ${isActive
          ? 'bg-brand-accent text-brand-light shadow-sm'
          : 'text-brand-light hover:bg-brand-darkest/50 hover:text-brand-white'
        }
        ${item.dev ? 'opacity-60 hover:opacity-100' : ''}
      `}
    >
      <Icon className="h-5 w-5 shrink-0" />
      <span className="flex-1 truncate">{item.label}</span>
      {item.dev && (
        <span className="shrink-0 rounded-full bg-brand-accent/20 px-1.5 py-0.5 text-[10px] font-semibold uppercase tracking-wider text-brand-accent ring-1 ring-brand-accent/30">
          {t('pages.sidebar.dev_badge')}
        </span>
      )}
    </Link>
  );
});

function NavSection({ section }) {
  return (
    <div>
      {section.label && (
        <p className="px-3 py-1 text-[11px] font-semibold uppercase tracking-widest text-brand-mid">
          {section.label}
        </p>
      )}
      <nav className="space-y-0.5">
        {section.items.map((item) => (
          <NavItem key={item.href} item={item} />
        ))}
      </nav>
    </div>
  );
}

// ── Main Sidebar ────────────────────────────────────────────────────────

export default function Sidebar() {
  const { auth } = usePage().props;
  const sections = getSections();
  const bottomItems = getBottomItems();

  return (
    <aside className="w-64 flex flex-col h-full bg-brand-darkest border-r border-brand-mid/20 shrink-0">
      {/* Logo */}
      <div className="shrink-0 p-4">
        <span className="text-lg font-bold tracking-tight text-brand-white">{t('pages.sidebar.brand')}</span>
      </div>

      {/* Navigation */}
      <div className="flex-1 overflow-y-auto px-3 space-y-4 py-2">
        {sections.map((section, i) => (
          <NavSection key={section.label ?? `top-${i}`} section={section} />
        ))}
      </div>

      {/* Bottom — Settings, Admin, User */}
      <div className="shrink-0 mt-auto border-t border-brand-mid/20 p-4 space-y-1">
        {bottomItems.map((item) => (
          <NavItem key={item.href} item={item} />
        ))}
        {auth?.user && (
          <Link
            href="/profile"
            className="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-brand-mid hover:bg-brand-darkest/50 hover:text-brand-white transition-colors"
          >
            <div className="flex h-5 w-5 shrink-0 items-center justify-center">
              <svg className="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
                <path strokeLinecap="round" strokeLinejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
              </svg>
            </div>
            <span className="truncate">{auth.user.first_name} {auth.user.last_name}</span>
          </Link>
        )}
      </div>
    </aside>
  );
}
