import { formatAbsolute } from '@/utils/format';
import DateDisplay from './DateDisplay';

const FMT = new Intl.DateTimeFormat('pt-PT', { day: 'numeric', month: 'short', year: 'numeric' });

function toParts(date) {
  return FMT.formatToParts(date).reduce((acc, p) => ({ ...acc, [p.type]: p.value }), {});
}

function parseDate(raw) {
  if (!raw) return null;
  const d = new Date(String(raw).length === 10 ? `${raw}T00:00:00` : raw);
  return isNaN(d.getTime()) ? null : d;
}

function stripDot(s) { return s.replace(/\./g, ''); }

/**
 * Deduplication logic — returns { main, year } for visual-hierarchy rendering.
 *   Same month+year:      main="12–15 jun"   year="2026"
 *   Diff month, same year: main="28 jun – 3 jul" year="2026"
 *   Diff years:           main="28 dez 2026 – 5 jan 2027" year=null
 */
function rangeParts(start, end) {
  if (!start && !end) return { main: '', year: null };
  if (!start) return { main: formatAbsolute(end),   year: null };
  if (!end)   return { main: formatAbsolute(start), year: null };
  const s = parseDate(start);
  const e = parseDate(end);
  if (!s || !e) return { main: `${formatAbsolute(start)} – ${formatAbsolute(end)}`, year: null };
  const sp = toParts(s);
  const ep = toParts(e);
  const sm = stripDot(sp.month);
  const em = stripDot(ep.month);
  if (sp.year === ep.year && sp.month === ep.month) {
    return { main: `${sp.day}–${ep.day} ${sm}`, year: sp.year };
  }
  if (sp.year === ep.year) {
    return { main: `${sp.day} ${sm} – ${ep.day} ${em}`, year: sp.year };
  }
  return { main: `${sp.day} ${sm} ${sp.year} – ${ep.day} ${em} ${ep.year}`, year: null };
}

/**
 * Renders a date range with pt-PT deduplication and visual hierarchy.
 *
 *   <DateRange start="2026-06-12" end="2026-06-15" />
 *     → "12–15 jun 2026"  (day bold, year muted)
 *
 *   <DateRange start="2026-06-28" end="2026-07-03" />
 *     → "28 jun – 3 jul 2026"
 *
 *   <DateRange start="2026-12-28" end="2027-01-05" />
 *     → "28 dez 2026 – 5 jan 2027"
 *
 *   <DateRange start="2026-06-01" end="2026-06-30" compact />
 *     → stacked layout with "Início:" / "Fim:" labels (for narrow containers)
 */
export default function DateRange({ start, end, compact = false, className = '' }) {
  if (!start && !end) return null;

  if (compact) {
    return (
      <div className={`flex flex-col gap-0.5 text-sm ${className}`}>
        {start && (
          <div className="flex items-center gap-1">
            <span className="shrink-0 text-xs text-gray-400">Início:</span>
            <DateDisplay value={start} className="font-medium text-gray-900" />
          </div>
        )}
        {end && (
          <div className="flex items-center gap-1">
            <span className="shrink-0 text-xs text-gray-400">Fim:</span>
            <DateDisplay value={end} className="font-medium text-gray-900" />
          </div>
        )}
      </div>
    );
  }

  const { main, year } = rangeParts(start, end);

  return (
    <span className={`whitespace-nowrap ${className}`}>
      <span className="font-medium text-gray-900">{main}</span>
      {year && <span className="text-gray-500"> {year}</span>}
    </span>
  );
}
