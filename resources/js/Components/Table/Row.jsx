import { Pencil, Trash2 } from 'lucide-react';

// Helper to resolve nested object properties
function resolveValue(item, key) {
    if (!key || !item) return '';
    const parts = key.split('.');
    let val = item;
    for (const p of parts) {
        if (val === null || val === undefined) return '';
        val = val[p];
    }
    if (val !== null && val !== undefined && typeof val === 'object' && !Array.isArray(val)) {
        return val.name ?? val.process ?? val.label ?? '';
    }
    return val ?? '';
}

const BADGE_COLORS = {
    urgent: 'bg-red-500/20 text-red-300',
    critical: 'bg-red-500/20 text-red-300',
    high: 'bg-orange-500/20 text-orange-300',
    low: 'bg-teal-500/20 text-teal-300',
    pending: 'bg-yellow-500/20 text-yellow-300',
    in_progress: 'bg-blue-500/20 text-blue-300',
    active: 'bg-blue-500/20 text-blue-300',
    completed: 'bg-green-500/20 text-green-300',
    normal: 'bg-green-500/20 text-green-300',
    done: 'bg-green-500/20 text-green-300',
    finished: 'bg-green-500/20 text-green-300',
    cancelled: 'bg-slate-500/20 text-slate-400',
    canceled: 'bg-slate-500/20 text-slate-400',
    default: 'bg-slate-500/20 text-slate-400',
};

function badgeColor(value) {
    return BADGE_COLORS[value?.toLowerCase()] ?? BADGE_COLORS.default;
}

const TRUNCATE_KEYS = new Set(['description', 'process', 'notes', 'comments', 'remarks', 'observations']);

function renderCell(item, col) {
    if (col.render) return col.render(item);

    const raw = resolveValue(item, col.key);
    const isStatusOrPriority = col.key === 'status' || col.key === 'priority';
    const isLongText = TRUNCATE_KEYS.has(col.key);

    if (isStatusOrPriority) {
        return (
            <span className={`inline-block rounded-full px-2.5 py-0.5 text-xs font-semibold ${badgeColor(raw)}`}>
                {raw}
            </span>
        );
    }

    if (isLongText) {
        return <span className="block max-w-xs truncate text-slate-300">{raw}</span>;
    }

    return <span className="text-slate-300">{raw}</span>;
}

export default function Row({ item, columns, hasEdit, onEdit, onDelete, onRowClick }) {
    return (
        <tr
            className={`hover:bg-slate-700/30 transition-colors ${onRowClick ? 'cursor-pointer' : ''}`}
            onClick={() => onRowClick?.(item)}
        >
            {columns.map((col, i) => (
                <td key={i} className="whitespace-nowrap px-4 py-2.5 text-sm text-slate-300">
                    {renderCell(item, col)}
                </td>
            ))}
            {hasEdit && (
                <td className="whitespace-nowrap px-4 py-2.5 text-right">
                    <div className="inline-flex items-center gap-1">
                        <button
                            type="button"
                            className="rounded-lg p-1.5 text-slate-500 hover:bg-indigo-500/20 hover:text-indigo-400 transition-colors"
                            onClick={(e) => { e.stopPropagation(); onEdit(item); }}
                            title="Edit"
                        >
                            <Pencil className="h-4 w-4" />
                        </button>
                        {onDelete && (
                            <button
                                type="button"
                                className="rounded-lg p-1.5 text-slate-500 hover:bg-red-500/20 hover:text-red-400 transition-colors"
                                onClick={(e) => { e.stopPropagation(); onDelete(item.id); }}
                                title="Delete"
                            >
                                <Trash2 className="h-4 w-4" />
                            </button>
                        )}
                    </div>
                </td>
            )}
        </tr>
    );
}
