const BORDER_COLORS = {
  blue: 'border-blue-500',
  yellow: 'border-yellow-500',
  green: 'border-green-500',
  indigo: 'border-indigo-500',
};

/**
 * Dashboard KPI card with coloured left border and metric display.
 */
export default function KpiCard({ label, value, unit, color = 'blue' }) {
  const borderColor = BORDER_COLORS[color] ?? BORDER_COLORS.blue;

  return (
    <div className={`rounded-lg border-l-4 ${borderColor} bg-brand-white p-6 shadow-sm`}>
      <h3 className="text-sm font-semibold uppercase tracking-wide text-brand-mid">
        {label}
      </h3>
      <div className="mt-2 flex items-baseline gap-1">
        <span className="text-3xl font-extrabold text-brand-darkest">
          {value}
        </span>
        {unit && (
          <span className="text-sm text-brand-mid">{unit}</span>
        )}
      </div>
    </div>
  );
}
