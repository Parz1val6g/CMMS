import { useState, useCallback, useEffect, useRef } from 'react';
import { usePage } from '@inertiajs/react';
import { Plus, LayoutList, Grid2X2, Trash2, X } from 'lucide-react';
import { t } from '@/utils/i18n';
import DialogModal from '@/Components/Common/DialogModal';
import Table from '@/Components/Table/index.jsx';
import Pagination from '@/Components/Table/Pagination.jsx';
import EditPanel from './EditPanel.jsx';
import FilterBar from './filterbar.jsx';
import { replaceId, toQueryString } from '@/utils/url';
import { csrfHeader } from '@/utils/csrf';

/**
 * Normalize API paginated response to Inertia-compatible shape.
 * API resources return links in meta.links, Inertia expects links at top level.
 */
function normalizeResponse(data) {
    if (!data) return data;
    if (Array.isArray(data.links)) return data; // already Inertia shape
    if (data.meta?.links) {
        return { ...data, links: data.meta.links };
    }
    return data;
}

export default function DataManager({
    title,
    entityName,
    items: initialItems,
    columns = [],
    formSchema = [],
    routes = {},
    filterSchema = [],
    advancedFilterFields = [],
    onNew = null,
    onRowClick = null,
    viewMode = 'table',
    onViewModeChange = null,
    supportKanban = false,
    refreshKey = 0,
    children = null
}) {
    const [editItem, setEditItem] = useState(null);
    const [deleteTarget, setDeleteTarget] = useState(null);
    const [deleting, setDeleting] = useState(false);
    const [errorDialog, setErrorDialog] = useState({ open: false, title: '', description: '' });

    /* ── Batch selection state ────────────────────────────────────── */
    const [selectedIds, setSelectedIds] = useState(new Set());
    const [batchDeleting, setBatchDeleting] = useState(false);
    const [batchConfirm, setBatchConfirm] = useState(false);

    /* ── Sort state ───────────────────────────────────────────────── */
    const [sortBy,  setSortBy]  = useState(null);   // active column key, or null
    const [sortDir, setSortDir] = useState('asc');  // 'asc' | 'desc'

    /* ── Client-side data state ───────────────────────────────────── */
    // Keep initialItems in a ref so it never re-triggers the fetch effect
    // when the parent re-renders with a new object reference (#6)
    const initialItemsRef = useRef(initialItems);
    const [items, setItems] = useState(initialItems);
    const [loading, setLoading] = useState(false);
    const [filters, setFilters] = useState({});

    /* ── Advanced filter builder state ───────────────────────────── */
    const [advFilters, setAdvFilters] = useState([]);   // Rule[]

    /* ── Sync parent optimistic updates when no active filters ───── */
    const filtersRef = useRef(filters);
    const advFiltersRef = useRef(advFilters);
    useEffect(() => { filtersRef.current = filters; }, [filters]);
    useEffect(() => { advFiltersRef.current = advFilters; }, [advFilters]);
    useEffect(() => {
        if (initialItems === initialItemsRef.current) return;
        initialItemsRef.current = initialItems;
        const hasFilters = Object.values(filtersRef.current).some(v => v !== null && v !== undefined)
            || advFiltersRef.current.length > 0;
        if (!hasFilters) setItems(initialItems);
    // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [initialItems]);
    const [advLogic, setAdvLogic]     = useState('and'); // 'and' | 'or'

    const abortRef = useRef(null);
    const refetchRef = useRef(null);

    const { props: { activeRole } } = usePage();

    const hasEdit = !!routes.update;
    const name = entityName ?? title?.replace(/s$/, '') ?? t('common.entity_name');

    /* ── Fetch helper — called after mutations to refresh without reload (#3) ── */
    const refetch = useCallback(() => {
        if (!routes.index) return;
        const params = { ...filters, active_role: activeRole };
        if (advFilters.length > 0) {
            params.adv_filters = JSON.stringify(advFilters);
            params.adv_logic   = advLogic;
        }
        const qs = toQueryString(params);
        const url = routes.index + (qs ? `?${qs}` : '');
        setLoading(true);
        fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest', Accept: 'application/json' } })
            .then((r) => { if (!r.ok) throw new Error(); return r.json(); })
            .then((data) => setItems(normalizeResponse(data)))
            .catch(() => setItems(initialItemsRef.current))
            .finally(() => setLoading(false));
    }, [routes.index, filters, advFilters, advLogic, activeRole]);

    refetchRef.current = refetch;

    /* ── Refresh table when parent signals (refreshKey increment) ── */
    useEffect(() => {
        if (refreshKey > 0 && refetchRef.current) {
            refetchRef.current();
        }
    }, [refreshKey]);

    /* ── Fetch data from API when filters change ──────────────────── */
    useEffect(() => {
        const filterKeys = Object.keys(filters).filter((k) => filters[k] !== null && filters[k] !== undefined);
        const hasAdvFilters = advFilters.length > 0;

        // No active filters of any kind — show initial server-rendered data
        if (filterKeys.length === 0 && !hasAdvFilters) {
            setItems(initialItemsRef.current);
            return;
        }

        if (!routes.index) return;

        // Abort previous in-flight request
        if (abortRef.current) abortRef.current.abort();
        const controller = new AbortController();
        abortRef.current = controller;

        // Merge flat filters + advanced filter payload
        const params = { ...filters, active_role: activeRole };
        if (hasAdvFilters) {
            params.adv_filters = JSON.stringify(advFilters);
            params.adv_logic   = advLogic;
        }

        const qs = toQueryString(params);
        const url = routes.index + (qs ? `?${qs}` : '');

        setLoading(true);
        fetch(url, {
            signal: controller.signal,
            headers: { 'X-Requested-With': 'XMLHttpRequest', Accept: 'application/json' },
        })
            .then((r) => {
                if (!r.ok) throw new Error('Fetch failed');
                return r.json();
            })
            .then((data) => {
                setItems(normalizeResponse(data));
            })
            .catch((err) => {
                if (err.name !== 'AbortError') {
                    setItems(initialItemsRef.current);
                }
            })
            .finally(() => setLoading(false));

        return () => {
            if (abortRef.current) controller.abort();
        };
    // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [filters, advFilters, advLogic, routes.index, activeRole]);

    /* ── Filter change handler ────────────────────────────────────── */
    const handleFilterChange = useCallback((newFilters) => {
        setFilters({ ...newFilters, page: null });
    }, []);

    /* ── Advanced filter builder handler ─────────────────────────── */
    const handleAdvancedFiltersChange = useCallback((rules, logic) => {
        setAdvFilters(rules);
        setAdvLogic(logic);
        setFilters((prev) => ({ ...prev, page: null }));
    }, []);

    /* ── Page change handler ──────────────────────────────────────── */
    const handlePageChange = useCallback((page) => {
        setFilters((prev) => ({ ...prev, page }));
    }, []);

    /* ── Sort handler ─────────────────────────────────────────────── */
    const handleSort = useCallback((colKey) => {
        const nextDir = sortBy === colKey && sortDir === 'asc' ? 'desc' : 'asc';
        setSortDir(nextDir);
        setSortBy(colKey);
        setFilters((f) => ({ ...f, sort: `${colKey}:${nextDir}`, page: null }));
    }, [sortBy, sortDir]);

    /* ── Selection handlers ───────────────────────────────────────── */
    const handleToggleSelect = useCallback((id) => {
        setSelectedIds(prev => {
            const next = new Set(prev);
            if (next.has(id)) next.delete(id);
            else next.add(id);
            return next;
        });
    }, []);

    const handleToggleAll = useCallback((pageItems) => {
        setSelectedIds(prev => {
            const allSelected = pageItems.every(item => prev.has(item.id));
            if (allSelected) return new Set();
            return new Set(pageItems.map(i => i.id));
        });
    }, []);

    const clearSelection = useCallback(() => setSelectedIds(new Set()), []);

    /* ── Batch delete ─────────────────────────────────────────────── */
    const confirmBatchDelete = useCallback(async () => {
        if (!routes.destroy || selectedIds.size === 0) return;
        setBatchDeleting(true);
        const ids = [...selectedIds];

        const results = await Promise.allSettled(
            ids.map(id =>
                fetch(replaceId(routes.destroy, id), {
                    method: 'DELETE',
                    headers: { 'X-Requested-With': 'XMLHttpRequest', ...csrfHeader() },
                })
            )
        );

        const failed = results.filter(r => r.status === 'rejected' || !r.value?.ok).length;
        setBatchDeleting(false);
        setBatchConfirm(false);
        clearSelection();

        if (failed > 0) {
            setErrorDialog({
                open: true,
                title: t('pages.datamanager.batch_partial_title'),
                description: t('pages.datamanager.batch_partial_desc', { failed, total: ids.length }),
            });
        }

        refetch();
    }, [routes.destroy, selectedIds, clearSelection, refetch]);

    /* ── Delete logic ─────────────────────────────────────────────── */
    const confirmDelete = useCallback(async () => {
        if (!routes.destroy || !deleteTarget) return;
        setDeleting(true);

        try {
            const url = replaceId(routes.destroy, deleteTarget);
            const res = await fetch(url, {
                method: 'DELETE',
                headers: { 'X-Requested-With': 'XMLHttpRequest', ...csrfHeader() },
            });
            if (res.ok) {
                setDeleteTarget(null);
                setEditItem(null);
                // Refresh in-place — no full page reload (#3)
                refetch();
            } else {
                setErrorDialog({ open: true, title: t('pages.datamanager.delete_failed_title'), description: t('pages.datamanager.delete_failed_desc') });
            }
        } catch (error) {
            setErrorDialog({ open: true, title: t('pages.datamanager.delete_error_title'), description: error?.message || t('pages.datamanager.error_fallback') });
        } finally {
            setDeleting(false);
        }
    }, [routes.destroy, deleteTarget, refetch]);

    return (
        <div className="flex-1 flex flex-col h-full overflow-hidden bg-brand-white p-6">
            {/* Top Area — title + search + filters + action buttons */}
            <div className="shrink-0 mb-4 flex flex-col gap-4">
                {/* Title row with optional view toggles */}
                {title && (
                    <div className="flex items-center justify-between">
                        <h2 className="text-lg font-bold text-brand-darkest">{title}</h2>
                        {supportKanban && (
                            <div className="flex items-center gap-3">
                                {viewMode === 'kanban' && onNew && routes.store && (
                                    <button
                                        type="button"
                                        onClick={onNew}
                                        className="inline-flex items-center gap-1.5 rounded-lg bg-brand-accent px-3 py-2 text-sm font-medium text-brand-white shadow-sm hover:bg-brand-accent/90 transition-colors"
                                    >
                                        <Plus className="h-4 w-4" />
                                        {t('pages.datamanager.new_entity', { name })}
                                    </button>
                                )}
                                <div className="flex items-center gap-2 rounded-lg bg-brand-light p-1">
                                    <button
                                        onClick={() => onViewModeChange && onViewModeChange('table')}
                                        className={`inline-flex items-center gap-2 rounded-md px-3 py-2 text-sm font-medium transition-colors ${viewMode === 'table'
                                                ? 'bg-brand-accent text-brand-white'
                                                : 'text-brand-mid hover:text-brand-darkest'
                                            }`}
                                    >
                                        <LayoutList className="h-4 w-4" />
                                        {t('pages.datamanager.view_table')}
                                    </button>
                                    <button
                                        onClick={() => onViewModeChange && onViewModeChange('kanban')}
                                        className={`inline-flex items-center gap-2 rounded-md px-3 py-2 text-sm font-medium transition-colors ${viewMode === 'kanban'
                                                ? 'bg-brand-accent text-brand-white'
                                                : 'text-brand-mid hover:text-brand-darkest'
                                            }`}
                                    >
                                        <Grid2X2 className="h-4 w-4" />
                                        {t('pages.datamanager.view_kanban')}
                                    </button>
                                </div>
                            </div>
                        )}
                    </div>
                )}

                {/* Search & Filters (only show in table view) */}
                {routes.index && viewMode !== 'kanban' && (
                    <FilterBar
                        routes={routes}
                        filterSchema={filterSchema}
                        advancedFilterFields={advancedFilterFields}
                        onNew={onNew}
                        entityName={name}
                        onFilterChange={handleFilterChange}
                        onAdvancedFiltersChange={handleAdvancedFiltersChange}
                    />
                )}
            </div>

            {/* Table Container (only show in table view) */}
            {viewMode !== 'kanban' && (
                <div className="relative flex-1 flex flex-col overflow-hidden bg-brand-white rounded-lg shadow-xl">
                    {/* Loading overlay — `relative` on parent contains this correctly (#2) */}
                    {loading && (
                        <div className="absolute inset-0 z-20 flex items-center justify-center bg-brand-darkest/10 rounded-lg">
                            <div className="h-8 w-8 animate-spin rounded-full border-2 border-brand-accent border-t-transparent" />
                        </div>
                    )}

                    {/* Batch action bar — visible when rows are selected */}
                    {selectedIds.size > 0 && (
                        <div className="shrink-0 flex items-center justify-between gap-3 border-b border-brand-mid/20 bg-brand-accent/10 px-4 py-2">
                            <span className="text-sm font-medium text-brand-accent">
                                {selectedIds.size} {selectedIds.size === 1 ? t('pages.datamanager.selected_one') : t('pages.datamanager.selected_many')}
                            </span>
                            <div className="flex items-center gap-2">
                                {routes.destroy && (
                                    <button
                                        type="button"
                                        onClick={() => setBatchConfirm(true)}
                                        disabled={batchDeleting}
                                        className="inline-flex items-center gap-1.5 rounded-lg border border-red-800/50 px-3 py-1.5 text-xs font-medium text-red-400 hover:bg-red-500/10 transition-colors disabled:opacity-50"
                                    >
                                        <Trash2 className="h-3.5 w-3.5" />
                                        {batchDeleting ? t('pages.datamanager.deleting_btn') : t('pages.datamanager.batch_delete_btn', { count: selectedIds.size })}
                                    </button>
                                )}
                                <button
                                    type="button"
                                    onClick={clearSelection}
                                    className="rounded-lg p-1.5 text-brand-mid hover:bg-brand-light hover:text-brand-darkest transition-colors"
                                    aria-label={t('pages.datamanager.clear_selection')}
                                >
                                    <X className="h-4 w-4" />
                                </button>
                            </div>
                        </div>
                    )}

                    {/* Table + Edit Panel row */}
                    <div className="relative flex flex-1 overflow-hidden">
                        {/* Table component */}
                        <Table
                            items={items}
                            columns={columns}
                            hasEdit={hasEdit}
                            onEditItem={setEditItem}
                            onDeleteTarget={setDeleteTarget}
                            onRowClick={onRowClick}
                            sortBy={sortBy}
                            sortDir={sortDir}
                            onSort={handleSort}
                            selectedIds={selectedIds}
                            onToggleSelect={routes.destroy ? handleToggleSelect : null}
                            onToggleAll={routes.destroy ? handleToggleAll : null}
                        />

                        {/* Edit Panel slide-over */}
                        {hasEdit && editItem && (
                            <EditPanel
                                key={editItem.id}
                                title={title}
                                entityName={name}
                                formSchema={formSchema}
                                routes={routes}
                                selectedItem={editItem}
                                onClose={() => setEditItem(null)}
                                onSaved={refetch}
                                onDelete={(id) => { setDeleteTarget(id); setEditItem(null); }}
                                onError={(title, description) => setErrorDialog({ open: true, title, description })}
                            />
                        )}
                    </div>

                    {/* Pagination */}
                    {items?.links && (
                        <div className="shrink-0 pt-4 mt-4 border-t border-brand-mid/20 px-4 pb-4">
                            <Pagination links={items.links} onPageChange={handlePageChange} />
                        </div>
                    )}
                </div>
            )}

            {/* Child components (Modals, Drawers, etc.) — always rendered */}
            {children}

            {/* Batch Delete Confirmation Dialog */}
            <DialogModal
                open={batchConfirm}
                type="confirm"
                title={t('pages.datamanager.batch_delete_confirm_title', { count: selectedIds.size, name })}
                description={t('pages.datamanager.batch_delete_confirm_desc', { count: selectedIds.size, name: name?.toLowerCase() })}
                onClose={() => setBatchConfirm(false)}
                buttons={[
                    { label: t('pages.datamanager.cancel_btn'), onClick: () => setBatchConfirm(false), variant: 'secondary' },
                    { label: batchDeleting ? t('pages.datamanager.deleting_btn') : t('pages.datamanager.batch_delete_btn', { count: selectedIds.size }), onClick: confirmBatchDelete, variant: 'primary' },
                ]}
            />

            {/* Delete Confirmation Dialog */}
            <DialogModal
                open={!!deleteTarget}
                type="confirm"
                title={t('pages.datamanager.delete_confirm_title', { name })}
                description={t('pages.datamanager.delete_confirm_desc', { name: name?.toLowerCase() })}
                onClose={() => { setDeleteTarget(null); setDeleting(false); }}
                buttons={[
                    { label: t('pages.datamanager.cancel_btn'), onClick: () => { setDeleteTarget(null); setDeleting(false); }, variant: 'secondary' },
                    { label: deleting ? t('pages.datamanager.deleting_btn') : t('pages.datamanager.delete_btn'), onClick: confirmDelete, variant: 'primary' },
                ]}
            />

            {/* Error Dialog */}
            <DialogModal
                open={errorDialog.open}
                type="error"
                title={errorDialog.title}
                description={errorDialog.description}
                onClose={() => setErrorDialog({ open: false, title: '', description: '' })}
                buttons={[{ label: t('pages.datamanager.ok_btn'), onClick: () => setErrorDialog({ open: false, title: '', description: '' }), variant: 'primary' }]}
            />
        </div>
    );
}
