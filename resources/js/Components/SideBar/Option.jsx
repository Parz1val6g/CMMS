import { memo } from 'react';
import { Link, usePage } from '@inertiajs/react';
import { useMemo } from 'react';

function Option({ item }) {
    const { url } = usePage();

    const isActive = useMemo(() => {
        const path = item.href === '/dashboard' ? '/' : item.href;
        return url === path || url.startsWith(path + '/');
    }, [url, item.href]);

    return (
        <Link
            href={item.href}
            className={`flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition-colors ${isActive
                    ? 'bg-brand-accent text-brand-light shadow-sm'
                    : 'text-brand-light hover:bg-brand-darkest/50 hover:text-brand-white'
                }`}
        >
            <svg className="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
                <path strokeLinecap="round" strokeLinejoin="round" d={item.icon} />
            </svg>
            <span className="truncate">{item.label}</span>
        </Link>
    );
}

export default memo(Option, (prev, next) => prev.item.href === next.item.href);
