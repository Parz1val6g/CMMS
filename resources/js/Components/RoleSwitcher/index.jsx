import { useState, useEffect, useRef, useCallback } from 'react';
import { Link, router } from '@inertiajs/react';
import { t } from '@/utils/i18n';
import { LogOut, Settings, ChevronRight, User, ArrowLeftRight } from 'lucide-react';
import RolePalette from '@/Components/RolePalette';

/**
 * Sidebar user menu: trigger button → flyout with links + "Trocar função" → opens RolePalette.
 *
 * Props:
 *   - availableRoles   Array<{name, label}>
 *   - activeRole       string
 *   - bottomItems      Array<{href, label, icon, can?}>
 *   - can              object
 *   - onLogout         () => void
 *   - collapsed        bool — render compact trigger
 *   - userName         string
 *   - activeRoleLabel  string
 *   - onTooltipShow    (e, label) => void
 *   - onTooltipHide    () => void
 */
export default function RoleSwitcher({
  availableRoles = [],
  activeRole,
  bottomItems = [],
  can = {},
  onLogout,
  collapsed = false,
  userName = '',
  activeRoleLabel = '',
  onTooltipShow,
  onTooltipHide,
}) {
  const [flyoutOpen, setFlyoutOpen] = useState(false);
  const [paletteOpen, setPaletteOpen] = useState(false);

  const triggerRef = useRef(null);
  const flyoutRef = useRef(null);

  const hasMultipleRoles = availableRoles.length > 1;
  const visibleBottomItems = bottomItems.filter(
    (item) => !item.can || can?.[item.can]
  );

  // ── Close flyout on outside click ────────────────────
  useEffect(() => {
    if (!flyoutOpen) return;
    const handler = (e) => {
      if (
        flyoutRef.current && !flyoutRef.current.contains(e.target) &&
        triggerRef.current && !triggerRef.current.contains(e.target)
      ) setFlyoutOpen(false);
    };
    document.addEventListener('mousedown', handler);
    return () => document.removeEventListener('mousedown', handler);
  }, [flyoutOpen]);

  const toggleFlyout = useCallback(() => {
    setFlyoutOpen((prev) => !prev);
    if (collapsed && onTooltipHide) onTooltipHide();
  }, [collapsed, onTooltipHide]);

  const openPalette = useCallback(() => {
    setFlyoutOpen(false);
    setPaletteOpen(true);
  }, []);

  const closePalette = useCallback(() => setPaletteOpen(false), []);

  const handleSwitch = useCallback((roleName) => {
    router.post('/switch-role', { role: roleName });
  }, []);

  const handleLogout = useCallback(() => {
    setFlyoutOpen(false);
    onLogout?.();
  }, [onLogout]);

  return (
    <>
      {/* ── Trigger ─────────────────────────────────────── */}
      <div className="px-2 pb-1">
        {collapsed ? (
          <button
            ref={triggerRef}
            type="button"
            onClick={toggleFlyout}
            onMouseEnter={(e) => onTooltipShow?.(e, userName)}
            onMouseLeave={onTooltipHide}
            className={[
              'flex w-full justify-center items-center rounded-lg px-2 py-2.5 text-sm font-medium transition-colors',
              flyoutOpen
                ? 'bg-white/10 text-brand-white'
                : 'text-brand-mid hover:bg-white/5 hover:text-brand-white',
            ].join(' ')}
          >
            <User className="h-[18px] w-[18px] shrink-0" />
          </button>
        ) : (
          <button
            ref={triggerRef}
            type="button"
            onClick={toggleFlyout}
            className={[
              'group flex w-full items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition-colors',
              flyoutOpen
                ? 'bg-white/10 text-brand-white'
                : 'text-brand-mid hover:bg-white/5 hover:text-brand-white',
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
            <ChevronRight
              className={[
                'h-3.5 w-3.5 shrink-0 transition-all duration-200',
                flyoutOpen
                  ? 'opacity-100 text-brand-white rotate-90'
                  : 'opacity-0 group-hover:opacity-60',
              ].join(' ')}
            />
          </button>
        )}
      </div>

      {/* ── Flyout ──────────────────────────────────────── */}
      {flyoutOpen && (
        <div
          ref={flyoutRef}
          className={[
            'fixed z-[200] w-56 rounded-xl border border-white/[0.08] bg-brand-darkest/95 py-2',
            'shadow-[0_12px_40px_rgba(0,0,0,0.55)] backdrop-blur-md',
          ].join(' ')}
          style={{
            left: triggerRef.current
              ? triggerRef.current.getBoundingClientRect().right + 8
              : 0,
            bottom:
              typeof window !== 'undefined'
                ? window.innerHeight -
                  (triggerRef.current
                    ? triggerRef.current.getBoundingClientRect().top
                    : 0)
                : 0,
          }}
        >
          {/* User header */}
          <div className="px-4 pb-2 mb-1 border-b border-white/[0.06]">
            <p className="text-xs font-semibold text-brand-white truncate">{userName}</p>
            {activeRoleLabel && (
              <p className="text-[10px] text-brand-mid/50 truncate">{activeRoleLabel}</p>
            )}
          </div>

          {/* Bottom nav items */}
          {visibleBottomItems.map((item) => {
            const IconComponent = item.icon;
            return (
              <Link
                key={item.href}
                href={item.href}
                onClick={() => setFlyoutOpen(false)}
                className="flex items-center gap-3 px-4 py-2 text-xs text-brand-mid transition-colors hover:text-brand-white hover:bg-white/5"
              >
                <IconComponent className="h-3.5 w-3.5 shrink-0" />
                <span className="truncate">{item.label}</span>
              </Link>
            );
          })}

          {visibleBottomItems.length > 0 && (
            <div className="my-1 border-t border-white/[0.06]" />
          )}

          {/* Settings link */}
          <Link
            href="/settings"
            onClick={() => setFlyoutOpen(false)}
            className="flex items-center gap-3 px-4 py-2 text-xs text-brand-mid transition-colors hover:text-brand-white hover:bg-white/5"
          >
            <Settings className="h-3.5 w-3.5 shrink-0" />
            <span className="truncate">{t('pages.sidebar.settings')}</span>
          </Link>

          {/* Role switch button — opens palette */}
          {hasMultipleRoles && (
            <>
              <div className="my-1 border-t border-white/[0.06]" />
              <button
                type="button"
                onClick={openPalette}
                className="flex items-center gap-3 w-full px-4 py-2 text-xs text-brand-mid transition-colors hover:text-brand-accent hover:bg-white/5"
              >
                <ArrowLeftRight className="h-3.5 w-3.5 shrink-0" />
                <span className="truncate">{t('pages.select_role.title')}</span>
                <kbd className="ml-auto text-[10px] text-brand-mid/40">Ctrl+K</kbd>
              </button>
            </>
          )}

          {/* Logout */}
          <div className="mt-1 border-t border-white/[0.06]" />
          <button
            type="button"
            onClick={handleLogout}
            className="flex items-center gap-3 w-full px-4 py-2 text-xs text-brand-mid transition-colors hover:text-red-400 hover:bg-white/5"
          >
            <LogOut className="h-3.5 w-3.5 shrink-0" />
            <span className="truncate">{t('pages.sidebar.logout')}</span>
          </button>
        </div>
      )}

      {/* ── Command palette ──────────────────────────────── */}
      <RolePalette
        open={paletteOpen}
        onClose={closePalette}
        onSelect={handleSwitch}
        onRequestOpen={() => { setFlyoutOpen(false); setPaletteOpen(true); }}
        availableRoles={availableRoles}
        activeRole={activeRole}
        userName={userName}
      />
    </>
  );
}
