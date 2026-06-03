import { useState, useEffect, useRef, useCallback, useMemo } from 'react';
import { User, Search, CornerDownLeft, ArrowUp, ArrowDown } from 'lucide-react';
import { ROLE_META, getRecentRoles, recordRecentRole } from '@/Features/Authentication/roleMeta';

/**
 * Command-palette style role selector.
 *
 * Props:
 *   - open            bool
 *   - onClose         () => void
 *   - onSelect        (roleName: string) => void   — fires immediately, no spinner
 *   - onRequestOpen?  () => void   — called on Ctrl+K when palette is closed
 *   - availableRoles  Array<{name, label}>
 *   - activeRole?     string — highlight current role (in-session switch)
 *   - userName?       string — shown in header
 */
export default function RolePalette({
  open,
  onClose,
  onSelect,
  onRequestOpen,
  availableRoles = [],
  activeRole,
  userName,
}) {
  const [query, setQuery] = useState('');
  const [selectedIndex, setSelectedIndex] = useState(0);
  const inputRef = useRef(null);
  const listRef = useRef(null);
  const backdropRef = useRef(null);

  // Load recent roles fresh every time the palette opens
  const [recentRoles, setRecentRoles] = useState([]);
  useEffect(() => {
    if (open) {
      setRecentRoles(getRecentRoles());
      setQuery('');
      setSelectedIndex(0);
      // Focus input after animation frame
      requestAnimationFrame(() => inputRef.current?.focus());
    }
  }, [open]);

  // ── Filtered results ──────────────────────────────────
  const { recentMatches, allMatches } = useMemo(() => {
    const q = query.toLowerCase().trim();

    // Recent roles that exist in availableRoles
    const recent = availableRoles.filter(
      (r) => recentRoles.includes(r.name) && (!q || r.label.toLowerCase().includes(q))
    );

    // All roles filtered by query, excluding those already in recent
    const recentNames = new Set(recent.map((r) => r.name));
    const all = availableRoles.filter(
      (r) => !recentNames.has(r.name) && (!q || r.label.toLowerCase().includes(q))
    );

    return { recentMatches: recent, allMatches: all };
  }, [query, availableRoles, recentRoles]);

  // Flattened list for keyboard nav
  const flatList = useMemo(
    () => [...recentMatches, ...allMatches],
    [recentMatches, allMatches]
  );

  // Reset selected index when filtered list changes
  useEffect(() => {
    setSelectedIndex(0);
  }, [query]);

  // ── Keyboard handling ─────────────────────────────────
  useEffect(() => {
    if (!open) return;

    const handler = (e) => {
      switch (e.key) {
        case 'ArrowDown':
          e.preventDefault();
          setSelectedIndex((prev) => Math.min(prev + 1, flatList.length - 1));
          break;
        case 'ArrowUp':
          e.preventDefault();
          setSelectedIndex((prev) => Math.max(prev - 1, 0));
          break;
        case 'Enter':
          e.preventDefault();
          if (flatList[selectedIndex]) {
            recordRecentRole(flatList[selectedIndex].name);
            onSelectRef.current(flatList[selectedIndex].name);
            onCloseRef.current();
          }
          break;
        case 'Escape':
          e.preventDefault();
          onCloseRef.current();
          break;
        default:
          break;
      }
    };

    document.addEventListener('keydown', handler);
    return () => document.removeEventListener('keydown', handler);
  }, [open, flatList, selectedIndex]);

  // Scroll selected item into view
  useEffect(() => {
    if (!listRef.current) return;
    const el = listRef.current.querySelector('[data-selected="true"]');
    if (el) {
      el.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
    }
  }, [selectedIndex]);

  // ── Commit selection ──────────────────────────────────
  const onSelectRef = useRef(onSelect);
  onSelectRef.current = onSelect;
  const onCloseRef = useRef(onClose);
  onCloseRef.current = onClose;

  const commit = useCallback(
    (roleName) => {
      recordRecentRole(roleName);
      onSelectRef.current(roleName);
      onCloseRef.current();
    },
    []
  );

  // ── Ctrl+Shift+K global toggle ──
  const onRequestOpenRef = useRef(onRequestOpen);
  onRequestOpenRef.current = onRequestOpen;
  const openRef = useRef(open);
  openRef.current = open;
  useEffect(() => {
    const handler = (e) => {
      if (!e.ctrlKey || !e.shiftKey) return;
      if (e.key !== 'K' && e.code !== 'KeyK') return;
      const tag = e.target?.tagName?.toLowerCase();
      if (tag === 'input' || tag === 'textarea' || tag === 'select' || e.target?.isContentEditable) return;
      e.preventDefault();
      e.stopPropagation();
      if (openRef.current) {
        onCloseRef.current();
      } else {
        onRequestOpenRef.current?.();
      }
    };

    window.addEventListener('keydown', handler);
    return () => window.removeEventListener('keydown', handler);
  }, []);

  // ── Backdrop click ────────────────────────────────────
  const handleBackdropClick = (e) => {
    if (e.target === backdropRef.current) onClose();
  };

  if (!open) return null;

  return (
    <div
      ref={backdropRef}
      onClick={handleBackdropClick}
      className="fixed inset-0 z-[300] flex items-center justify-center bg-black/40 backdrop-blur-sm px-4"
    >
      <div
        className="w-full max-w-lg rounded-2xl border border-white/[0.08] bg-brand-darkest shadow-[0_25px_60px_rgba(0,0,0,0.5)] overflow-hidden"
        role="dialog"
        aria-label="Selecionar função"
      >
        {/* ── Header / Search ──────────────────────────── */}
        <div className="flex items-center gap-3 px-4 py-3 border-b border-white/[0.06]">
          <Search className="h-4 w-4 shrink-0 text-brand-mid/60" />
          <input
            ref={inputRef}
            type="text"
            value={query}
            onChange={(e) => setQuery(e.target.value)}
            placeholder={userName ? `${userName} — pesquisar funções...` : 'Pesquisar funções...'}
            className="flex-1 bg-transparent text-sm text-brand-white placeholder:text-brand-mid/40 outline-none border-0 p-0"
            autoComplete="off"
            spellCheck={false}
          />
          <kbd className="hidden sm:inline-flex items-center gap-0.5 rounded-md border border-white/[0.08] bg-white/[0.03] px-1.5 py-0.5 text-[10px] font-medium text-brand-mid/50">
            Esc
          </kbd>
        </div>

        {/* ── Body ──────────────────────────────────────── */}
        <div ref={listRef} className="max-h-[360px] overflow-y-auto py-2">
          {flatList.length === 0 && (
            <div className="px-4 py-8 text-center text-xs text-brand-mid/50">
              Nenhuma função encontrada
            </div>
          )}

          {/* ── Recent ────────────────────────────────── */}
          {recentMatches.length > 0 && (
            <div className="mb-1">
              <p className="px-4 py-1 text-[10px] font-semibold uppercase tracking-widest text-brand-mid/40">
                Recentes
              </p>
                  {recentMatches.map((role, i) => (
                <RoleItem
                  key={role.name}
                  role={role}
                  index={i}
                  selectedIndex={selectedIndex}
                  isActive={role.name === activeRole}
                  onClick={() => commit(role.name)}
                  onHover={setSelectedIndex}
                />
              ))}
            </div>
          )}

          {/* ── All ───────────────────────────────────── */}
          {allMatches.length > 0 && (
            <div>
              {recentMatches.length > 0 && (
                <p className="px-4 py-1 text-[10px] font-semibold uppercase tracking-widest text-brand-mid/40">
                  Todas as funções
                </p>
              )}
              {allMatches.map((role, i) => {
                const globalIndex = recentMatches.length + i;
                return (
                  <RoleItem
                    key={role.name}
                    role={role}
                    index={globalIndex}
                    selectedIndex={selectedIndex}
                    isActive={role.name === activeRole}
                    onClick={() => commit(role.name)}
                    onHover={setSelectedIndex}
                  />
                );
              })}
            </div>
          )}
        </div>

        {/* ── Footer hints ─────────────────────────────── */}
        <div className="flex items-center gap-4 px-4 py-2 border-t border-white/[0.06] text-[10px] text-brand-mid/40">
          <span className="inline-flex items-center gap-1">
            <ArrowUp className="h-3 w-3" />
            <ArrowDown className="h-3 w-3" />
            Navegar
          </span>
          <span className="inline-flex items-center gap-1">
            <CornerDownLeft className="h-3 w-3" />
            Selecionar
          </span>
        </div>
      </div>
    </div>
  );
}

/* ── Role item row ────────────────────────────────────────── */
function RoleItem({ role, index, selectedIndex, isActive, onClick, onHover }) {
  const meta = ROLE_META[role.name] || { icon: User, hue: '#6b7280' };
  const Icon = meta.icon;
  const isSelected = index === selectedIndex;

  return (
    <button
      type="button"
      data-selected={isSelected}
      onClick={onClick}
      onMouseMove={() => onHover?.(index)}
      className={[
        'flex items-center gap-3 w-full px-4 py-2.5 text-left transition-colors duration-75',
        isSelected
          ? 'bg-brand-accent/15 text-brand-white'
          : isActive
            ? 'text-brand-accent/80'
            : 'text-brand-mid hover:bg-white/[0.03] hover:text-brand-white',
      ].join(' ')}
    >
      <span
        className="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg"
        style={{ backgroundColor: `${meta.hue}20`, color: meta.hue }}
      >
        <Icon className="h-4 w-4" />
      </span>
      <span className="flex-1 text-sm font-medium truncate">{role.label}</span>
      {meta.description && (
        <span className="hidden sm:block text-[11px] text-brand-mid/50 truncate max-w-[180px]">
          {meta.description}
        </span>
      )}
      {isActive && !isSelected && (
        <span className="h-1.5 w-1.5 rounded-full bg-brand-accent shrink-0" />
      )}
    </button>
  );
}
