import { useState, useMemo } from 'react';
import { Search, AlertCircle, Package } from 'lucide-react';

/* ── Status badge styling ────────────────────────────────────── */
const EQUIP_STATUS_STYLE = {
  active:      'bg-green-500/20  text-green-300  border border-green-500/40',
  maintenance: 'bg-yellow-500/20 text-yellow-300 border border-yellow-500/40',
  retired:     'bg-red-500/20    text-red-300    border border-red-500/40',
  in_use:      'bg-blue-500/20   text-blue-300   border border-blue-500/40',
};

const EQUIP_STATUS_LABEL = {
  active:      'Active',
  maintenance: 'Maintenance',
  retired:     'Retired',
  in_use:      'In Use',
};

/* ── Type badge ──────────────────────────────────────────────── */
function TypeBadge({ isLoanable }) {
  return (
    <span
      className={`inline-flex items-center px-2 py-0.5 text-xs font-semibold rounded-full ${
        isLoanable
          ? 'bg-indigo-500/20 text-indigo-300 border border-indigo-500/40'
          : 'bg-slate-600/30  text-slate-400 border border-slate-600/40'
      }`}
    >
      {isLoanable ? 'Loanable' : 'Fixed'}
    </span>
  );
}

/* ── Revision date helper ────────────────────────────────────── */
function RevisionDate({ date, label }) {
  if (!date) return <span className="text-slate-500">—</span>;
  const d = new Date(date);
  const isOverdue = d < new Date();
  return (
    <span className={isOverdue ? 'text-red-400' : 'text-slate-300'}>
      {d.toLocaleDateString()}
    </span>
  );
}

/**
 * SOMaterialsList — Equipment & materials associated with a Service Order.
 *
 * Renders the primary loaned equipment (from serviceOrder.equipment)
 * with a search filter bar, type badges, and revision tracking.
 *
 * @param {Object}   props
 * @param {Object}   props.serviceOrder   - The full Service Order object (equipment embedded)
 */
export default function SOMaterialsList({ serviceOrder }) {
  const [query, setQuery] = useState('');

  const equipment = serviceOrder?.equipment;

  /* ── Client-side filter (name / serial) ─────────────────────── */
  const filtered = useMemo(() => {
    if (!equipment) return null;
    if (!query.trim()) return equipment;
    const q = query.toLowerCase();
    const match =
      equipment.name?.toLowerCase().includes(q) ||
      equipment.serial_number?.toLowerCase().includes(q);
    return match ? equipment : null;
  }, [equipment, query]);

  /* ── Waiting for SO data ───────────────────────────────────── */
  if (!serviceOrder) {
    return (
      <div className="flex items-center justify-center h-40 text-slate-500">
        <p className="text-sm">No service order selected.</p>
      </div>
    );
  }

  /* ── No equipment linked ───────────────────────────────────── */
  if (!equipment) {
    return (
      <div className="flex flex-col items-center justify-center h-40 text-slate-500 gap-2">
        <Package className="h-6 w-6 text-slate-600" />
        <p className="text-sm">No equipment linked to this service order.</p>
      </div>
    );
  }

  /* ── Filtered empty ────────────────────────────────────────── */
  if (!filtered) {
    return (
      <div>
        {/* Search bar */}
        <div className="relative mb-4">
          <Search className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-500" />
          <input
            type="text"
            value={query}
            onChange={(e) => setQuery(e.target.value)}
            placeholder="Filter by name or serial number…"
            className="w-full pl-9 pr-3 py-2 text-sm rounded-lg bg-slate-800/60 border border-slate-700 text-slate-200 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/50"
          />
        </div>
        <div className="flex flex-col items-center justify-center h-40 text-slate-500 gap-2">
          <Search className="h-5 w-5 text-slate-600" />
          <p className="text-sm">No equipment matches your filter.</p>
        </div>
      </div>
    );
  }

  /* ── Data table ────────────────────────────────────────────── */
  return (
    <div>
      {/* Quick Filter */}
      <div className="relative mb-4">
        <Search className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-500" />
        <input
          type="text"
          value={query}
          onChange={(e) => setQuery(e.target.value)}
          placeholder="Filter by name or serial number…"
          className="w-full pl-9 pr-3 py-2 text-sm rounded-lg bg-slate-800/60 border border-slate-700 text-slate-200 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/50"
        />
      </div>

      {/* Equipment table */}
      <div className="rounded-lg border border-slate-700/50 overflow-hidden">
        <table className="w-full text-sm">
          {/* Header */}
          <thead className="bg-slate-800/60 border-b border-slate-700/50">
            <tr>
              <th className="text-left px-4 py-3 text-xs font-semibold text-slate-400 uppercase tracking-wider">Item Name</th>
              <th className="text-left px-4 py-3 text-xs font-semibold text-slate-400 uppercase tracking-wider">Serial Number</th>
              <th className="text-left px-4 py-3 text-xs font-semibold text-slate-400 uppercase tracking-wider">Type</th>
              <th className="text-left px-4 py-3 text-xs font-semibold text-slate-400 uppercase tracking-wider">Status</th>
              <th className="text-left px-4 py-3 text-xs font-semibold text-slate-400 uppercase tracking-wider">Next Revision</th>
            </tr>
          </thead>

          {/* Body */}
          <tbody className="divide-y divide-slate-700/30">
            <tr className="bg-slate-800/20 hover:bg-slate-700/40 transition-colors">
              <td className="px-4 py-3">
                <div className="flex items-center gap-2">
                  <Package className="h-4 w-4 text-indigo-400 shrink-0" />
                  <span className="font-medium text-slate-200">{filtered.name}</span>
                </div>
                {filtered.description && (
                  <p className="mt-0.5 text-xs text-slate-500 line-clamp-2">{filtered.description}</p>
                )}
              </td>
              <td className="px-4 py-3 font-mono text-xs text-slate-300">
                {filtered.serial_number}
              </td>
              <td className="px-4 py-3">
                <TypeBadge isLoanable={filtered.is_loanable} />
              </td>
              <td className="px-4 py-3">
                <span
                  className={`inline-flex items-center px-2 py-0.5 text-xs font-semibold rounded-full ${
                    EQUIP_STATUS_STYLE[filtered.status] || 'bg-slate-600/30 text-slate-400 border border-slate-600/40'
                  }`}
                >
                  {EQUIP_STATUS_LABEL[filtered.status] || filtered.status}
                </span>
              </td>
              <td className="px-4 py-3 text-xs">
                <RevisionDate date={filtered.next_revision_date} label="Next Revision" />
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      {/* Footer summary */}
      <div className="mt-3 flex items-center justify-between px-1">
        <span className="text-xs text-slate-500">
          {query.trim() ? '1 equipment (filtered)' : '1 equipment'}
        </span>
      </div>
    </div>
  );
}
