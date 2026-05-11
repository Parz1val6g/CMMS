import { useState, useRef, useEffect, useMemo, useCallback } from 'react';
import { ChevronDown, X, Search } from 'lucide-react';

/**
 * Normalize value to an array of primitive IDs.
 * Accepts: [1, 2, 3] or [{id: 1}, {id: 2}] or {id: 1}
 */
function toIds(raw) {
  if (!raw) return [];
  const arr = Array.isArray(raw) ? raw : [raw];
  return arr.map(item => (item && typeof item === 'object' ? item.id : item)).filter(v => v !== undefined && v !== null);
}

export default function MultiSelect({ name, options = [], value = [], onChange, placeholder = 'Select...', showSearch = true }) {
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
    const next = selected.includes(itemValue)
      ? selected.filter(v => v !== itemValue)
      : [...selected, itemValue];
    setSelected(next);
    onChange?.(next);
    inputRef.current?.focus();
  };

  const removeItem = (e, itemValue) => {
    e.stopPropagation();
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
        className="flex min-h-[38px] cursor-pointer flex-wrap items-center gap-1 rounded-lg border border-slate-700 bg-slate-800/60 px-3 py-1.5 text-sm text-slate-200 transition-colors hover:border-slate-600"
        onClick={() => { if (isOpen) close(); else open(); }}
        role="combobox"
        aria-expanded={isOpen}
        aria-haspopup="listbox"
        tabIndex={0}
        onKeyDown={(e) => { if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); if (isOpen) close(); else open(); } }}
      >
        {selectedLabels.length === 0 ? (
          <span className="text-slate-500">{placeholder}</span>
        ) : (
          selectedLabels.map(({ label, value }) => (
            <span
              key={value}
              className="inline-flex items-center gap-1 rounded-md bg-indigo-500/20 px-2 py-0.5 text-xs font-medium text-indigo-300"
            >
              {label}
              <button
                type="button"
                onClick={(e) => removeItem(e, value)}
                className="inline-flex rounded-sm p-0.5 text-indigo-400 hover:bg-indigo-500/30 hover:text-indigo-200 transition-colors"
                aria-label={`Remove ${label}`}
              >
                <X className="h-3 w-3" />
              </button>
            </span>
          ))
        )}
        <ChevronDown className={`ml-auto h-4 w-4 text-slate-500 transition-transform ${isOpen ? 'rotate-180' : ''}`} />
      </div>

      {/* ── Dropdown (fixed positioning to avoid overflow clip) ── */}
      {isOpen && (
        <div
          id={`ms-dropdown-${name}`}
          style={dropdownStyle}
          className="max-h-60 overflow-auto rounded-lg border border-slate-600 bg-slate-800 shadow-2xl"
        >
          {/* ── Sticky search bar ──────────────────────────────── */}
          {showSearch && (
            <div className="sticky top-0 z-10 border-b border-slate-600 bg-slate-800 p-2">
              <div className="relative">
                <Search className="absolute left-2.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-500" />
                <input
                  ref={searchRef}
                  type="text"
                  value={searchQuery}
                  onChange={e => setSearchQuery(e.target.value)}
                  placeholder="Search..."
                  className="w-full rounded-md border border-slate-600 bg-slate-700/60 py-1.5 pl-8 pr-3 text-sm text-slate-200 placeholder:text-slate-500 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                />
              </div>
            </div>
          )}

          {filtered.length === 0 && options.length > 0 ? (
            <div className="px-3 py-2 text-sm text-slate-500">No matches</div>
          ) : filtered.length === 0 && options.length === 0 ? (
            <div className="px-3 py-2 text-sm text-slate-500">No options available</div>
          ) : (
            filtered.map((opt) => {
              const isChecked = selected.includes(opt.value);
              return (
                <div
                  key={opt.value}
                  className={`flex cursor-pointer items-center gap-2 px-3 py-2 text-sm transition-colors ${
                    isChecked
                      ? 'bg-indigo-500/10 text-indigo-300'
                      : 'text-slate-300 hover:bg-slate-700/50'
                  }`}
                  onClick={() => toggleItem(opt.value)}
                  role="option"
                  aria-selected={isChecked}
                >
                  <input
                    type="checkbox"
                    checked={isChecked}
                    readOnly
                    className="h-4 w-4 rounded border-slate-600 bg-slate-700 text-indigo-500 focus:ring-indigo-500"
                  />
                  <span className="flex-1">{opt.label}</span>
                  {isChecked && (
                    <span className="text-xs text-indigo-400">✓</span>
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
