import { useState, useCallback, useMemo, useLayoutEffect, useRef, memo } from 'react';
import { usePage, Link, router } from '@inertiajs/react';
import { LogOut, ChevronDown, ChevronLeft, ChevronRight } from 'lucide-react';
import { getSections, getBottomItems } from '@/Layouts/data/sidebar';
import { t } from '@/utils/i18n';

/* ════════════════════════════════════════════════════════════════
   TOOLTIP — fixed position so escapes overflow-y-auto clipping
   ════════════════════════════════════════════════════════════════ */
function CollapsedTooltip({ tooltip }) {
  if (!tooltip) return null;
  return (
    <div
      className="fixed z-[200] pointer-events-none"
      style={{ top: tooltip.y, left: tooltip.x, transform: 'translateY(-50%)' }}
    >
      <div className="ml-3 rounded-md border border-brand-mid/30 bg-brand-darkest px-2.5 py-1.5 text-xs font-medium text-brand-white shadow-xl whitespace-nowrap">
        {tooltip.label}
        {/* Arrow */}
        <span className="absolute top-1/2 -left-1.5 -translate-y-1/2 border-4 border-transparent border-r-brand-darkest" />
      </div>
    </div>
  );
}

/* ════════════════════════════════════════════════════════════════
   NAV ITEM
   ════════════════════════════════════════════════════════════════ */
const NavItem = memo(function NavItem({ item, isCollapsed, onTooltipShow, onTooltipHide }) {
  const { url } = usePage();
  const isActive = url === item.href || url.startsWith(item.href + '/');
  const Icon = item.icon;

  return (
    <Link
      href={item.href}
      onMouseEnter={isCollapsed ? (e) => onTooltipShow(e, item.label) : undefined}
      onMouseLeave={isCollapsed ? onTooltipHide : undefined}
      className={[
        'group flex items-center rounded-lg text-sm font-medium transition-colors',
        isCollapsed ? 'justify-center px-2 py-2.5' : 'gap-3 px-3 py-2',
        isActive
          ? 'bg-brand-accent text-brand-light shadow-sm'
          : 'text-brand-light hover:bg-white/5 hover:text-brand-white',
        item.dev ? 'opacity-60 hover:opacity-100' : '',
      ].join(' ')}
    >
      <Icon className="h-[18px] w-[18px] shrink-0" />
      {!isCollapsed && (
        <>
          <span className="flex-1 truncate">{item.label}</span>
          {item.dev && (
            <span className="shrink-0 rounded-full bg-brand-accent/20 px-1.5 py-0.5 text-[10px] font-semibold uppercase tracking-wider text-brand-accent ring-1 ring-brand-accent/30">
              {t('pages.sidebar.dev_badge')}
            </span>
          )}
        </>
      )}
    </Link>
  );
});

/* ════════════════════════════════════════════════════════════════
   NAV SECTION — accordion when expanded, icon strip when collapsed
   ════════════════════════════════════════════════════════════════ */
function NavSection({ section, isCollapsed, isOpen, hasActiveChild, onToggle, onTooltipShow, onTooltipHide }) {
  const hasLabel = Boolean(section.label);

  // Show the indicator dot when section is closed (active item hidden) OR always when active
  const showDot = hasActiveChild;
  // Emphasise header text only when closed so the user knows where they are
  const emphasiseHeader = hasActiveChild && !isOpen;

  return (
    <div>
      {/* Accordion header — only when expanded and section has a label */}
      {hasLabel && !isCollapsed && (
        <button
          type="button"
          onClick={() => onToggle(section.label)}
          className="group flex w-full items-center justify-between rounded-lg px-3 py-1.5 transition-colors hover:bg-white/5"
        >
          <span
            className={[
              'text-[10.5px] font-semibold uppercase tracking-widest transition-colors',
              emphasiseHeader
                ? 'text-brand-accent'
                : 'text-brand-mid group-hover:text-brand-white/60',
            ].join(' ')}
          >
            {section.label}
          </span>

          {/* Right side: dot + chevron */}
          <span className="flex items-center gap-1.5">
            {showDot && (
              <span
                className={[
                  'h-1.5 w-1.5 rounded-full bg-brand-accent transition-opacity duration-200',
                  isOpen ? 'opacity-50' : 'opacity-100',
                ].join(' ')}
              />
            )}
            <ChevronDown
              className={[
                'h-3.5 w-3.5 transition-transform duration-200',
                emphasiseHeader ? 'text-brand-accent' : 'text-brand-mid',
                isOpen ? 'rotate-180' : '',
              ].join(' ')}
            />
          </span>
        </button>
      )}

      {/* Divider when collapsed — accent tint when section has active child */}
      {hasLabel && isCollapsed && (
        <div
          className={[
            'mx-3 my-1.5 border-t transition-colors duration-300',
            hasActiveChild ? 'border-brand-accent/50' : 'border-brand-mid/20',
          ].join(' ')}
        />
      )}

      {/* Items container — grid animation for smooth expand/collapse */}
      <div
        className={[
          'grid transition-all duration-200 ease-in-out',
          isCollapsed || !hasLabel || isOpen ? 'grid-rows-[1fr]' : 'grid-rows-[0fr]',
        ].join(' ')}
      >
        <div className="overflow-hidden">
          <nav
            className={[
              'space-y-0.5 py-0.5',
              hasLabel && !isCollapsed
                ? 'ml-3 pl-2 border-l transition-colors duration-300 ' +
                  (hasActiveChild ? 'border-brand-accent/40' : 'border-brand-mid/20')
                : '',
            ].join(' ')}
          >
            {section.items.map((item) => (
              <NavItem
                key={item.href}
                item={item}
                isCollapsed={isCollapsed}
                onTooltipShow={onTooltipShow}
                onTooltipHide={onTooltipHide}
              />
            ))}
          </nav>
        </div>
      </div>
    </div>
  );
}

/* ════════════════════════════════════════════════════════════════
   BOTTOM NAV ITEM (profile + logout — same pattern as NavItem)
   ════════════════════════════════════════════════════════════════ */
function BottomUserRow({ auth, isCollapsed, onTooltipShow, onTooltipHide, onLogout }) {
  if (!auth?.user) return null;
  const userName = `${auth.user.first_name} ${auth.user.last_name}`;

  return (
    <>
      <Link
        href="/profile"
        onMouseEnter={isCollapsed ? (e) => onTooltipShow(e, userName) : undefined}
        onMouseLeave={isCollapsed ? onTooltipHide : undefined}
        className={[
          'flex items-center rounded-lg text-sm font-medium text-brand-mid transition-colors hover:bg-white/5 hover:text-brand-white',
          isCollapsed ? 'justify-center px-2 py-2.5' : 'gap-3 px-3 py-2',
        ].join(' ')}
      >
        {/* User avatar icon */}
        <svg className="h-[18px] w-[18px] shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
          <path strokeLinecap="round" strokeLinejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
        </svg>
        {!isCollapsed && <span className="truncate">{userName}</span>}
      </Link>

      <button
        type="button"
        onClick={onLogout}
        onMouseEnter={isCollapsed ? (e) => onTooltipShow(e, t('pages.sidebar.logout')) : undefined}
        onMouseLeave={isCollapsed ? onTooltipHide : undefined}
        className={[
          'flex w-full items-center rounded-lg text-sm font-medium text-brand-mid transition-colors hover:bg-white/5 hover:text-brand-white',
          isCollapsed ? 'justify-center px-2 py-2.5' : 'gap-3 px-3 py-2',
        ].join(' ')}
      >
        <LogOut className="h-[18px] w-[18px] shrink-0" />
        {!isCollapsed && <span>{t('pages.sidebar.logout')}</span>}
      </button>
    </>
  );
}

/* ════════════════════════════════════════════════════════════════
   MAIN SIDEBAR
   ════════════════════════════════════════════════════════════════ */
export default function Sidebar() {
  const { props: { auth, can }, url } = usePage();
  const sections    = getSections();
  const bottomItems = getBottomItems();

  const visibleSections = useMemo(() => {
    return sections.map((section) => ({
      ...section,
      items: section.items.filter((item) => !item.can || can?.[item.can]),
    })).filter((section) => section.items.length > 0);
  }, [sections, can]);

  const visibleBottomItems = useMemo(() => {
    return bottomItems.filter((item) => !item.can || can?.[item.can]);
  }, [bottomItems, can]);

  /* ── Collapsed state — persisted across navigations ── */
  const [isCollapsed, setIsCollapsed] = useState(
    () => localStorage.getItem('sidebar-collapsed') === 'true'
  );

  /* ── Accordion state — persisted in localStorage, user-controlled only ── */
  const [openSections, setOpenSections] = useState(() => {
    try {
      const stored = localStorage.getItem('sidebar-open-sections');
      if (stored !== null) return new Set(JSON.parse(stored));
    } catch {}
    // First visit: all sections open
    return new Set(sections.map((s) => s.label).filter(Boolean));
  });

  /**
   * Reactive active-child map — recomputed on every Inertia navigation
   * because `url` comes from usePage() which re-renders on page changes.
   * Key: section.label  Value: boolean
   */
  const activeChildMap = useMemo(() => {
    const map = {};
    for (const section of sections) {
      if (!section.label) continue;
      map[section.label] = section.items.some(
        (item) => url === item.href || url.startsWith(item.href + '/')
      );
    }
    return map;
  }, [url, sections]);

  /* ── Tooltip state for collapsed mode ── */
  const [tooltip, setTooltip] = useState(null);

  /* ── Scroll position persistence ── */
  const navRef = useRef(null);

  // Restore before paint — useLayoutEffect runs synchronously after DOM mutations
  // but before the browser draws, so the user never sees position 0.
  useLayoutEffect(() => {
    const el = navRef.current;
    if (!el) return;
    const saved = sessionStorage.getItem('sidebar-scroll');
    if (saved !== null) el.scrollTop = parseInt(saved, 10);
  }, []); // empty deps: runs once on mount, which is once per Inertia navigation

  // Persist on scroll — rAF-throttled so it never blocks the main thread.
  const saveScroll = useCallback(() => {
    const el = navRef.current;
    if (!el) return;
    sessionStorage.setItem('sidebar-scroll', String(el.scrollTop));
  }, []);

  /* ── Handlers ── */
  const toggleCollapse = useCallback(() => {
    setIsCollapsed((prev) => {
      const next = !prev;
      localStorage.setItem('sidebar-collapsed', String(next));
      if (next) setTooltip(null);
      return next;
    });
  }, []);

  const toggleSection = useCallback((label) => {
    setOpenSections((prev) => {
      const next = new Set(prev);
      next.has(label) ? next.delete(label) : next.add(label);
      try { localStorage.setItem('sidebar-open-sections', JSON.stringify([...next])); } catch {}
      return next;
    });
  }, []);

  const showTooltip = useCallback((e, label) => {
    const rect = e.currentTarget.getBoundingClientRect();
    setTooltip({ label, x: rect.right, y: rect.top + rect.height / 2 });
  }, []);

  const hideTooltip = useCallback(() => setTooltip(null), []);

  const handleLogout = useCallback(() => router.post('/logout'), []);

  return (
    <>
      <aside
        className={[
          'flex flex-col h-full bg-brand-darkest border-r border-brand-mid/20 shrink-0',
          'transition-all duration-300 ease-in-out',
          isCollapsed ? 'w-16' : 'w-64',
        ].join(' ')}
      >
        {/* ── Logo + collapse toggle ───────────────────────────── */}
        <div
          className={[
            'shrink-0 flex items-center border-b border-brand-mid/20 h-14',
            isCollapsed ? 'justify-center px-3' : 'justify-between px-4',
          ].join(' ')}
        >
          {!isCollapsed && (
            <span className="text-base font-bold tracking-tight text-brand-white truncate">
              {t('pages.sidebar.brand')}
            </span>
          )}
          <button
            type="button"
            onClick={toggleCollapse}
            title={isCollapsed ? t('pages.sidebar.expand') : t('pages.sidebar.collapse')}
            className="flex h-7 w-7 items-center justify-center rounded-md text-brand-mid transition-colors hover:bg-white/5 hover:text-brand-white shrink-0"
          >
            {isCollapsed
              ? <ChevronRight className="h-4 w-4" />
              : <ChevronLeft className="h-4 w-4" />
            }
          </button>
        </div>

        {/* ── Scrollable navigation ───────────────────────────── */}
        <div ref={navRef} onScroll={saveScroll} className="flex-1 overflow-y-auto space-y-1 px-2 py-3">
          {visibleSections.map((section, i) => (
            <NavSection
              key={section.label ?? `s-${i}`}
              section={section}
              isCollapsed={isCollapsed}
              isOpen={!section.label || openSections.has(section.label)}
              hasActiveChild={section.label ? (activeChildMap[section.label] ?? false) : false}
              onToggle={toggleSection}
              onTooltipShow={showTooltip}
              onTooltipHide={hideTooltip}
            />
          ))}
        </div>

        {/* ── Sticky footer ───────────────────────────────────── */}
        <div
          className={[
            'shrink-0 mt-auto border-t border-brand-mid/20 space-y-0.5 py-3',
            isCollapsed ? 'px-2' : 'px-2',
          ].join(' ')}
        >
          {visibleBottomItems.map((item) => (
            <NavItem
              key={item.href}
              item={item}
              isCollapsed={isCollapsed}
              onTooltipShow={showTooltip}
              onTooltipHide={hideTooltip}
            />
          ))}
          <BottomUserRow
            auth={auth}
            isCollapsed={isCollapsed}
            onTooltipShow={showTooltip}
            onTooltipHide={hideTooltip}
            onLogout={handleLogout}
          />
        </div>
      </aside>

      {/* Tooltip rendered outside aside so it's never clipped */}
      <CollapsedTooltip tooltip={tooltip} />
    </>
  );
}
