import { Check, Minus } from 'lucide-react';

/**
 * Brand-styled checkbox — works everywhere (forms, tables, modals).
 *
 * Props:
 *   - checked      bool
 *   - onChange     (e: Event) => void
 *   - indeterminate bool — show dash instead of check (e.g. "select all" partial state)
 *   - disabled     bool
 *   - label        string — renders a clickable label next to the checkbox
 *   - id           string — used for label htmlFor
 *   - className    string — additional classes for the wrapper
 */
export default function Checkbox({
  checked = false,
  onChange,
  indeterminate = false,
  disabled = false,
  label,
  id,
  className = '',
}) {
  return (
    <label
      className={`inline-flex items-center gap-2.5 ${disabled ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer'} ${className}`}
    >
      <span className="relative inline-flex shrink-0">
        <input
          type="checkbox"
          checked={checked}
          onChange={onChange}
          disabled={disabled}
          id={id}
          className="peer sr-only"
          ref={(el) => {
            if (el) el.indeterminate = indeterminate;
          }}
        />
        <span
          className={[
            'flex h-5 w-5 items-center justify-center rounded-md border transition-all duration-150',
            checked || indeterminate
              ? 'bg-brand-accent border-brand-accent'
              : 'bg-brand-white border-brand-mid/30 hover:border-brand-mid/50',
            disabled ? '' : 'peer-focus:ring-2 peer-focus:ring-brand-accent/30 peer-focus:ring-offset-1',
          ].join(' ')}
        >
          {indeterminate ? (
            <Minus className="h-3 w-3 text-white" strokeWidth={3} />
          ) : checked ? (
            <Check className="h-3 w-3 text-white" strokeWidth={3} />
          ) : null}
        </span>
      </span>
      {label && (
        <span className="text-sm text-brand-darkest select-none">{label}</span>
      )}
    </label>
  );
}
