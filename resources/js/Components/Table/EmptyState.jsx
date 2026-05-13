import { Link } from '@inertiajs/react';
import { t } from '@/utils/i18n';

export default function EmptyState({ title, description, icon, action, actionText }) {
  return (
    <div className="flex flex-1 flex-col items-center justify-center px-5 py-12 text-center">
      {icon ? (
        <div className="mb-3 text-5xl text-brand-mid">{icon}</div>
      ) : (
        <svg
          xmlns="http://www.w3.org/2000/svg"
          width="48"
          height="48"
          fill="currentColor"
          className="mb-3 text-brand-mid"
          viewBox="0 0 16 16"
          aria-hidden="true"
        >
          <path d="M14 1a1 1 0 0 1 1 1v12a1 1 0 0 1-1 1H2a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h12zM2 0a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2H2z" />
          <path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4z" />
        </svg>
      )}

      <h5 className="mb-1 font-semibold text-brand-darkest">{title ?? t('pages.table.empty_title')}</h5>
      <p className="text-sm text-brand-mid">{description ?? t('pages.table.empty_desc')}</p>

      {action && actionText && (
        <Link href={action} className="mt-3 inline-flex items-center rounded-lg bg-brand-accent px-4 py-2 text-sm font-medium text-brand-white shadow-sm hover:bg-brand-accent/90 transition-colors">
          {actionText}
        </Link>
      )}
    </div>
  );
}
