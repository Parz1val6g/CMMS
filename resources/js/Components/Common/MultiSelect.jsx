import { useState, useRef, useEffect, useMemo, useCallback } from 'react';
import { ChevronDown, X, Search } from 'lucide-react';
import { t } from '@/utils/i18n';

/**
 * Normalize value to an array of primitive IDs.
 * Accepts: [1, 2, 3] or [{id: 1}, {id: 2}] or {id: 1}
 */
function toIds(raw) {
  if (!raw) return [];
  const arr = Array.isArray(raw) ? raw : [raw];
  return arr.map(item => (item && typeof item === 'object' ? item.id : item)).filter(v => v !== undefined && v !== null);
}

export default function MultiSelect({ name, options = [], value = [], onChange, placeholder, showSearch = true, lockedValues = [] }) {
  const [isOpen, setIsOpen] = useState(false);
  const [searchQuery, setSearchQuery] = useState('');
  const [dropdownStyle, setDropdownStyle] = useState({});
  const triggerRef = useRef(null);
  const inputRef = useRef(null);
  const searchRef = useRef(null);

  /* ── Initialize selected from value (no options filter — may not be loaded) ── */
  const [selected, setSelected] = useState(() => toIds(value));

  /* ── Sync selected when value changes ───────────────────── */
  useEffect(() => {
    const ids = toIds(value);
    setSelected(prev => {
      if (JSON.stringify(ids) === JSON.stringify(prev)) return prev;
      return ids;
    });
  }, [value]);

  /* ── Focus search input on open ──────────────────────────── */
  useEffect(() => {
    if (isOpen && showSearch) {
      setSearchQuery('');
      searchRef.current?.focus();
    }
  }, [isOpen, showSearch]);

  /* ── Filtered options ───────────────────────────────────── */
  const filtered = useMemo(() => {
    if (!searchQuery) return options;
    const q = searchQuery.toLowerCase();
    return options.filter(o => o.label.toLowerCase().includes(q));
  }, [options, searchQuery]);

  /* ── Compute fixed position for dropdown ────────────────── */
  const computeDropdownPosition = useCallback(() => {
    if (!triggerRef.current) return {};
    const rect = triggerRef.current.getBoundingClientRect();
    return {
      position: 'fixed',
      top: rect.bottom + 4,
      left: rect.left,
      width: rect.width,
      zIndex: 9999,
    };
  }, []);

  /* ── Open / close ────────────────────────────────────────── */
  const open = useCallback(() => {
    setDropdownStyle(computeDropdownPosition());
    setIsOpen(true);
  }, [computeDropdownPosition]);

  const close = useCallback(() => {
    setIsOpen(false);
  }, []);

  /* ── Recompute position on scroll / resize ──────────────── */
  useEffect(() => {
    if (!isOpen) return;
    const recompute = () => setDropdownStyle(computeDropdownPosition());
    window.addEventListener('scroll', recompute, true);
    window.addEventListener('resize', recompute);
    return () => {
      window.removeEventListener('scroll', recompute, true);
      window.removeEventListener('resize', recompute);
    };
  }, [isOpen, computeDropdownPosition]);

  /* ── Close on outside click ──────────────────────────────── */
  useEffect(() => {
    function handleClick(e) {
      if (triggerRef.current && !triggerRef.current.contains(e.target)) {
        // also exclude dropdown itself
        const dd = document.getElementById(`ms-dropdown-${name}`);
        if (dd && dd.contains(e.target)) return;
        setIsOpen(false);
      }
    }
    document.addEventListener('mousedown', handleClick);
    return () => document.removeEventListener('mousedown', handleClick);
  }, [name]);

  const toggleItem = (itemValue) => {
    // Prevent deselecting locked values
    if (lockedValues.includes(itemValue) && selected.includes(itemValue)) return;
    const next = selected.includes(itemValue)
      ? selected.filter(v => v !== itemValue)
      : [...selected, itemValue];
    setSelected(next);
    onChange?.(next);
    inputRef.current?.focus();
  };

  const removeItem = (e, itemValue) => {
    e.stopPropagation();
    // Prevent removing locked values
    if (lockedValues.includes(itemValue)) return;
    const next = selected.filter(v => v !== itemValue);
    setSelected(next);
    onChange?.(next);
  };

  const selectedLabels = options
    .filter(o => selected.includes(o.value))
    .map(o => ({ label: o.label, value: o.value }));

  return (
    <div className="relative">
      {/* ── Trigger / Tag display ────────────────────────────── */}
      <div
        ref={triggerRef}
        className="flex min-h-[38px] cursor-pointer flex-wrap items-center gap-1 rounded-lg border border-brand-mid/20 bg-brand-white px-3 py-1.5 text-sm text-brand-darkest transition-colors hover:border-brand-mid"
        onClick={() => { if (isOpen) close(); else open(); }}
        role="combobox"
        aria-expanded={isOpen}
        aria-haspopup="listbox"
        tabIndex={0}
        onKeyDown={(e) => { if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); if (isOpen) close(); else open(); } }}
      >
        {selectedLabels.length === 0 ? (
          <span className="text-brand-mid">{placeholder || t('common.multi_select.placeholder')}</span>
        ) : (
          selectedLabels.map(({ label, value }) => {
            const isLocked = lockedValues.includes(value);
            return (
              <span
                key={value}
                className={`inline-flex items-center gap-1 rounded-md px-2 py-0.5 text-xs font-medium ${
                  isLocked
                    ? 'bg-brand-mid/10 text-brand-mid opacity-60 cursor-not-allowed'
                    : 'bg-brand-accent/10 text-brand-accent'
                }`}
              >
                {label}
                {!isLocked && (
                  <button
                    type="button"
                    onClick={(e) => removeItem(e, value)}
                    className="inline-flex rounded-sm p-0.5 text-brand-accent hover:bg-brand-accent/10 hover:text-brand-accent/80 transition-colors"
                    aria-label={t('common.multi_select.remove_aria', { label })}
                  >
                    <X className="h-3 w-3" />
                  </button>
                )}
              </span>
            );
          })
        )}
        <ChevronDown className={`ml-auto h-4 w-4 text-brand-mid transition-transform ${isOpen ? 'rotate-180' : ''}`} />
      </div>

      {/* ── Dropdown (fixed positioning to avoid overflow clip) ── */}
      {isOpen && (
        <div
          id={`ms-dropdown-${name}`}
          style={dropdownStyle}
          className="max-h-60 overflow-auto rounded-lg border border-brand-mid/20 bg-brand-white shadow-2xl"
        >
          {/* ── Sticky search bar ──────────────────────────────── */}
          {showSearch && (
            <div className="sticky top-0 z-10 border-b border-brand-mid/20 bg-brand-white p-2">
              <div className="relative">
                <Search className="absolute left-2.5 top-1/2 h-4 w-4 -translate-y-1/2 text-brand-mid" />
                <input
                  ref={searchRef}
                  type="text"
                  value={searchQuery}
                  onChange={e => setSearchQuery(e.target.value)}
                  placeholder={t('pages.common.search_placeholder')}
                  className="w-full rounded-md border border-brand-mid/20 bg-brand-light py-1.5 pl-8 pr-3 text-sm text-brand-darkest placeholder:text-brand-mid focus:border-brand-accent focus:outline-none focus:ring-1 focus:ring-brand-accent"
                />
              </div>
            </div>
          )}

          {filtered.length === 0 && options.length > 0 ? (
            <div className="px-3 py-2 text-sm text-brand-mid">{t('pages.common.no_matches')}</div>
          ) : filtered.length === 0 && options.length === 0 ? (
            <div className="px-3 py-2 text-sm text-brand-mid">{t('common.multi_select.no_options')}</div>
          ) : (
            filtered.map((opt) => {
              const isChecked = selected.includes(opt.value);
              return (
                <div
                  key={opt.value}
                  className={`flex cursor-pointer items-center gap-2 px-3 py-2 text-sm transition-colors ${
                    isChecked
                      ? 'bg-brand-accent/10 text-brand-accent'
                      : 'text-brand-darkest hover:bg-brand-light'
                  }`}
                  onClick={() => toggleItem(opt.value)}
                  role="option"
                  aria-selected={isChecked}
                >
                  <input
                    type="checkbox"
                    checked={isChecked}
                    readOnly
                    className="h-4 w-4 rounded border-brand-mid/20 bg-brand-white text-brand-accent focus:ring-brand-accent"
                  />
                  <span className="flex-1">{opt.label}</span>
                  {isChecked && (
                    <span className="text-xs text-brand-accent">✓</span>
                  )}
                </div>
              );
            })
          )}
        </div>
      )}

      {/* Hidden ref for programmatic focus */}
      <input ref={inputRef} type="hidden" />
    </div>
  );
}
