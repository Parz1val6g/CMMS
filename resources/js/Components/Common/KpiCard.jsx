import { TrendingUp, TrendingDown, Minus } from 'lucide-react';

const BORDER = {
  blue:   'border-blue-500',
  yellow: 'border-yellow-500',
  green:  'border-green-500',
  indigo: 'border-indigo-500',
  red:    'border-red-500',
  teal:   'border-teal-500',
};

/**
 * Dashboard KPI card.
 *
 * @param {string}  label
 * @param {number}  value
 * @param {string}  [unit]
 * @param {string}  [color]
 * @param {number}  [delta]       — signed integer vs previous period (optional)
 * @param {string}  [deltaLabel]  — e.g. "vs last week"
 * @param {boolean} [deltaInvert] — when true, negative delta is GOOD (e.g. overdue tasks)
 */
export default function KpiCard({ label, value, unit, color = 'blue', delta, deltaLabel, deltaInvert = false }) {
  const borderColor = BORDER[color] ?? BORDER.blue;

  let DeltaIcon = Minus;
  let deltaColor = 'text-gray-400';

  if (delta !== undefined && delta !== null) {
    const isPositive = delta > 0;
    const isGood     = deltaInvert ? !isPositive : isPositive;

    if (delta > 0) {
      DeltaIcon  = TrendingUp;
      deltaColor = isGood ? 'text-green-600' : 'text-red-500';
    } else if (delta < 0) {
      DeltaIcon  = TrendingDown;
      deltaColor = isGood ? 'text-green-600' : 'text-red-500';
    }
  }

  const showDelta = delta !== undefined && delta !== null;

  return (
    <div className={`rounded-lg border-l-4 ${borderColor} bg-brand-white p-5 shadow-sm flex flex-col gap-2`}>
      <h3 className="text-xs font-semibold uppercase tracking-wider text-brand-mid leading-tight">
        {label}
      </h3>

      <div className="flex items-baseline gap-1">
        <span className="text-3xl font-extrabold text-brand-darkest tabular-nums">
          {value ?? '—'}
        </span>
        {unit && <span className="text-sm text-brand-mid">{unit}</span>}
      </div>

      {showDelta && (
        <div className={`flex items-center gap-1 text-xs font-medium ${deltaColor}`}>
          <DeltaIcon className="h-3.5 w-3.5 shrink-0" />
          <span>
            {delta > 0 ? '+' : ''}{delta}
            {deltaLabel && <span className="text-gray-400 font-normal ml-1">{deltaLabel}</span>}
          </span>
        </div>
      )}
    </div>
  );
}
