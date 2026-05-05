import { useState, useEffect, useRef, useCallback } from 'react';
import { Download, Plus, SlidersHorizontal } from 'lucide-react';
import { router } from '@inertiajs/react';
import { useToast } from '@/Components/Toast/ToastContext';
import { buildQuery } from '@/utils/url';

function navigateWithQuery(params) {
    const qs = buildQuery(params);
    router.get(window.location.pathname + (qs ? `?${qs}` : ''), {}, { preserveState: true, replace: true });
}

export default function FilterBar({ routes, filterSchema = [], onNew = () => { }, entityName = 'Record' }) {
    const toast = useToast();
    const params = new URLSearchParams(window.location.search);
    const searchVal = params.get('search') ?? '';
    const sortVal = params.get('sort') ?? 'desc';
    const [search, setSearch] = useState(searchVal);
    const [showAdvanced, setShowAdvanced] = useState(
        localStorage.getItem('advancedFiltersOpen') === 'true'
    );
    const debounceRef = useRef(null);

    useEffect(() => {
        if (localStorage.getItem('advancedFiltersOpen') === 'true') {
            setShowAdvanced(true);
        }
    }, []);

    useEffect(() => {
        if (debounceRef.current) clearTimeout(debounceRef.current);
        debounceRef.current = setTimeout(() => {
            if (search !== searchVal) {
                navigateWithQuery({ search, page: null });
            }
        }, 800);
        return () => { if (debounceRef.current) clearTimeout(debounceRef.current); };
    }, [search, searchVal]);

    const hasAdvancedFilters =
        params.has('date_from') || params.has('date_to') ||
        (filterSchema ?? []).some((f) => params.has(f.name ?? f.key));
    const hasActiveFilters = params.has('search') || hasAdvancedFilters;

    const handleClearFilters = () => {
        const clearParams = {};
        (filterSchema ?? []).forEach((f) => { clearParams[f.name ?? f.key] = null; });
        clearParams.date_from = null;
        clearParams.date_to = null;
        clearParams.search = null;
        clearParams.sort = null;
        clearParams.page = null;
        navigateWithQuery(clearParams);
        setSearch('');
    };

    const handleExport = useCallback(() => {
        const pathname = window.location.pathname;
        const parts = pathname.split('/').filter(Boolean);
        const model = parts[parts.length - 1];

        const url = new URL(window.location.href);
        url.pathname = `/api/${model}/export/csv`;

        fetch(url.toString(), {
            method: 'GET',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
        })
            .then((r) => {
                if (!r.ok) throw new Error('CSV export failed');
                return r.blob();
            })
            .then((blob) => {
                const blobUrl = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = blobUrl;
                a.download = `export_${model}_${Date.now()}.csv`;
                document.body.appendChild(a);
                a.click();
                window.URL.revokeObjectURL(blobUrl);
                document.body.removeChild(a);
            })
            .catch(() => toast.error('Falha ao exportar CSV. Tente novamente.'));
    }, [toast]);

    return (
        <form
            id="filter-form"
            onSubmit={(e) => { e.preventDefault(); navigateWithQuery({ search, page: null }); }}
        >
            {/* Main Filter Row */}
            <div className="flex flex-wrap items-center gap-2">
                {/* Search */}
                <div className="relative min-w-0 flex-1" style={{ maxWidth: 280 }}>
                    <div className="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                        <svg className="h-3.5 w-3.5 text-slate-500" fill="currentColor" viewBox="0 0 16 16">
                            <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0z" />
                        </svg>
                    </div>
                    <input
                        type="text"
                        name="search"
                        value={search}
                        onChange={(e) => setSearch(e.target.value)}
                        onKeyDown={(e) => { if (e.key === 'Enter') { e.preventDefault(); navigateWithQuery({ search, page: null }); } }}
                        className="block w-full rounded-lg border border-slate-700 bg-slate-800/60 py-2 pl-9 pr-3 text-sm text-slate-200 placeholder:text-slate-500 focus:border-indigo-500 focus:ring-0"
                        placeholder="Search..."
                        aria-label="Search"
                    />
                </div>

                {/* Sort */}
                <select
                    name="sort"
                    value={sortVal}
                    onChange={(e) => navigateWithQuery({ sort: e.target.value, page: null })}
                    className="rounded-lg border border-slate-700 bg-slate-800/60 px-3 py-2 text-sm text-slate-300 focus:border-indigo-500 focus:ring-indigo-500"
                    style={{ maxWidth: 140 }}
                    aria-label="Sort by"
                >
                    <option value="desc">Newest</option>
                    <option value="asc">Oldest</option>
                </select>

                {/* Advanced Toggle */}
                <button
                    type="button"
                    onClick={() => {
                        const next = !showAdvanced;
                        setShowAdvanced(next);
                        if (next) localStorage.setItem('advancedFiltersOpen', 'true');
                        else localStorage.removeItem('advancedFiltersOpen');
                    }}
                    className={`relative inline-flex items-center gap-1.5 rounded-lg border px-3 py-2 text-sm transition-all ${showAdvanced
                        ? 'border-indigo-600 bg-indigo-600/20 text-indigo-300'
                        : 'border-slate-700 bg-slate-800/60 text-slate-400 hover:bg-slate-700'
                        }`}
                    title="Toggle advanced filters"
                >
                    <SlidersHorizontal className="h-3.5 w-3.5" />
                    Advanced
                    {hasAdvancedFilters && (
                        <span className="absolute -right-1.5 -top-1.5 flex h-3.5 w-3.5 items-center justify-center rounded-full bg-indigo-500 text-[9px] font-bold text-white ring-2 ring-slate-900">
                            !
                        </span>
                    )}
                </button>

                {/* Right side actions */}
                <div className="ml-auto flex flex-wrap items-center gap-2">
                    <button
                        type="button"
                        onClick={handleExport}
                        className="inline-flex items-center gap-1.5 rounded-lg border border-slate-700 bg-slate-800/60 px-3 py-2 text-sm text-slate-400 hover:bg-slate-700 transition-colors"
                        title="Export table to CSV"
                    >
                        <Download className="h-3.5 w-3.5" />
                        Export CSV
                    </button>

                    {routes.store && (
                        <button
                            type="button"
                            className="inline-flex items-center gap-1.5 rounded-lg bg-indigo-600 px-3 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 transition-colors"
                            onClick={onNew}
                            title="Create new record"
                        >
                            <Plus className="h-3.5 w-3.5" />
                            New {entityName}
                        </button>
                    )}
                </div>
            </div>

            {/* Active Filters Display */}
            {hasActiveFilters && (
                <div className="mt-2 flex flex-wrap items-center gap-2">
                    {params.get('search') && (
                        <span className="inline-flex items-center gap-1 rounded bg-blue-500/10 px-2 py-0.5 text-xs font-medium text-blue-300">
                            Search: <strong>{params.get('search')}</strong>
                        </span>
                    )}
                    {(params.get('date_from') || params.get('date_to')) && (
                        <span className="inline-flex items-center gap-1 rounded bg-yellow-500/10 px-2 py-0.5 text-xs font-medium text-yellow-300">
                            Date: <strong>{params.get('date_from') ?? '...'} → {params.get('date_to') ?? '...'}</strong>
                        </span>
                    )}
                </div>
            )}

            {/* Advanced Filters */}
            {showAdvanced && (filterSchema ?? []).length > 0 && (
                <div className="mt-3 border-t border-slate-700 pt-3">
                    <div className="grid grid-cols-2 gap-3 md:grid-cols-4">
                        {(filterSchema ?? []).map((filter, i) => (
                            <div key={i}>
                                <label className="mb-1 block text-xs font-bold text-slate-400">{filter.label}</label>
                                {filter.type === 'text' ? (
                                    <input
                                        type="text"
                                        name={filter.name ?? filter.key}
                                        defaultValue={params.get(filter.name ?? filter.key) ?? ''}
                                        placeholder={filter.placeholder ?? ''}
                                        onChange={(e) => navigateWithQuery({ [filter.name ?? filter.key]: e.target.value, page: null })}
                                        className="block w-full rounded-lg border border-slate-700 bg-slate-800/60 px-3 py-2 text-sm text-slate-200 placeholder:text-slate-500 focus:border-indigo-500 focus:ring-indigo-500"
                                    />
                                ) : (
                                    <select
                                        name={filter.name ?? filter.key}
                                        defaultValue={params.get(filter.name ?? filter.key) ?? ''}
                                        onChange={(e) => navigateWithQuery({ [filter.name ?? filter.key]: e.target.value, page: null })}
                                        className="block w-full rounded-lg border border-slate-700 bg-slate-800/60 px-3 py-2 text-sm text-slate-200 focus:border-indigo-500 focus:ring-indigo-500"
                                    >
                                        <option value="">All</option>
                                        {(filter.options ?? []).map((opt, j) => (
                                            <option key={j} value={opt.value}>{opt.label}</option>
                                        ))}
                                    </select>
                                )}
                            </div>
                        ))}

                        <div>
                            <label className="mb-1 block text-xs font-bold text-slate-400">From Date</label>
                            <input
                                type="date"
                                name="date_from"
                                defaultValue={params.get('date_from') ?? ''}
                                onChange={(e) => navigateWithQuery({ date_from: e.target.value, page: null })}
                                className="block w-full rounded-lg border border-slate-700 bg-slate-800/60 px-3 py-2 text-sm text-slate-200 focus:border-indigo-500 focus:ring-indigo-500"
                                aria-label="Filter by start date"
                            />
                        </div>

                        <div>
                            <label className="mb-1 block text-xs font-bold text-slate-400">To Date</label>
                            <input
                                type="date"
                                name="date_to"
                                defaultValue={params.get('date_to') ?? ''}
                                onChange={(e) => navigateWithQuery({ date_to: e.target.value, page: null })}
                                className="block w-full rounded-lg border border-slate-700 bg-slate-800/60 px-3 py-2 text-sm text-slate-200 focus:border-indigo-500 focus:ring-indigo-500"
                                aria-label="Filter by end date"
                            />
                        </div>

                        <div className="flex items-end gap-2">
                            <button
                                type="submit"
                                className="flex-1 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 transition-colors"
                            >
                                Apply
                            </button>
                            <button
                                type="button"
                                onClick={handleClearFilters}
                                className="flex-1 rounded-lg border border-slate-700 bg-slate-800/60 px-4 py-2 text-sm font-medium text-slate-400 hover:bg-slate-700 transition-colors"
                            >
                                Clear
                            </button>
                        </div>
                    </div>
                </div>
            )}
        </form>
    );
}
