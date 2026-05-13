import { ChevronUp, ChevronDown, ChevronsUpDown } from 'lucide-react';
import Row from './Row.jsx';
import EmptyState from './EmptyState.jsx';
import { t } from '@/utils/i18n';

function SortIcon({ colKey, sortBy, sortDir }) {
    if (sortBy !== colKey) {
        return <ChevronsUpDown className="h-3.5 w-3.5 text-brand-mid group-hover:text-brand-darkest/60 transition-colors flex-shrink-0" />;
    }
    return sortDir === 'asc'
        ? <ChevronUp   className="h-3.5 w-3.5 text-brand-accent flex-shrink-0" />
        : <ChevronDown className="h-3.5 w-3.5 text-brand-accent flex-shrink-0" />;
}

export default function Table({
    items,
    columns,
    hasEdit,
    onEditItem,
    onDeleteTarget,
    onRowClick,
    sortBy      = null,
    sortDir     = 'asc',
    onSort      = null,
    selectedIds = new Set(),
    onToggleSelect  = null,
    onToggleAll     = null,
}) {
    const dataToMap = Array.isArray(items) ? items : (items?.data ?? []);

    if (!dataToMap || dataToMap.length === 0) {
        return <EmptyState title={t('pages.table.empty_title')} description={t('pages.table.empty_desc')} />;
    }

    const allSelected = dataToMap.length > 0 && dataToMap.every(item => selectedIds.has(item.id));
    const someSelected = !allSelected && dataToMap.some(item => selectedIds.has(item.id));
    const showCheckboxes = !!onToggleSelect;

    return (
        <div className="flex-1 overflow-x-auto overflow-y-auto">
            <table className="min-w-full table-auto text-sm">
                <thead className="sticky top-0 z-10 bg-brand-light backdrop-blur">
                    <tr>
                        {showCheckboxes && (
                            <th className="w-10 px-4 py-2" scope="col">
                                <input
                                    type="checkbox"
                                    className="h-4 w-4 rounded border-brand-mid/20 bg-brand-white text-brand-accent focus:ring-brand-accent focus:ring-offset-brand-light"
                                    checked={allSelected}
                                    ref={el => { if (el) el.indeterminate = someSelected; }}
                                    onChange={() => onToggleAll?.(dataToMap)}
                                    aria-label={t('pages.table.select_all')}
                                />
                            </th>
                        )}
                        {columns.map((col, i) => {
                            const isSortable = col.sortable && !!onSort;
                            const isActive   = sortBy === col.key;
                            return (
                                <th
                                    key={i}
                                    scope="col"
                                    onClick={isSortable ? () => onSort(col.key) : undefined}
                                    className={[
                                        'whitespace-nowrap px-4 py-2 text-left text-xs font-semibold uppercase tracking-wider select-none',
                                        isSortable ? 'cursor-pointer group transition-colors hover:bg-brand-light' : '',
                                        isActive   ? 'text-brand-accent' : 'text-brand-mid',
                                    ].join(' ')}
                                >
                                    <span className="inline-flex items-center gap-1.5">
                                        {col.label ?? ''}
                                        {isSortable && <SortIcon colKey={col.key} sortBy={sortBy} sortDir={sortDir} />}
                                    </span>
                                </th>
                            );
                        })}
                        {hasEdit && (
                            <th className="whitespace-nowrap px-4 py-2 text-right text-xs font-semibold text-brand-mid uppercase tracking-wider" scope="col">
                                {t('pages.table.actions_col')}
                            </th>
                        )}
                    </tr>
                </thead>
                <tbody className="divide-y divide-brand-mid/10">
                    {dataToMap.map((item) => (
                        <Row
                            key={item.id}
                            item={item}
                            columns={columns}
                            hasEdit={hasEdit}
                            onEdit={onEditItem}
                            onDelete={onDeleteTarget}
                            onRowClick={onRowClick}
                            selected={selectedIds.has(item.id)}
                            onToggleSelect={onToggleSelect}
                        />
                    ))}
                </tbody>
            </table>
        </div>
    );
}
