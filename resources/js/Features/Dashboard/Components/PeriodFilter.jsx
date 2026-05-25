const PERIODS = [
  { key: 'today', label: 'Hoje' },
  { key: 'week',  label: 'Esta semana' },
  { key: 'month', label: 'Este mês' },
];

export default function PeriodFilter({ period, onChange }) {
  return (
    <div className="flex items-center gap-1 rounded-lg border border-brand-mid/20 bg-brand-white p-1 shadow-sm">
      {PERIODS.map((p) => (
        <button
          key={p.key}
          type="button"
          onClick={() => onChange(p.key)}
          className={[
            'rounded-md px-3 py-1.5 text-xs font-semibold transition-colors',
            period === p.key
              ? 'bg-brand-accent text-white shadow-sm'
              : 'text-brand-mid hover:text-brand-darkest hover:bg-brand-light',
          ].join(' ')}
        >
          {p.label}
        </button>
      ))}
    </div>
  );
}
