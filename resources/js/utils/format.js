/**
 * Shared formatting utilities — pt-PT locale.
 *
 *   formatDate(raw)           — absolute "12 jun 2026"  (backward-compat alias)
 *   formatAbsolute(raw)       — absolute "12 jun 2026"
 *   formatDateTime(raw)       — absolute "12 jun 2026, 08:14"
 *   formatRelative(raw)       — relative "Há 2 horas" / "Em 3 dias" (tooltip-ready)
 *   formatDateRange(start, end) — smart range string with deduplication
 */

const FMT_DATE_PARTS = new Intl.DateTimeFormat('pt-PT', {
  day: 'numeric', month: 'short', year: 'numeric',
});
const FMT_DATETIME_PARTS = new Intl.DateTimeFormat('pt-PT', {
  day: 'numeric', month: 'short', year: 'numeric',
  hour: '2-digit', minute: '2-digit',
});
const RTF = new Intl.RelativeTimeFormat('pt-PT', { numeric: 'auto' });

function parseDate(raw) {
  if (!raw) return null;
  const d = new Date(String(raw).length === 10 ? `${raw}T00:00:00` : raw);
  return isNaN(d.getTime()) ? null : d;
}

function toParts(fmt, date) {
  return fmt.formatToParts(date).reduce((acc, p) => ({ ...acc, [p.type]: p.value }), {});
}

function stripDot(s) { return s.replace(/\./g, ''); }

/** "12 jun 2026" */
export function formatAbsolute(raw) {
  if (!raw) return '';
  const d = parseDate(raw);
  if (!d) return String(raw);
  const p = toParts(FMT_DATE_PARTS, d);
  return `${p.day} ${stripDot(p.month)} ${p.year}`;
}

/** "12 jun 2026, 08:14" */
export function formatDateTime(raw) {
  if (!raw) return '';
  const d = parseDate(raw);
  if (!d) return String(raw);
  const p = toParts(FMT_DATETIME_PARTS, d);
  return `${p.day} ${stripDot(p.month)} ${p.year}, ${p.hour}:${p.minute}`;
}

/**
 * "Há 2 horas" / "Em 3 dias" — capitalised.
 * Falls back to formatAbsolute when > 6 months away.
 */
export function formatRelative(raw) {
  if (!raw) return '';
  const d = parseDate(raw);
  if (!d) return String(raw);
  const diffMs   = d.getTime() - Date.now();
  const absDays  = Math.abs(diffMs) / 86400000;
  if (absDays > 180) return formatAbsolute(raw);
  const diffSec  = Math.round(diffMs / 1000);
  const diffMin  = Math.round(diffMs / 60000);
  const diffHour = Math.round(diffMs / 3600000);
  const diffDay  = Math.round(diffMs / 86400000);
  const diffWeek = Math.round(diffMs / 604800000);
  const diffMon  = Math.round(diffMs / 2592000000);
  let rel;
  if (Math.abs(diffSec)  <  60) rel = RTF.format(diffSec,  'second');
  else if (Math.abs(diffMin)  <  60) rel = RTF.format(diffMin,  'minute');
  else if (Math.abs(diffHour) <  24) rel = RTF.format(diffHour, 'hour');
  else if (Math.abs(diffDay)  <   7) rel = RTF.format(diffDay,  'day');
  else if (Math.abs(diffWeek) <   5) rel = RTF.format(diffWeek, 'week');
  else                               rel = RTF.format(diffMon,  'month');
  return rel.charAt(0).toUpperCase() + rel.slice(1);
}

/**
 * Smart range string with deduplication:
 *   Same month+year:      "12–15 jun 2026"
 *   Diff month, same year: "28 jun – 3 jul 2026"
 *   Diff years:           "28 dez 2026 – 5 jan 2027"
 */
export function formatDateRange(start, end) {
  if (!start && !end) return '';
  if (!start) return formatAbsolute(end);
  if (!end)   return formatAbsolute(start);
  const s = parseDate(start);
  const e = parseDate(end);
  if (!s || !e) return `${formatAbsolute(start)} – ${formatAbsolute(end)}`;
  const sp = toParts(FMT_DATE_PARTS, s);
  const ep = toParts(FMT_DATE_PARTS, e);
  const sm = stripDot(sp.month);
  const em = stripDot(ep.month);
  if (sp.year === ep.year && sp.month === ep.month) {
    return `${sp.day}–${ep.day} ${sm} ${sp.year}`;
  }
  if (sp.year === ep.year) {
    return `${sp.day} ${sm} – ${ep.day} ${em} ${sp.year}`;
  }
  return `${sp.day} ${sm} ${sp.year} – ${ep.day} ${em} ${ep.year}`;
}

/** @deprecated Use formatAbsolute(). */
export function formatDate(raw) {
  return formatAbsolute(raw);
}
