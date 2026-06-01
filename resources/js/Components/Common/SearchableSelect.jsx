import { useState, useRef, useEffect, useMemo, useCallback } from 'react';
import { createPortal } from 'react-dom';
import { ChevronDown, Search } from 'lucide-react';
import { toScalar } from '@/utils/url';
import { t } from '@/utils/i18n';

export default function SearchableSelect({ name, options = [], value = '', onChange, placeholder, disabled = false, required }) {
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
    const DROPDOWN_MAX_H = 240;
    const GAP = 4;
    const spaceBelow = window.innerHeight - rect.bottom - GAP;
    const spaceAbove = rect.top - GAP;
    const openUpward = spaceBelow < DROPDOWN_MAX_H && spaceAbove > spaceBelow;
    return openUpward
      ? {
          position: 'fixed',
          bottom: window.innerHeight - rect.top + GAP,
          left: rect.left,
          width: rect.width,
          maxHeight: Math.min(DROPDOWN_MAX_H, spaceAbove),
          zIndex: 9999,
        }
      : {
          position: 'fixed',
          top: rect.bottom + GAP,
          left: rect.left,
          width: rect.width,
          maxHeight: Math.min(DROPDOWN_MAX_H, spaceBelow),
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
        className={`flex min-h-[38px] items-center gap-2 rounded-lg border border-brand-mid/20 bg-brand-white px-3 py-2 text-sm text-brand-darkest transition-colors ${disabled ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer hover:border-brand-mid'}`}
        onClick={() => { if (disabled) return; if (isOpen) close(); else open(); }}
        role="combobox"
        aria-expanded={isOpen}
        aria-haspopup="listbox"
        tabIndex={disabled ? -1 : 0}
        onKeyDown={(e) => { if (disabled) return; if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); if (isOpen) close(); else open(); } }}
      >
        <span className={`flex-1 ${scalarValue !== '' ? '' : 'text-brand-mid'}`}>
          {scalarValue !== '' ? displayLabel : (placeholder || t('common.searchable_select.placeholder'))}
        </span>
        <ChevronDown className={`h-4 w-4 text-brand-mid transition-transform ${isOpen ? 'rotate-180' : ''}`} />
      </div>

      {/* ── Dropdown — portal to document.body to escape CSS transform contexts ── */}
      {isOpen && createPortal(
        <div
          id={`ss-dropdown-${name}`}
          style={dropdownStyle}
          className="flex flex-col overflow-hidden rounded-lg border border-brand-mid/20 bg-brand-white shadow-2xl"
        >
          {/* Search bar */}
          <div className="shrink-0 border-b border-brand-mid/20 bg-brand-white p-2">
            <div className="relative">
              <Search className="absolute left-2.5 top-1/2 h-4 w-4 -translate-y-1/2 text-brand-mid" />
              <input
                ref={inputRef}
                type="text"
                value={query}
                onChange={e => setQuery(e.target.value)}
                placeholder={t('pages.common.search_placeholder')}
                className="w-full rounded-md border border-brand-mid/20 bg-brand-light py-1.5 pl-8 pr-3 text-sm text-brand-darkest placeholder:text-brand-mid focus:border-brand-accent focus:outline-none focus:ring-1 focus:ring-brand-accent"
              />
            </div>
          </div>

          {/* Options list */}
          <div className="overflow-auto flex-1">
            {filtered.length === 0 ? (
              <div className="px-3 py-2 text-sm text-brand-mid">{t('pages.common.no_matches')}</div>
            ) : (
              filtered.map((opt) => (
                <div
                  key={opt.value}
                  className={`flex cursor-pointer items-center gap-2 px-3 py-2 text-sm transition-colors ${
                    opt.value === scalarValue
                      ? 'bg-brand-accent/10 text-brand-accent'
                      : 'text-brand-mid hover:bg-brand-light'
                  }`}
                  onClick={() => selectItem(opt)}
                  role="option"
                  aria-selected={opt.value === scalarValue}
                >
                  <span className="flex-1">{opt.label}</span>
                  {opt.value === scalarValue && (
                    <span className="text-xs text-brand-accent">✓</span>
                  )}
                </div>
              ))
            )}
          </div>
        </div>,
        document.body
      )}

      {/* ── Hidden input for form.elements compatibility ──────── */}
      <input type="hidden" name={name} value={scalarValue} required={required} />
    </div>
  );
}
