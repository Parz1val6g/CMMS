import { useState, useRef, useEffect, useMemo, useCallback } from 'react';
import { ChevronDown, Search } from 'lucide-react';
import { toScalar } from '@/Utils/url';

export default function SearchableSelect({ name, options = [], value = '', onChange, placeholder = 'Select...' }) {
  /* ── Normalize incoming value (may be object from Laravel relation) ── */
  const scalarValue = toScalar(value);

  const [isOpen, setIsOpen] = useState(false);
  const [query, setQuery] = useState('');
  const [dropdownStyle, setDropdownStyle] = useState({});
  const [displayLabel, setDisplayLabel] = useState('');
  const triggerRef = useRef(null);
  const inputRef = useRef(null);

  /* ── Sync display label when value/options change ──────── */
  useEffect(() => {
    const label = options.find(o => o.value === scalarValue)?.label ?? '';
    setDisplayLabel(label);
  }, [scalarValue, options]);

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

  const open = useCallback(() => {
    setDropdownStyle(computeDropdownPosition());
    setIsOpen(true);
  }, [computeDropdownPosition]);

  const close = useCallback(() => {
    setIsOpen(false);
  }, []);

  /* ── Recompute on scroll / resize ───────────────────────── */
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
        const dd = document.getElementById(`ss-dropdown-${name}`);
        if (dd && dd.contains(e.target)) return;
        setIsOpen(false);
      }
    }
    document.addEventListener('mousedown', handleClick);
    return () => document.removeEventListener('mousedown', handleClick);
  }, [name]);

  /* ── Focus search input on open ──────────────────────────── */
  useEffect(() => {
    if (isOpen) {
      setQuery('');
      inputRef.current?.focus();
    }
  }, [isOpen]);

  /* ── Filtered options ───────────────────────────────────── */
  const filtered = useMemo(() => {
    if (!query) return options;
    const q = query.toLowerCase();
    return options.filter(o => o.label.toLowerCase().includes(q));
  }, [options, query]);

  const selectItem = (opt) => {
    onChange?.(opt.value);
    setIsOpen(false);
  };

  return (
    <div className="relative">
      {/* ── Trigger ──────────────────────────────────────────── */}
      <div
        ref={triggerRef}
        className="flex min-h-[38px] cursor-pointer items-center gap-2 rounded-lg border border-slate-700 bg-slate-800/60 px-3 py-2 text-sm text-slate-200 transition-colors hover:border-slate-600"
        onClick={() => { if (isOpen) close(); else open(); }}
        role="combobox"
        aria-expanded={isOpen}
        aria-haspopup="listbox"
        tabIndex={0}
        onKeyDown={(e) => { if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); if (isOpen) close(); else open(); } }}
      >
        <span className={`flex-1 ${scalarValue !== '' ? '' : 'text-slate-500'}`}>
          {scalarValue !== '' ? displayLabel : placeholder}
        </span>
        <ChevronDown className={`h-4 w-4 text-slate-500 transition-transform ${isOpen ? 'rotate-180' : ''}`} />
      </div>

      {/* ── Dropdown (fixed positioning to avoid overflow clip) ── */}
      {isOpen && (
        <div
          id={`ss-dropdown-${name}`}
          style={dropdownStyle}
          className="rounded-lg border border-slate-600 bg-slate-800 shadow-2xl"
        >
          {/* Sticky search */}
          <div className="sticky top-0 border-b border-slate-600 bg-slate-800 p-2">
            <div className="relative">
              <Search className="absolute left-2.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-500" />
              <input
                ref={inputRef}
                type="text"
                value={query}
                onChange={e => setQuery(e.target.value)}
                placeholder="Search..."
                className="w-full rounded-md border border-slate-600 bg-slate-700/60 py-1.5 pl-8 pr-3 text-sm text-slate-200 placeholder:text-slate-500 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
              />
            </div>
          </div>

          {/* Options list */}
          <div className="max-h-48 overflow-auto">
            {filtered.length === 0 ? (
              <div className="px-3 py-2 text-sm text-slate-500">No matches</div>
            ) : (
              filtered.map((opt) => (
                <div
                  key={opt.value}
                  className={`flex cursor-pointer items-center gap-2 px-3 py-2 text-sm transition-colors ${
                    opt.value === scalarValue
                      ? 'bg-indigo-500/10 text-indigo-300'
                      : 'text-slate-300 hover:bg-slate-700/50'
                  }`}
                  onClick={() => selectItem(opt)}
                  role="option"
                  aria-selected={opt.value === scalarValue}
                >
                  <span className="flex-1">{opt.label}</span>
                  {opt.value === scalarValue && (
                    <span className="text-xs text-indigo-400">✓</span>
                  )}
                </div>
              ))
            )}
          </div>
        </div>
      )}

      {/* ── Hidden input for form.elements compatibility ──────── */}
      <input type="hidden" name={name} value={scalarValue} />
    </div>
  );
}
