import { ChevronLeft, ChevronRight } from 'lucide-react';
import { t } from '@/utils/i18n';

export default function Pagination({ links, onPageChange }) {
    if (!links || links.length <= 3) return null;

    const currentPage = links.find((l) => l.active)?.label ?? '?';
    const totalPages = Math.max(...links.map((l) => {
        const num = parseInt(l.label, 10);
        return Number.isNaN(num) ? 0 : num;
    }));

    const handleClick = (link) => {
        if (!link.url || !onPageChange) return;
        const url = new URL(link.url);
        const page = url.searchParams.get('page') || '1';
        onPageChange(parseInt(page, 10));
    };

    return (
        <nav className="flex items-center justify-between">
            <div className="flex items-center gap-2">
                <span className="text-xs font-medium text-slate-400">
                    {t('pages.table.page_label')} <span className="font-bold text-white">{currentPage}</span>
                    {totalPages > 0 && <span className="text-slate-500"> {t('pages.table.of_label')} {totalPages}</span>}
                </span>
            </div>

            <div className="flex items-center gap-1">
                {links.map((link, i) => {
                    // Use stable keys: prev/next by position, pages by label (#7)
                    const key = i === 0 ? '__prev' : i === links.length - 1 ? '__next' : `page-${link.label}`;

                    if (link.label === '...') {
                        return <span key={`ellipsis-${i}`} className="px-2 text-xs text-slate-500">...</span>;
                    }

                    const isPrev = i === 0;
                    const isNext = i === links.length - 1;

                    return (
                        <button
                            key={key}
                            type="button"
                            disabled={!link.url}
                            onClick={() => handleClick(link)}
                            className={`inline-flex h-7 w-7 items-center justify-center rounded-lg text-xs font-medium transition-colors ${link.active
                                ? 'bg-indigo-600 text-white'
                                : link.url
                                    ? 'text-slate-400 hover:bg-slate-700'
                                    : 'cursor-not-allowed text-slate-600'
                                }`}
                        >
                            {isPrev ? <ChevronLeft className="h-3.5 w-3.5" />
                                : isNext ? <ChevronRight className="h-3.5 w-3.5" />
                                    : link.label}
                        </button>
                    );
                })}
            </div>
        </nav>
    );
}
