import { useState, useCallback, useMemo, useLayoutEffect, useRef, useEffect, memo } from 'react';
import { usePage, Link, router } from '@inertiajs/react';
import { LogOut, ChevronDown, ChevronLeft, ChevronRight, Check, User, Settings } from 'lucide-react';
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
   BOTTOM NAV ITEM — side flyout via fixed positioning so it
   escapes sidebar overflow clipping in both expanded/collapsed
   ════════════════════════════════════════════════════════════════ */
function BottomUserRow({ auth, isCollapsed, onTooltipShow, onTooltipHide, onLogout, bottomItems, can }) {
  const { props: { availableRoles, activeRole } } = usePage();

  const visibleBottomItems = bottomItems.filter((item) => !item.can || can?.[item.can]);

  // null = closed; { x, y } = open, viewport-relative anchor for fixed flyout
  const [flyoutPos, setFlyoutPos] = useState(null);
  const menuOpen = flyoutPos !== null;

  const triggerRef = useRef(null);
  const flyoutRef  = useRef(null);

  // Close on outside click — must check both trigger and flyout
  useEffect(() => {
    if (!menuOpen) return;
    const handler = (e) => {
      if (
        flyoutRef.current  && !flyoutRef.current.contains(e.target) &&
        triggerRef.current && !triggerRef.current.contains(e.target)
      ) setFlyoutPos(null);
    };
    document.addEventListener('mousedown', handler);
    return () => document.removeEventListener('mousedown', handler);
  }, [menuOpen]);

  if (!auth?.user) return null;
  const userName = `${auth.user.first_name} ${auth.user.last_name}`;

  const hasMultipleRoles = availableRoles && availableRoles.length > 1;
  const activeRoleLabel = availableRoles?.find((r) => r.name === activeRole)?.label
    ?? availableRoles?.[0]?.label;

  const handleSwitch = (roleName) => {
    setFlyoutPos(null);
    router.post('/switch-role', { role: roleName });
  };

  const toggleMenu = () => {
    if (menuOpen) { setFlyoutPos(null); return; }
    const rect = triggerRef.current.getBoundingClientRect();
    // Anchor to right edge + bottom of trigger; flyout grows upward from this point
    setFlyoutPos({ x: rect.right, y: rect.bottom });
    if (isCollapsed) onTooltipHide();
  };

  const flyout = menuOpen && (
    <div
      ref={flyoutRef}
      style={{ position: 'fixed', left: flyoutPos.x + 8, top: flyoutPos.y, zIndex: 200, transform: 'translateY(-100%)' }}
      className="w-52 rounded-lg border border-white/[0.07] bg-brand-darkest/95 shadow-[0_8px_32px_rgba(0,0,0,0.5)] backdrop-blur-sm py-1.5"
    >
      {/* Role-gated items (Admin, Notifications, etc.) */}
      {visibleBottomItems.map((item) => {
        const Icon = item.icon;
        return (
          <Link
            key={item.href}
            href={item.href}
            onClick={() => setFlyoutPos(null)}
            className="flex w-full items-center gap-3 px-4 py-2.5 text-xs text-brand-mid transition-colors hover:text-brand-white hover:bg-white/5"
          >
            <Icon className="h-3.5 w-3.5 shrink-0" />
            <span>{item.label}</span>
          </Link>
        );
      })}

      {visibleBottomItems.length > 0 && <div className="my-1 border-t border-white/[0.06]" />}

      <Link
        href="/settings"
        onClick={() => setFlyoutPos(null)}
        className="flex w-full items-center gap-3 px-4 py-2.5 text-xs text-brand-mid transition-colors hover:text-brand-white hover:bg-white/5"
      >
        <Settings className="h-3.5 w-3.5 shrink-0" />
        <span>{t('pages.sidebar.settings')}</span>
      </Link>

      {hasMultipleRoles && (
        <>
          <div className="my-1 border-t border-white/[0.06]" />
          {availableRoles.map((role) => (
            <button
              key={role.name}
              type="button"
              onClick={() => handleSwitch(role.name)}
              className={[
                'flex w-full items-center pr-4 pl-[42px] py-2.5 text-left text-xs transition-colors',
                role.name === activeRole
                  ? 'bg-white/5 text-brand-white'
                  : 'text-brand-mid hover:text-brand-white hover:bg-white/5',
              ].join(' ')}
            >
              <span className="flex-1 truncate">{role.label}</span>
              {role.name === activeRole && (
                <Check className="h-3.5 w-3.5 shrink-0 text-brand-accent" strokeWidth={2.5} />
              )}
            </button>
          ))}
        </>
      )}

      <div className="my-1 border-t border-white/[0.06]" />
      <button
        type="button"
        onClick={() => { setFlyoutPos(null); onLogout(); }}
        className="flex w-full items-center gap-3 px-4 py-2.5 text-xs text-brand-mid transition-colors hover:text-red-400 hover:bg-white/5"
      >
        <LogOut className="h-3.5 w-3.5 shrink-0" />
        <span>{t('pages.sidebar.logout')}</span>
      </button>
    </div>
  );

  return (
    <>
      <div className="px-2 pb-1">
        {isCollapsed ? (
          /* ── Collapsed: user icon is the sole trigger ── */
          <button
            ref={triggerRef}
            type="button"
            onClick={toggleMenu}
            onMouseEnter={(e) => onTooltipShow(e, userName)}
            onMouseLeave={onTooltipHide}
            className={[
              'flex w-full justify-center items-center rounded-lg px-2 py-2.5 text-sm font-medium transition-colors',
              menuOpen ? 'bg-white/10 text-brand-white' : 'text-brand-mid hover:bg-white/5 hover:text-brand-white',
            ].join(' ')}
          >
            <User className="h-[18px] w-[18px] shrink-0" />
          </button>
        ) : (
          /* ── Expanded: entire block is the trigger ── */
          <button
            ref={triggerRef}
            type="button"
            onClick={toggleMenu}
            className={[
              'group flex w-full items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition-colors',
              menuOpen ? 'bg-white/10 text-brand-white' : 'text-brand-mid hover:bg-white/5 hover:text-brand-white',
            ].join(' ')}
          >
            <User className="h-[18px] w-[18px] shrink-0" />
            <span className="flex-1 min-w-0 text-left">
              <span className="block truncate">{userName}</span>
              {activeRoleLabel && (
                <span className="block text-[11px] font-normal text-brand-accent/60 truncate">
                  {activeRoleLabel}
                </span>
              )}
            </span>
            <ChevronRight className={[
              'h-3.5 w-3.5 shrink-0 transition-opacity duration-200',
              menuOpen ? 'opacity-100 text-brand-white' : 'opacity-0 group-hover:opacity-60',
            ].join(' ')} />
          </button>
        )}
      </div>

      {/* Fixed flyout — rendered outside sidebar stacking context */}
      {flyout}
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
        <div className="shrink-0 mt-auto border-t border-brand-mid/20 py-3 px-2">
          <BottomUserRow
            auth={auth}
            isCollapsed={isCollapsed}
            onTooltipShow={showTooltip}
            onTooltipHide={hideTooltip}
            onLogout={handleLogout}
            bottomItems={bottomItems}
            can={can}
          />
        </div>
      </aside>

      {/* Tooltip rendered outside aside so it's never clipped */}
      <CollapsedTooltip tooltip={tooltip} />
    </>
  );
}
