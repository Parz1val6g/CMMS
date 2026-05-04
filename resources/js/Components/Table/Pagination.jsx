import { router } from '@inertiajs/react';
import { ChevronLeft, ChevronRight } from 'lucide-react';

export default function Pagination({ links }) {
    if (!links || links.length <= 3) return null;

    const currentPage = links.find((l) => l.active)?.label ?? '?';
    const totalPages = Math.max(...links.map((l) => {
        const num = parseInt(l.label, 10);
        return Number.isNaN(num) ? 0 : num;
    }));

    return (
        <nav className="flex items-center justify-between">
            <div className="flex items-center gap-2">
                <span className="text-xs font-medium text-slate-400">
                    Page <span className="font-bold text-white">{currentPage}</span>
                    {totalPages > 0 && <span className="text-slate-500"> of {totalPages}</span>}
                </span>
            </div>

            <div className="flex items-center gap-1">
                {links.map((link, i) => {
                    if (link.label === '...') {
                        return <span key={i} className="px-2 text-xs text-slate-500">...</span>;
                    }

                    const isPrev = link.label.includes('Previous');
                    const isNext = link.label.includes('Next');

                    return (
                        <button
                            key={i}
                            type="button"
                            disabled={!link.url}
                            onClick={() => {
                                if (link.url) {
                                    const url = new URL(link.url);
                                    router.get(url.pathname + url.search, {}, { preserveState: true, replace: true });
                                }
                            }}
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
