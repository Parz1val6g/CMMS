const variantStyles = {
  brand: 'bg-brand-accent/10 text-brand-accent',
  primary: 'bg-sky-100 text-sky-800',
  success: 'bg-emerald-100 text-emerald-800',
  danger: 'bg-red-100 text-red-800',
  warning: 'bg-amber-100 text-amber-800',
  info: 'bg-cyan-100 text-cyan-800',
  secondary: 'bg-brand-mid/10 text-brand-mid',
  light: 'bg-brand-light text-brand-darkest',
  dark: 'bg-brand-darkest text-brand-white',
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
