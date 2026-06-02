import { useState, useEffect, useCallback, useRef } from 'react';
import { createPortal } from 'react-dom';
import { Plus, X, ChevronDown } from 'lucide-react';
import { labelFor } from '@/utils/enums';
import { t } from '@/utils/i18n';

const PRIORITY_OPTIONS = [
  { value: 'low' },
  { value: 'normal' },
  { value: 'high' },
  { value: 'urgent' },
];

const DROPDOWN_MAX_H = 200; // px — used to decide flip direction

/* ── Smart portal positioning ────────────────────────────────── */
function useDropdownPos(btnRef, open, dropdownWidth = 200) {
  const [style, setStyle] = useState({});

  const recalc = useCallback(() => {
    if (!btnRef.current) return;
    const r    = btnRef.current.getBoundingClientRect();
    const vh   = window.innerHeight;
    const w    = Math.max(r.width, dropdownWidth);
    // Anchor to right edge of button, extend leftward
    const right = window.innerWidth - r.right;

    const spaceBelow = vh - r.bottom - 8;
    const spaceAbove = r.top - 8;

    if (spaceBelow >= Math.min(DROPDOWN_MAX_H, 100) || spaceBelow >= spaceAbove) {
      setStyle({ top: r.bottom + 4, right, width: w, maxHeight: Math.min(DROPDOWN_MAX_H, spaceBelow) });
    } else {
      setStyle({ bottom: vh - r.top + 4, right, width: w, maxHeight: Math.min(DROPDOWN_MAX_H, spaceAbove) });
    }
  }, [btnRef, dropdownWidth]);

  useEffect(() => {
    if (!open) return;
    recalc();
    window.addEventListener('scroll', recalc, true);
    window.addEventListener('resize', recalc);
    return () => {
      window.removeEventListener('scroll', recalc, true);
      window.removeEventListener('resize', recalc);
    };
  }, [open, recalc]);

  return style;
}

/* ── Outside-click handler ───────────────────────────────────── */
function useOutsideClick(refs, enabled, onClose) {
  useEffect(() => {
    if (!enabled) return;
    const handler = (e) => {
      if (refs.every(r => !r.current?.contains(e.target))) onClose();
    };
    document.addEventListener('mousedown', handler);
    return () => document.removeEventListener('mousedown', handler);
  }, [enabled, onClose, refs]);
}

/* ── Service type multi-select ───────────────────────────────── */
function ServiceTypeDropdown({ options, selected, onChange }) {
  const [open, setOpen] = useState(false);
  const btnRef  = useRef(null);
  const listRef = useRef(null);
  const posStyle = useDropdownPos(btnRef, open, 160);
  useOutsideClick([btnRef, listRef], open, () => setOpen(false));

  const toggle = (id) =>
    onChange(selected.includes(id) ? selected.filter(x => x !== id) : [...selected, id]);

  const label = selected.length === 0
    ? t('pages.service_orders.service_types_for_sector')
    : `${selected.length} selecionado${selected.length > 1 ? 's' : ''}`;

  if (options.length === 0) {
    return (
      <span className="text-xs text-brand-mid/50 italic px-2">
        {t('pages.service_orders.no_service_types_for_sector')}
      </span>
    );
  }

  return (
    <>
      <button
        ref={btnRef}
        type="button"
        onClick={() => setOpen(v => !v)}
        className="flex items-center gap-1 rounded border border-brand-mid/30 bg-brand-dark px-2 py-1.5 text-xs text-brand-darkest hover:border-brand-mid/60 transition-colors w-full"
      >
        <span className="flex-1 text-left truncate">{label}</span>
        <ChevronDown className={`h-3 w-3 flex-shrink-0 transition-transform ${open ? 'rotate-180' : ''}`} />
      </button>

      {open && createPortal(
        <div
          ref={listRef}
          style={{ position: 'fixed', zIndex: 9999, overflowY: 'auto', overflowX: 'hidden', left: 'auto', ...posStyle }}
          className="rounded-lg border border-brand-mid/20 bg-brand-white shadow-xl py-1"
        >
          {options.map(st => (
            <label
              key={st.id}
              className="flex items-center gap-2 px-3 py-1.5 text-sm text-brand-darkest hover:bg-brand-light cursor-pointer select-none"
            >
              <input
                type="checkbox"
                checked={selected.includes(st.id)}
                onChange={() => toggle(st.id)}
                className="h-3.5 w-3.5 rounded border-brand-mid/40 accent-brand-accent"
              />
              <span className="truncate">{st.name}</span>
            </label>
          ))}
        </div>,
        document.body
      )}
    </>
  );
}

/* ── Add sector button ───────────────────────────────────────── */
function AddSectorButton({ sectors, onAdd }) {
  const [open, setOpen] = useState(false);
  const btnRef  = useRef(null);
  const listRef = useRef(null);
  const posStyle = useDropdownPos(btnRef, open, 230);
  useOutsideClick([btnRef, listRef], open, () => setOpen(false));

  return (
    <>
      <button
        ref={btnRef}
        type="button"
        onClick={() => setOpen(v => !v)}
        className="flex items-center gap-1.5 rounded-lg border border-dashed border-brand-mid/40 px-3 py-1.5 text-xs text-brand-mid hover:border-brand-accent hover:text-brand-accent transition-colors"
      >
        <Plus className="h-3.5 w-3.5" />
        {t('pages.service_orders.add_sector')}
      </button>

      {open && createPortal(
        <div
          ref={listRef}
          style={{ position: 'fixed', zIndex: 9999, overflowY: 'auto', overflowX: 'hidden', left: 'auto', ...posStyle }}
          className="rounded-lg border border-brand-mid/20 bg-brand-white shadow-xl py-1"
        >
          {sectors.map(s => (
            <button
              key={s.id}
              type="button"
              onClick={() => { onAdd(s.id); setOpen(false); }}
              className="w-full text-left px-3 py-2 text-sm text-brand-darkest hover:bg-brand-light transition-colors"
            >
              {s.name}
            </button>
          ))}
        </div>,
        document.body
      )}
    </>
  );
}

/* ── Main component ───────────────────────────────────────────── */
export default function SectorConfigPanel({ serviceTypesBySector = {}, onChange, isOpen, error }) {
  const [rows, setRows] = useState([]);

  useEffect(() => {
    if (!isOpen) setRows([]);
  }, [isOpen]);

  useEffect(() => {
    onChange?.(rows.map(r => ({
      sector_id:        r.sector_id,
      priority:         r.priority ?? null,
      service_type_ids: r.serviceTypeIds,
    })));
  }, [rows, onChange]);

  const availableSectors = Object.entries(serviceTypesBySector)
    .filter(([id]) => !rows.find(r => r.sector_id === id))
    .map(([id, data]) => ({ id, name: data.name }));

  const addSector = useCallback((sectorId) => {
    setRows(prev => [...prev, { sector_id: sectorId, priority: null, serviceTypeIds: [] }]);
  }, []);

  const removeRow = useCallback((sectorId) => {
    setRows(prev => prev.filter(r => r.sector_id !== sectorId));
  }, []);

  const updateRow = useCallback((sectorId, field, value) => {
    setRows(prev => prev.map(r => r.sector_id === sectorId ? { ...r, [field]: value } : r));
  }, []);

  return (
    <div className="mb-4">
      <div className="flex items-center justify-between mb-2">
        <label className={`text-sm font-medium ${error ? 'text-red-500' : 'text-brand-darkest'}`}>
          {t('pages.service_orders.sectors_label')}
          <span className="text-red-500 ml-0.5">*</span>
        </label>
        {availableSectors.length > 0 && (
          <AddSectorButton sectors={availableSectors} onAdd={addSector} />
        )}
      </div>
      {error && <p className="text-xs text-red-500 mb-2">{error}</p>}

      {rows.length > 0 && (
        <div className="space-y-2 mb-2">
          {rows.map(row => {
            const sectorData  = serviceTypesBySector[row.sector_id];
            const sectorName  = sectorData?.name ?? row.sector_id;
            const sectorTypes = sectorData?.service_types ?? [];

            return (
              <div
                key={row.sector_id}
                className="rounded-lg border border-brand-mid/20 bg-brand-dark/20 px-3 py-2 space-y-2"
              >
                {/* Row 1: name + remove */}
                <div className="flex items-center justify-between gap-2">
                  <span className="text-sm font-medium text-brand-darkest truncate">
                    {sectorName}
                  </span>
                  <button
                    type="button"
                    onClick={() => removeRow(row.sector_id)}
                    className="flex-shrink-0 rounded p-1 text-brand-mid hover:text-red-400 hover:bg-red-400/10 transition-colors"
                    aria-label={`Remover ${sectorName}`}
                  >
                    <X className="h-3.5 w-3.5" />
                  </button>
                </div>

                {/* Row 2: priority + service types (each takes half the width) */}
                <div className="flex gap-2">
                  <select
                    value={row.priority ?? ''}
                    onChange={e => updateRow(row.sector_id, 'priority', e.target.value || null)}
                    className="flex-1 min-w-0 rounded border border-brand-mid/30 bg-brand-dark px-2 py-1.5 text-xs text-brand-darkest focus:outline-none focus:ring-1 focus:ring-brand-accent"
                  >
                    <option value="">{t('pages.service_orders.priority_placeholder')}</option>
                    {PRIORITY_OPTIONS.map(o => (
                      <option key={o.value} value={o.value}>
                        {labelFor(o.value) ?? o.value}
                      </option>
                    ))}
                  </select>

                  <div className="flex-1 min-w-0">
                    <ServiceTypeDropdown
                      options={sectorTypes}
                      selected={row.serviceTypeIds}
                      onChange={ids => updateRow(row.sector_id, 'serviceTypeIds', ids)}
                    />
                  </div>
                </div>
              </div>
            );
          })}
        </div>
      )}

      {rows.length === 0 && availableSectors.length === 0 && (
        <p className="text-xs text-brand-mid/60 italic">Nenhum setor disponível</p>
      )}
    </div>
  );
}
