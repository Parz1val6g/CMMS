import { useState, useMemo } from 'react';
import { Search, Package } from 'lucide-react';
import { t } from '@/utils/i18n';

/* ── Status badge styling ────────────────────────────────────── */
const EQUIP_STATUS_STYLE = {
  active:      'bg-green-100  text-green-700  border border-green-200',
  maintenance: 'bg-yellow-100 text-yellow-700 border border-yellow-200',
  retired:     'bg-red-100    text-red-700    border border-red-200',
  in_use:      'bg-blue-100   text-blue-700   border border-blue-200',
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
          ? 'bg-brand-accent/15 text-brand-accent border border-brand-accent/30'
          : 'bg-brand-mid/20  text-brand-mid border border-brand-mid/30'
      }`}
    >
      {isLoanable ? 'Loanable' : 'Fixed'}
    </span>
  );
}

/* ── Revision date helper ────────────────────────────────────── */
function RevisionDate({ date }) {
  if (!date) return <span className="text-brand-mid">—</span>;
  const d = new Date(date);
  const isOverdue = d < new Date();
  return (
    <span className={isOverdue ? 'text-red-400' : 'text-brand-mid'}>
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
      <div className="flex items-center justify-center h-40 text-brand-mid">
        <p className="text-sm">No service order selected.</p>
      </div>
    );
  }

  /* ── No equipment linked ───────────────────────────────────── */
  if (!equipment) {
    return (
      <div className="flex flex-col items-center justify-center h-40 text-brand-mid gap-2">
        <Package className="h-6 w-6 text-brand-mid" />
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
          <Search className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-brand-mid" />
          <input
            type="text"
            value={query}
            onChange={(e) => setQuery(e.target.value)}
            placeholder={t('pages.service_orders.equipment_filter_placeholder')}
            className="w-full pl-9 pr-3 py-2 text-sm rounded-lg bg-brand-white border border-brand-mid/20 text-brand-darkest placeholder-brand-mid focus:outline-none focus:ring-2 focus:ring-brand-accent/50"
          />
        </div>
        <div className="flex flex-col items-center justify-center h-40 text-brand-mid gap-2">
          <Search className="h-5 w-5 text-brand-mid" />
          <p className="text-sm">{t('pages.service_orders.no_equipment_filter_match')}</p>
        </div>
      </div>
    );
  }

  /* ── Data table ────────────────────────────────────────────── */
  return (
    <div>
      {/* Quick Filter */}
      <div className="relative mb-4">
        <Search className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-brand-mid" />
        <input
          type="text"
          value={query}
          onChange={(e) => setQuery(e.target.value)}
          placeholder={t('pages.service_orders.equipment_filter_placeholder')}
          className="w-full pl-9 pr-3 py-2 text-sm rounded-lg bg-brand-white border border-brand-mid/20 text-brand-darkest placeholder-brand-mid focus:outline-none focus:ring-2 focus:ring-brand-accent/50"
        />
      </div>

      {/* Equipment table */}
      <div className="rounded-lg border border-brand-mid/20 overflow-hidden">
        <table className="w-full text-sm">
          {/* Header */}
          <thead className="bg-brand-light border-b border-brand-mid/20">
            <tr>
              <th className="text-left px-4 py-3 text-xs font-semibold text-brand-mid uppercase tracking-wider">Item Name</th>
              <th className="text-left px-4 py-3 text-xs font-semibold text-brand-mid uppercase tracking-wider">Serial Number</th>
              <th className="text-left px-4 py-3 text-xs font-semibold text-brand-mid uppercase tracking-wider">Type</th>
              <th className="text-left px-4 py-3 text-xs font-semibold text-brand-mid uppercase tracking-wider">Status</th>
              <th className="text-left px-4 py-3 text-xs font-semibold text-brand-mid uppercase tracking-wider">Next Revision</th>
            </tr>
          </thead>

          {/* Body */}
          <tbody className="divide-y divide-brand-mid/10">
            <tr className="bg-brand-white hover:bg-brand-light transition-colors">
              <td className="px-4 py-3">
                <div className="flex items-center gap-2">
                  <Package className="h-4 w-4 text-brand-accent shrink-0" />
                  <span className="font-medium text-brand-darkest">{filtered.name}</span>
                </div>
                {filtered.description && (
                  <p className="mt-0.5 text-xs text-brand-mid line-clamp-2">{filtered.description}</p>
                )}
              </td>
              <td className="px-4 py-3 font-mono text-xs text-brand-mid">
                {filtered.serial_number}
              </td>
              <td className="px-4 py-3">
                <TypeBadge isLoanable={filtered.is_loanable} />
              </td>
              <td className="px-4 py-3">
                <span
                  className={`inline-flex items-center px-2 py-0.5 text-xs font-semibold rounded-full ${
                    EQUIP_STATUS_STYLE[filtered.status] || 'bg-brand-mid/20 text-brand-mid border border-brand-mid/20'
                  }`}
                >
                  {EQUIP_STATUS_LABEL[filtered.status] || filtered.status}
                </span>
              </td>
              <td className="px-4 py-3 text-xs">
                <RevisionDate date={filtered.next_revision_date} label={t('pages.service_orders.next_revision_label')} />
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      {/* Footer summary */}
      <div className="mt-3 flex items-center justify-between px-1">
        <span className="text-xs text-brand-mid">
          {query.trim() ? '1 equipment (filtered)' : '1 equipment'}
        </span>
      </div>
    </div>
  );
}
