/**
 * Shared formatting utilities.
 */

const CURRENT_YEAR = new Date().getFullYear();
const FMT_SHORT = new Intl.DateTimeFormat('pt-PT', { day: 'numeric', month: 'short' });
const FMT_WITH_YEAR = new Intl.DateTimeFormat('pt-PT', { day: 'numeric', month: 'short', year: 'numeric' });

/**
 * Formats a date string/number to pt-PT locale.
 * Omits year if current year (e.g. "14 Jan" vs "14 Jan 2025").
 *
 * @param {string|number|null|undefined} raw
 * @returns {string}
 */
export function formatDate(raw) {
  if (!raw) return '';
  const d = new Date(String(raw).length === 10 ? `${raw}T00:00:00` : raw);
  if (isNaN(d.getTime())) return String(raw);
  return d.getFullYear() === CURRENT_YEAR ? FMT_SHORT.format(d) : FMT_WITH_YEAR.format(d);
}
