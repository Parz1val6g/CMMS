/**
 * Shared formatting utilities.
 *
 *   formatDate(raw)             — absolute "dd-mm-yyyy"  (backward-compat alias)
 *   formatAbsolute(raw)         — absolute "dd-mm-yyyy"
 *   formatDateTime(raw)         — absolute "dd-mm-yyyy, HH:MM"
 *   formatRelative(raw)         — relative "Há 2 horas" / "Em 3 dias" (tooltip-ready)
 *   formatDateRange(start, end) — "dd-mm-yyyy – dd-mm-yyyy"
 */

const RTF = new Intl.RelativeTimeFormat('pt-PT', { numeric: 'auto' });

function parseDate(raw) {
  if (!raw) return null;
  const d = new Date(String(raw).length === 10 ? `${raw}T00:00:00` : raw);
  return isNaN(d.getTime()) ? null : d;
}

function pad(n) { return String(n).padStart(2, '0'); }

/** "dd-mm-yyyy" */
export function formatAbsolute(raw) {
  if (!raw) return '';
  const d = parseDate(raw);
  if (!d) return String(raw);
  return `${pad(d.getDate())}-${pad(d.getMonth() + 1)}-${d.getFullYear()}`;
}

/** "dd-mm-yyyy, HH:MM" */
export function formatDateTime(raw) {
  if (!raw) return '';
  const d = parseDate(raw);
  if (!d) return String(raw);
  return `${pad(d.getDate())}-${pad(d.getMonth() + 1)}-${d.getFullYear()}, ${pad(d.getHours())}:${pad(d.getMinutes())}`;
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

/** "dd-mm-yyyy – dd-mm-yyyy" */
export function formatDateRange(start, end) {
  if (!start && !end) return '';
  if (!start) return formatAbsolute(end);
  if (!end)   return formatAbsolute(start);
  return `${formatAbsolute(start)} – ${formatAbsolute(end)}`;
}

/** @deprecated Use formatAbsolute(). */
export function formatDate(raw) {
  return formatAbsolute(raw);
}
