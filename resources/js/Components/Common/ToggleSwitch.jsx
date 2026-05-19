import { useId } from 'react';

/**
 * A reusable, accessible toggle switch component.
 *
 * Uses `<button role="switch" aria-checked>` for proper a11y.
 * Supports checked, onChange, disabled, and id props.
 */
export default function ToggleSwitch({ checked = false, onChange, disabled = false, id }) {
  const uid = useId();
  const switchId = id ?? uid;

  return (
    <button
      type="button"
      role="switch"
      id={switchId}
      aria-checked={checked}
      disabled={disabled}
      onClick={() => onChange?.(!checked)}
      className={`
        relative inline-flex h-5 w-9 shrink-0 cursor-pointer items-center rounded-full
        transition-colors duration-200 ease-in-out
        focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-accent focus-visible:ring-offset-2
        ${checked ? 'bg-brand-accent' : 'bg-brand-mid/30'}
        ${disabled ? 'cursor-not-allowed opacity-50' : ''}
      `}
    >
      <span
        className={`
          inline-block h-4 w-4 rounded-full bg-white shadow-sm
          transition-transform duration-200 ease-in-out
          ${checked ? 'translate-x-[18px]' : 'translate-x-0.5'}
        `}
      />
    </button>
  );
}
