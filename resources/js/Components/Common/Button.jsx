import { Link } from '@inertiajs/react';

const variantStyles = {
  primary: 'bg-indigo-600 text-white hover:bg-indigo-700 focus:ring-indigo-500 shadow-sm',
  secondary: 'bg-white text-gray-700 border border-gray-300 hover:bg-gray-50 focus:ring-indigo-500 dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600 dark:hover:bg-gray-600',
  success: 'bg-green-600 text-white hover:bg-green-700 focus:ring-green-500 shadow-sm',
  danger: 'bg-red-600 text-white hover:bg-red-700 focus:ring-red-500 shadow-sm',
  warning: 'bg-yellow-500 text-white hover:bg-yellow-600 focus:ring-yellow-500 shadow-sm',
  info: 'bg-cyan-600 text-white hover:bg-cyan-700 focus:ring-cyan-500 shadow-sm',
  light: 'bg-gray-100 text-gray-800 hover:bg-gray-200 focus:ring-gray-500',
  dark: 'bg-gray-800 text-white hover:bg-gray-900 focus:ring-gray-700 shadow-sm',
  link: 'text-indigo-600 hover:text-indigo-800 underline-offset-2 hover:underline dark:text-indigo-400 dark:hover:text-indigo-300',
  'outline-primary': 'border border-indigo-600 text-indigo-600 hover:bg-indigo-50 focus:ring-indigo-500 dark:border-indigo-400 dark:text-indigo-400 dark:hover:bg-indigo-900/30',
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
    'inline-flex items-center justify-center font-medium rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 dark:focus:ring-offset-gray-900',
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
