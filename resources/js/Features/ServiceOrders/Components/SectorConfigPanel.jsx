import { useState, useEffect, useCallback, useRef } from 'react';
import { createPortal } from 'react-dom';
import { Plus, X } from 'lucide-react';
import { labelFor } from '@/utils/enums';
import { t } from '@/utils/i18n';

const PRIORITY_OPTIONS = [
  { value: 'low' },
  { value: 'normal' },
  { value: 'high' },
  { value: 'urgent' },
];

/* ── Priority badge ───────────────────────────────────────────── */
function PriorityBadge({ priority }) {
  if (!priority) return null;
  const variants = {
    low:    'bg-blue-500/20 text-blue-300',
    normal: 'bg-brand-mid/20 text-brand-mid',
    high:   'bg-orange-500/20 text-orange-300',
    urgent: 'bg-red-500/20 text-red-300',
  };
  return (
    <span className={`text-xs font-medium px-1.5 py-0.5 rounded whitespace-nowrap ${variants[priority] || 'bg-brand-mid/20 text-brand-mid'}`}>
      {labelFor(priority)}
    </span>
  );
}

/* ── Mini-modal: select sector + service types + priority per type ─ */
function AddSectorModal({ sectors, serviceTypesBySector, onAdd, onClose }) {
  const [selectedSectorId, setSelectedSectorId] = useState(null);
  const [typeSelections, setTypeSelections] = useState({});
  const modalRef = useRef(null);

  useEffect(() => {
    const handler = (e) => {
      if (modalRef.current && !modalRef.current.contains(e.target)) onClose();
    };
    const timer = setTimeout(() => document.addEventListener('mousedown', handler), 0);
    return () => { clearTimeout(timer); document.removeEventListener('mousedown', handler); };
  }, [onClose]);

  const handleSelectSector = (id) => {
    setSelectedSectorId(id);
    setTypeSelections({});
  };

  const toggleType = (typeId) => {
    setTypeSelections(prev => {
      if (typeId in prev) {
        const next = { ...prev };
        delete next[typeId];
        return next;
      }
      return { ...prev, [typeId]: null };
    });
  };

  const setPriority = (typeId, priority) => {
    setTypeSelections(prev => ({ ...prev, [typeId]: priority || null }));
  };

  const availableTypes = selectedSectorId
    ? (serviceTypesBySector[selectedSectorId]?.service_types ?? [])
    : [];

  const selectedCount = Object.keys(typeSelections).length;
  const canAdd = selectedSectorId && selectedCount > 0;

  const handleAdd = () => {
    if (!canAdd) return;
    const serviceTypes = Object.entries(typeSelections).map(([id, priority]) => ({ id, priority }));
    onAdd(selectedSectorId, serviceTypes);
    onClose();
  };

  return createPortal(
    <div className="fixed inset-0 z-[9998] flex items-center justify-center p-4">
      <div className="absolute inset-0 bg-black/40" onClick={onClose} />
      <div
        ref={modalRef}
        className="relative bg-brand-white rounded-xl shadow-2xl border border-brand-mid/20 w-full max-w-sm z-[9999] overflow-hidden"
      >
        <div className="flex items-center justify-between px-4 py-3 border-b border-brand-mid/10">
          <h3 className="text-sm font-semibold text-brand-darkest">
            {t('pages.service_orders.add_sector')}
          </h3>
          <button
            type="button"
            onClick={onClose}
            className="rounded p-1 text-brand-mid hover:text-brand-darkest hover:bg-brand-mid/10 transition-colors"
          >
            <X className="h-4 w-4" />
          </button>
        </div>

        <div className="p-4 space-y-4">
          {/* Sector selection */}
          <div>
            <label className="block text-xs font-medium text-brand-mid mb-1.5">Setor</label>
            <div className="max-h-[160px] overflow-y-auto rounded-lg border border-brand-mid/20 divide-y divide-brand-mid/5">
              {sectors.map(s => (
                <button
                  key={s.id}
                  type="button"
                  onClick={() => handleSelectSector(s.id)}
                  className={`w-full text-left px-3 py-2 text-sm transition-colors ${
                    selectedSectorId === s.id
                      ? 'bg-brand-accent/10 text-brand-accent font-medium'
                      : 'text-brand-darkest hover:bg-brand-light'
                  }`}
                >
                  {s.name}
                </button>
              ))}
              {sectors.length === 0 && (
                <p className="px-3 py-2 text-xs text-brand-mid/60 italic">Nenhum setor disponível</p>
              )}
            </div>
          </div>

          {/* Service types + priority per type */}
          {selectedSectorId && (
            <div>
              <label className="block text-xs font-medium text-brand-mid mb-1.5">
                Tipos de Serviço e Prioridade
              </label>
              {availableTypes.length === 0 ? (
                <p className="text-xs text-brand-mid/50 italic px-1">
                  {t('pages.service_orders.no_service_types_for_sector')}
                </p>
              ) : (
                <div className="rounded-lg border border-brand-mid/20 divide-y divide-brand-mid/5 max-h-[200px] overflow-y-auto">
                  {availableTypes.map(st => {
                    const isSelected = st.id in typeSelections;
                    return (
                      <div
                        key={st.id}
                        className={`flex items-center gap-2 px-3 py-2 transition-colors ${
                          isSelected ? 'bg-brand-accent/5' : 'hover:bg-brand-light'
                        }`}
                      >
                        <button
                          type="button"
                          onClick={() => toggleType(st.id)}
                          className={`flex-1 text-left text-sm ${isSelected ? 'text-brand-accent font-medium' : 'text-brand-darkest'}`}
                        >
                          {st.name}
                        </button>
                        {isSelected && (
                          <select
                            value={typeSelections[st.id] ?? ''}
                            onChange={e => setPriority(st.id, e.target.value)}
                            onClick={e => e.stopPropagation()}
                            className="rounded border border-brand-mid/30 bg-brand-dark px-2 py-1 text-xs text-brand-darkest focus:outline-none focus:ring-1 focus:ring-brand-accent/30"
                          >
                            <option value="">{t('pages.service_orders.priority_placeholder')}</option>
                            {PRIORITY_OPTIONS.map(o => (
                              <option key={o.value} value={o.value}>{labelFor(o.value) ?? o.value}</option>
                            ))}
                          </select>
                        )}
                        <button
                          type="button"
                          onClick={() => toggleType(st.id)}
                          className={`rounded p-0.5 transition-colors flex-shrink-0 ${
                            isSelected
                              ? 'text-brand-accent hover:text-red-400 hover:bg-red-400/10'
                              : 'text-brand-mid/40 hover:text-brand-accent hover:bg-brand-accent/10'
                          }`}
                        >
                          {isSelected ? <X className="h-3.5 w-3.5" /> : <Plus className="h-3.5 w-3.5" />}
                        </button>
                      </div>
                    );
                  })}
                </div>
              )}
            </div>
          )}
        </div>

        <div className="flex justify-end gap-2 px-4 py-3 border-t border-brand-mid/10">
          <button
            type="button"
            onClick={onClose}
            className="rounded-lg px-4 py-1.5 text-xs text-brand-mid hover:text-brand-darkest hover:bg-brand-mid/10 transition-colors"
          >
            Cancelar
          </button>
          <button
            type="button"
            onClick={handleAdd}
            disabled={!canAdd}
            className="rounded-lg bg-brand-accent px-4 py-1.5 text-xs font-medium text-white hover:bg-brand-accent/90 disabled:opacity-40 disabled:cursor-not-allowed transition-colors"
          >
            Adicionar
          </button>
        </div>
      </div>
    </div>,
    document.body
  );
}

/* ── Positioned add-type dropdown ────────────────────────────── */
function AddTypeDropdown({ listRef, btnRef, options, onAdd }) {
  const [style, setStyle] = useState({});

  useEffect(() => {
    if (!btnRef.current) return;
    const r = btnRef.current.getBoundingClientRect();
    setStyle({ top: r.bottom + 4, left: r.left, minWidth: Math.max(r.width, 160) });
  }, [btnRef]);

  return (
    <div
      ref={listRef}
      style={{ position: 'fixed', zIndex: 9999, ...style }}
      className="rounded-lg border border-brand-mid/20 bg-brand-white shadow-xl py-1"
    >
      {options.map(st => (
        <button
          key={st.id}
          type="button"
          onClick={() => onAdd(st)}
          className="w-full text-left px-3 py-1.5 text-sm text-brand-darkest hover:bg-brand-light transition-colors"
        >
          {st.name}
        </button>
      ))}
    </div>
  );
}

/* ── Service type rows with inline priority ───────────────────── */
function ServiceTypeRows({ options, selected, onChange }) {
  const available = options.filter(st => !selected.find(s => s.id === st.id));

  const remove = (id) => onChange(selected.filter(s => s.id !== id));

  const updatePriority = (id, priority) => {
    onChange(selected.map(s => s.id === id ? { ...s, priority: priority || null } : s));
  };

  const [addOpen, setAddOpen] = useState(false);
  const btnRef  = useRef(null);
  const listRef = useRef(null);

  useEffect(() => {
    if (!addOpen) return;
    const handler = (e) => {
      if (!btnRef.current?.contains(e.target) && !listRef.current?.contains(e.target)) {
        setAddOpen(false);
      }
    };
    document.addEventListener('mousedown', handler);
    return () => document.removeEventListener('mousedown', handler);
  }, [addOpen]);

  const addType = (st) => {
    onChange([...selected, { id: st.id, priority: null }]);
    setAddOpen(false);
  };

  if (options.length === 0) {
    return (
      <p className="text-xs text-brand-mid/50 italic px-1 py-0.5">
        {t('pages.service_orders.no_service_types_for_sector')}
      </p>
    );
  }

  return (
    <div className="space-y-1.5">
      {selected.length > 0 && (
        <div className="rounded border border-brand-mid/15 overflow-hidden">
          <table className="w-full text-xs">
            <tbody className="divide-y divide-brand-mid/10">
              {selected.map(s => {
                const st = options.find(o => o.id === s.id);
                if (!st) return null;
                return (
                  <tr key={s.id}>
                    <td className="px-2 py-1.5 text-brand-darkest">{st.name}</td>
                    <td className="px-1 py-1.5">
                      <select
                        value={s.priority ?? ''}
                        onChange={e => updatePriority(s.id, e.target.value)}
                        className="rounded border border-brand-mid/20 bg-brand-dark px-1.5 py-0.5 text-xs text-brand-darkest focus:outline-none focus:ring-1 focus:ring-brand-accent/30"
                      >
                        <option value="">{t('pages.service_orders.priority_placeholder')}</option>
                        {PRIORITY_OPTIONS.map(o => (
                          <option key={o.value} value={o.value}>{labelFor(o.value) ?? o.value}</option>
                        ))}
                      </select>
                    </td>
                    <td className="px-1 py-1.5 w-8 text-right">
                      <button
                        type="button"
                        onClick={() => remove(s.id)}
                        className="rounded p-0.5 text-brand-mid hover:text-red-400 hover:bg-red-400/10 transition-colors"
                        aria-label={`Remover ${st.name}`}
                      >
                        <X className="h-3 w-3" />
                      </button>
                    </td>
                  </tr>
                );
              })}
            </tbody>
          </table>
        </div>
      )}

      {available.length > 0 && (
        <div className="relative">
          <button
            ref={btnRef}
            type="button"
            onClick={() => setAddOpen(v => !v)}
            className="flex items-center gap-1 rounded border border-dashed border-brand-mid/30 px-2 py-1 text-xs text-brand-mid hover:border-brand-accent hover:text-brand-accent transition-colors"
          >
            <Plus className="h-3 w-3" />
            Tipo de Serviço
          </button>

          {addOpen && createPortal(
            <AddTypeDropdown
              listRef={listRef}
              btnRef={btnRef}
              options={available}
              onAdd={addType}
            />,
            document.body
          )}
        </div>
      )}
    </div>
  );
}

/* ── Main component ───────────────────────────────────────────── */
export default function SectorConfigPanel({ serviceTypesBySector = {}, onChange, isOpen, error }) {
  const [rows, setRows] = useState([]);
  const [showAddModal, setShowAddModal] = useState(false);

  useEffect(() => {
    if (!isOpen) setRows([]);
  }, [isOpen]);

  useEffect(() => {
    onChange?.(rows.map(r => ({
      sector_id:    r.sector_id,
      service_types: r.serviceTypes,
    })));
  }, [rows, onChange]);

  const availableSectors = Object.entries(serviceTypesBySector)
    .filter(([id]) => !rows.find(r => r.sector_id === id))
    .map(([id, data]) => ({ id, name: data.name }));

  const addSector = useCallback((sectorId, serviceTypes) => {
    setRows(prev => [...prev, { sector_id: sectorId, serviceTypes }]);
  }, []);

  const removeRow = useCallback((sectorId) => {
    setRows(prev => prev.filter(r => r.sector_id !== sectorId));
  }, []);

  const updateServiceTypes = useCallback((sectorId, serviceTypes) => {
    setRows(prev => prev.map(r => r.sector_id === sectorId ? { ...r, serviceTypes } : r));
  }, []);

  return (
    <div className="mb-4">
      <div className="flex items-center justify-between mb-2">
        <label className={`text-sm font-medium ${error ? 'text-red-500' : 'text-brand-darkest'}`}>
          {t('pages.service_orders.sectors_label')}
          <span className="text-red-500 ml-0.5">*</span>
        </label>
        {availableSectors.length > 0 && (
          <button
            type="button"
            onClick={() => setShowAddModal(true)}
            className="flex items-center gap-1.5 rounded-lg border border-dashed border-brand-mid/40 px-3 py-1.5 text-xs text-brand-mid hover:border-brand-accent hover:text-brand-accent transition-colors"
          >
            <Plus className="h-3.5 w-3.5" />
            {t('pages.service_orders.add_sector')}
          </button>
        )}
      </div>
      {error && <p className="text-xs text-red-500 mb-2">{error}</p>}

      {showAddModal && (
        <AddSectorModal
          sectors={availableSectors}
          serviceTypesBySector={serviceTypesBySector}
          onAdd={addSector}
          onClose={() => setShowAddModal(false)}
        />
      )}

      {rows.length > 0 && (
        <div className="space-y-3 mb-2">
          {rows.map(row => {
            const sectorData  = serviceTypesBySector[row.sector_id];
            const sectorName  = sectorData?.name ?? row.sector_id;
            const sectorTypes = sectorData?.service_types ?? [];

            return (
              <div
                key={row.sector_id}
                className="rounded-lg border border-brand-mid/20 bg-brand-dark/20 px-3 py-2.5 space-y-2.5"
              >
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

                <ServiceTypeRows
                  options={sectorTypes}
                  selected={row.serviceTypes}
                  onChange={types => updateServiceTypes(row.sector_id, types)}
                />
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
