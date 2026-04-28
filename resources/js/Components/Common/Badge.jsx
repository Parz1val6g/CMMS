const variantStyles = {
  primary: 'bg-blue-100 text-blue-800 dark:bg-blue-900/50 dark:text-blue-300',
  success: 'bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-300',
  danger: 'bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-300',
  warning: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/50 dark:text-yellow-300',
  info: 'bg-cyan-100 text-cyan-800 dark:bg-cyan-900/50 dark:text-cyan-300',
  secondary: 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
  light: 'bg-gray-50 text-gray-800 dark:bg-gray-800 dark:text-gray-200',
  dark: 'bg-gray-800 text-white dark:bg-gray-900 dark:text-gray-100',
};

export default function Badge({ variant = 'secondary', pill = false, icon, children }) {
  const classes = [
    'inline-flex items-center gap-1 text-xs font-medium px-2 py-0.5',
    variantStyles[variant] ?? variantStyles.secondary,
    pill ? 'rounded-full' : 'rounded',
  ].join(' ');

  return (
    <span className={classes} title={icon ? children : undefined}>
      {icon ? (
        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="currentColor" viewBox="0 0 16 16" aria-hidden="true">
          {icon}
        </svg>
      ) : (
        children
      )}
    </span>
  );
}
