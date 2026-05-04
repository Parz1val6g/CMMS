import Row from './Row.jsx';
import EmptyState from './EmptyState.jsx';

export default function Table({ items, columns, hasEdit, onEditItem, onDeleteTarget, onRowClick, loading = false }) {
    const dataToMap = Array.isArray(items) ? items : (items?.data ?? []);

    if (!dataToMap || dataToMap.length === 0) {
        return <EmptyState title="No records found" description="Try adjusting your search or filters" />;
    }

    return (
        <>
            <div className="flex-1 overflow-y-auto">
                <table className="min-w-full table-auto text-sm">
                    <thead className="sticky top-0 z-10 bg-slate-800/90 backdrop-blur">
                        <tr>
                            {columns.map((col, i) => (
                                <th key={i} className="whitespace-nowrap px-4 py-3 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider" scope="col">
                                    {col.label ?? ''}
                                </th>
                            ))}
                            {hasEdit && (
                                <th className="whitespace-nowrap px-4 py-3 text-right text-xs font-semibold text-slate-400 uppercase tracking-wider" scope="col">
                                    Actions
                                </th>
                            )}
                        </tr>
                    </thead>
                    <tbody className="divide-y divide-slate-700/50">
                        {dataToMap.map((item) => (
                            <Row
                                key={item.id}
                                item={item}
                                columns={columns}
                                hasEdit={hasEdit}
                                onEdit={onEditItem}
                                onDelete={onDeleteTarget}
                                onRowClick={onRowClick}
                            />
                        ))}
                    </tbody>
                </table>
            </div>
        </>
    );
}
