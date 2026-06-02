import { formatAbsolute, formatDateTime, formatRelative } from '@/utils/format';

/**
 * Renders a single date with pt-PT formatting.
 *
 *   <DateDisplay value="2026-06-12" />
 *     → "12-06-2026"
 *
 *   <DateDisplay value="2026-06-12T08:14" showTime />
 *     → "12-06-2026, 08:14"
 *
 *   <DateDisplay value="2026-06-10" relative />
 *     → "Há 2 dias" (with tooltip showing "12-06-2026")
 *
 * Golden rule: relative mode always wraps in a tooltip with the absolute date.
 */
export default function DateDisplay({ value, relative = false, showTime = false, className = '' }) {
  if (!value) return null;

  const absolute = showTime ? formatDateTime(value) : formatAbsolute(value);

  if (relative) {
    const relText = formatRelative(value);
    return (
      <span className={`relative group inline-block cursor-default ${className}`}>
        <span>{relText}</span>
        <span className="pointer-events-none absolute bottom-full left-1/2 z-50 mb-1.5 hidden -translate-x-1/2 whitespace-nowrap rounded-md bg-gray-900 px-2.5 py-1 text-xs text-white shadow-lg group-hover:block">
          {absolute}
        </span>
      </span>
    );
  }

  return <span className={className}>{absolute}</span>;
}
