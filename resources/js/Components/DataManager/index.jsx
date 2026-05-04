import { useState, useCallback } from 'react';
import { Plus, LayoutList, Grid2X2 } from 'lucide-react';
import Modal from '@/Components/Common/Modal';
import DialogModal from '@/Components/Common/DialogModal';
import Table from '@/Components/Table/index.jsx';
import Pagination from '@/Components/Table/Pagination.jsx';
import EditPanel from './EditPanel.jsx';
import FilterBar from './filterbar.jsx';

function replaceId(url, id) {
    return url.replace(':id', id).replace('__ID__', id);
}

function buildQuery(params) {
    const s = new URLSearchParams(window.location.search);
    Object.entries(params).forEach(([k, v]) => {
        if (v === '' || v === null || v === undefined) s.delete(k);
        else s.set(k, v);
    });
    return s.toString();
}

function navigateWithQuery(params) {
    const qs = buildQuery(params);
    window.history.replaceState(null, '', window.location.pathname + (qs ? `?${qs}` : ''));
    window.location.reload();
}

export default function DataManager({
    title,
    entityName,
    items,
    columns = [],
    formSchema = [],
    routes = {},
    filterSchema = [],
    onNew = null,
    onRowClick = null,
    viewMode = 'table',
    onViewModeChange = null,
    supportKanban = false,
    children = null
}) {
    const [editItem, setEditItem] = useState(null);
    const [deleteTarget, setDeleteTarget] = useState(null);
    const [deleting, setDeleting] = useState(false);
    const [errorDialog, setErrorDialog] = useState({ open: false, title: '', description: '' });
    const hasEdit = !!routes.update;
    const dataToMap = Array.isArray(items) ? items : (items?.data ?? []);
    const name = entityName ?? title?.replace(/s$/, '') ?? 'Record';

    const confirmDelete = useCallback(async () => {
        if (!routes.destroy || !deleteTarget) return;
        setDeleting(true);

        const token = document.querySelector('meta[name="csrf-token"]')?.content;
        try {
            const url = replaceId(routes.destroy, deleteTarget);
            const res = await fetch(url, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': token ?? '', 'X-Requested-With': 'XMLHttpRequest' },
            });
            if (res.ok) {
                setDeleteTarget(null);
                setEditItem(null);
                navigateWithQuery({});
            } else {
                setErrorDialog({ open: true, title: 'Delete Failed', description: 'An error occurred while deleting this item. Please try again.' });
            }
        } catch (error) {
            setErrorDialog({ open: true, title: 'Delete Error', description: error?.message || 'An unexpected error occurred.' });
        } finally {
            setDeleting(false);
        }
    }, [routes.destroy, deleteTarget]);

    return (
        <div className="flex-1 flex flex-col h-full overflow-hidden bg-slate-900 p-6">
            {/* Top Area "Filtros" — title + search + filters + action buttons */}
            <div className="shrink-0 mb-4 flex flex-col gap-4">
                {/* Title row with optional view toggles */}
                {title && (
                    <div className="flex items-center justify-between">
                        <h2 className="text-lg font-bold text-white">{title}</h2>
                        {supportKanban && (
                            <div className="flex items-center gap-3">
                                {viewMode === 'kanban' && onNew && routes.store && (
                                    <button
                                        type="button"
                                        onClick={onNew}
                                        className="inline-flex items-center gap-1.5 rounded-lg bg-indigo-600 px-3 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 transition-colors"
                                    >
                                        <Plus className="h-4 w-4" />
                                        New {name}
                                    </button>
                                )}
                                <div className="flex items-center gap-2 rounded-lg bg-slate-800 p-1">
                                    <button
                                        onClick={() => onViewModeChange && onViewModeChange('table')}
                                        className={`inline-flex items-center gap-2 rounded-md px-3 py-2 text-sm font-medium transition-colors ${
                                            viewMode === 'table'
                                                ? 'bg-indigo-600 text-white'
                                                : 'text-slate-400 hover:text-slate-300'
                                        }`}
                                    >
                                        <LayoutList className="h-4 w-4" />
                                        Table
                                    </button>
                                    <button
                                        onClick={() => onViewModeChange && onViewModeChange('kanban')}
                                        className={`inline-flex items-center gap-2 rounded-md px-3 py-2 text-sm font-medium transition-colors ${
                                            viewMode === 'kanban'
                                                ? 'bg-indigo-600 text-white'
                                                : 'text-slate-400 hover:text-slate-300'
                                        }`}
                                    >
                                        <Grid2X2 className="h-4 w-4" />
                                        Kanban
                                    </button>
                                </div>
                            </div>
                        )}
                    </div>
                )}

                {/* Search & Filters (only show in table view) */}
                {routes.index && viewMode !== 'kanban' && (
                    <FilterBar routes={routes} filterSchema={filterSchema} columns={columns} onNew={onNew} entityName={name} />
                )}
            </div>

            {/* Table Container (only show in table view) */}
            {viewMode !== 'kanban' && (
                <div className="flex-1 flex flex-col overflow-hidden bg-slate-800 rounded-lg shadow-xl">
                    {/* Table + Edit Panel row */}
                    <div className="flex flex-1 overflow-hidden">
                        {/* Table component */}
                        <Table
                            items={items}
                            columns={columns}
                            hasEdit={hasEdit}
                            onEditItem={setEditItem}
                            onDeleteTarget={setDeleteTarget}
                            onRowClick={onRowClick}
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
                                onDelete={(id) => { setDeleteTarget(id); setEditItem(null); }}
                                onError={(title, description) => setErrorDialog({ open: true, title, description })}
                            />
                        )}
                    </div>

                    {/* Pagination (inside table container, bottom border) */}
                    {items.links && (
                        <div className="shrink-0 pt-4 mt-4 border-t border-slate-700 px-4 pb-4">
                            <Pagination links={items.links} />
                        </div>
                    )}
                </div>
            )}

            {/* Kanban View (render children when in kanban mode) */}
            {viewMode === 'kanban' && children}

            {/* Delete Confirmation Dialog */}
            <DialogModal
                open={!!deleteTarget}
                type="confirm"
                title={`Delete ${name}`}
                description={`Are you sure you want to delete this ${name?.toLowerCase()}? This action is irreversible.`}
                onClose={() => { setDeleteTarget(null); setDeleting(false); }}
                buttons={[
                    { label: 'Cancel', onClick: () => { setDeleteTarget(null); setDeleting(false); }, variant: 'secondary' },
                    { label: deleting ? 'Deleting...' : 'Delete', onClick: confirmDelete, variant: 'primary' },
                ]}
            />

            {/* Error Dialog */}
            <DialogModal
                open={errorDialog.open}
                type="error"
                title={errorDialog.title}
                description={errorDialog.description}
                onClose={() => setErrorDialog({ open: false, title: '', description: '' })}
                buttons={[{ label: 'OK', onClick: () => setErrorDialog({ open: false, title: '', description: '' }), variant: 'primary' }]}
            />
        </div>
    );
}
