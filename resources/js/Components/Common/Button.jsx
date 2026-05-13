import { Link } from '@inertiajs/react';

const variantStyles = {
  primary: 'bg-brand-accent text-brand-white hover:bg-brand-accent/90 focus:ring-brand-accent shadow-sm',
  secondary: 'bg-brand-white text-brand-darkest border border-brand-mid/20 hover:bg-brand-light focus:ring-brand-accent',
  success: 'bg-green-600 text-white hover:bg-green-700 focus:ring-green-500 shadow-sm',
  danger: 'bg-red-600 text-white hover:bg-red-700 focus:ring-red-500 shadow-sm',
  warning: 'bg-yellow-500 text-white hover:bg-yellow-600 focus:ring-yellow-500 shadow-sm',
  info: 'bg-cyan-600 text-white hover:bg-cyan-700 focus:ring-cyan-500 shadow-sm',
  light: 'bg-gray-100 text-gray-800 hover:bg-gray-200 focus:ring-gray-500',
  dark: 'bg-gray-800 text-white hover:bg-gray-900 focus:ring-gray-700 shadow-sm',
  link: 'text-brand-accent hover:text-brand-accent/80 underline-offset-2 hover:underline',
  'outline-primary': 'border border-brand-accent text-brand-accent hover:bg-brand-accent/5 focus:ring-brand-accent',
};

const sizeStyles = {
  sm: 'px-2.5 py-1.5 text-xs',
  md: 'px-4 py-2 text-sm',
  lg: 'px-6 py-3 text-base',
};

export default function Button({
  variant = 'secondary',
  size = 'md',
  type = 'button',
  href,
  disabled = false,
  loading = false,
  icon,
  className = '',
  children,
  ...props
}) {
  const cls = [
    'inline-flex items-center justify-center font-medium rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2',
    variantStyles[variant] ?? variantStyles.secondary,
    sizeStyles[size] ?? sizeStyles.md,
    disabled && 'pointer-events-none opacity-50',
    loading && 'pointer-events-none opacity-70',
    className,
  ]
    .filter(Boolean)
    .join(' ');

  if (href && !disabled) {
    return (
      <Link href={href} className={cls} {...props}>
        {icon && <span className="mr-2 -ml-0.5">{icon}</span>}
        {children}
      </Link>
    );
  }

  return (
    <button type={type} className={cls} disabled={disabled || loading} {...props}>
      {loading && (
        <svg className="mr-2 -ml-0.5 h-4 w-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
          <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4" />
          <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
        </svg>
      )}
      {!loading && icon && <span className="mr-2 -ml-0.5">{icon}</span>}
      {children}
    </button>
  );
}
