const variantStyles = {
  primary: 'bg-blue-100 text-blue-800',
  success: 'bg-green-100 text-green-800',
  danger: 'bg-red-100 text-red-800',
  warning: 'bg-yellow-100 text-yellow-800',
  info: 'bg-cyan-100 text-cyan-800',
  secondary: 'bg-gray-100 text-gray-800',
  light: 'bg-gray-50 text-gray-800',
  dark: 'bg-gray-800 text-white',
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
