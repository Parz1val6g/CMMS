import { memo } from 'react';
import { Pencil, Trash2 } from 'lucide-react';
import { labelFor, badgeStyle } from '@/utils/enums';
import { formatDate } from '@/utils/format';

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


// Columns whose content is long text — truncated with a native tooltip (Fix 1)
const TRUNCATE_KEYS = new Set(['description', 'process', 'notes', 'comments', 'remarks', 'observations']);

// Columns that contain raw dates — formatted to PT-PT (Fix 2)
const DATE_KEYS = new Set(['created_at', 'updated_at', 'execution_date', 'last_revision_date', 'next_revision_date', 'completed_at', 'next_revision']);

// Columns that represent an assigned user — rendered as an avatar (Fix 5)
const AVATAR_KEYS = new Set(['manager', 'worker', 'assigned_to', 'responsible', 'supervisor', 'head']);

// Columns that hold auto-reference codes — rendered in indigo mono font
const REFERENCE_KEYS = new Set(['reference', 'process']);

/** Check if a column key represents a reference/process value (supports dot-notation) */
function isRefKey(key) {
    return REFERENCE_KEYS.has(key)
        || key.endsWith('.reference')
        || key.endsWith('.process');
}

// Fix 2 — PT-PT date formatter (extracted to utils/format.js)

// Fix 5 — Avatar circle with initials and accessible labels
function AvatarInitial({ value }) {
    const name = typeof value === 'object' ? (value?.name ?? '') : String(value ?? '');
    if (!name) return <span className="text-slate-300">{'—'}</span>;
    const initials = name.split(' ').filter(Boolean).slice(0, 2).map(w => w[0].toUpperCase()).join('');
    const label = 'Atribuído a: ' + name;
    return (
        <span
            className="inline-flex items-center justify-center w-7 h-7 rounded-full bg-indigo-600 text-white text-xs font-bold"
            title={label}
            aria-label={label}
        >
            {initials}
        </span>
    );
}

function renderCell(item, col) {
    if (col.render) return col.render(item);

    const raw = resolveValue(item, col.key);
    const isStatusOrPriority = col.key === 'status' || col.key === 'priority';
    const isLongText  = TRUNCATE_KEYS.has(col.key);
    const isDate      = DATE_KEYS.has(col.key);
    const isAvatar    = AVATAR_KEYS.has(col.key);
    const isReference = isRefKey(col.key);

    // Reference / process codes — indigo mono badge
    if (isReference) {
        return (
            <span className="font-mono font-bold text-indigo-400 text-xs tracking-wide">
                {raw || '—'}
            </span>
        );
    }

    if (isStatusOrPriority) {
        return (
            <span className={'inline-block rounded-full px-2 py-0.5 text-xs font-semibold ' + badgeStyle(raw)}>
                {labelFor(raw)}
            </span>
        );
    }

    // Fix 2 — human-readable PT-PT date
    if (isDate) {
        return <span className="text-slate-300">{formatDate(raw)}</span>;
    }

    // Fix 5 — user avatar with initials
    if (isAvatar) {
        return <AvatarInitial value={item[col.key]} />;
    }

    // Fix 1 — truncated with full-text native tooltip
    if (isLongText) {
        const fullText = raw != null ? String(raw) : '';
        return (
            <span className="block max-w-xs truncate text-slate-300" title={fullText}>
                {raw}
            </span>
        );
    }

    return <span className="text-slate-300">{raw}</span>;
}

function Row({ item, columns, hasEdit, onEdit, onDelete, onRowClick, selected = false, onToggleSelect = null }) {
    const showCheckbox = !!onToggleSelect;

    return (
        <tr
            className={[
                'transition-colors',
                selected ? 'bg-indigo-500/10' : 'hover:bg-slate-700/30',
                onRowClick || showCheckbox ? 'cursor-pointer' : '',
            ].join(' ')}
            onClick={() => {
                // Drawer takes priority; fall back to row-select only when no drawer is wired
                if (onRowClick) onRowClick(item);
                else if (showCheckbox) onToggleSelect(item.id);
            }}
        >
            {showCheckbox && (
                <td className="w-10 px-4 py-2" onClick={e => e.stopPropagation()}>
                    <input
                        type="checkbox"
                        className="h-4 w-4 rounded border-slate-600 bg-slate-700 text-indigo-500 focus:ring-indigo-500 focus:ring-offset-slate-800"
                        checked={selected}
                        onChange={() => onToggleSelect(item.id)}
                        aria-label={'Selecionar ' + (item.process ?? item.name ?? item.id)}
                    />
                </td>
            )}
            {columns.map((col, i) => (
                <td
                    key={col.key ?? i}
                    className={'px-4 py-2 text-sm text-slate-300 ' + (TRUNCATE_KEYS.has(col.key) ? '' : 'whitespace-nowrap')}
                >
                    {renderCell(item, col)}
                </td>
            ))}
            {hasEdit && (
                <td className="whitespace-nowrap px-4 py-2 text-right">
                    <div className="inline-flex items-center gap-1">
                        <button
                            type="button"
                            className="rounded-lg p-1.5 text-slate-500 hover:bg-indigo-500/20 hover:text-indigo-400 transition-colors"
                            onClick={(e) => { e.stopPropagation(); onEdit(item); }}
                            title="Editar"
                            aria-label={'Editar ' + (item.process ?? item.name ?? '')}
                        >
                            <Pencil className="h-4 w-4" />
                        </button>
                        {onDelete && (
                            <button
                                type="button"
                                className="rounded-lg p-1.5 text-slate-500 hover:bg-red-500/20 hover:text-red-400 transition-colors"
                                onClick={(e) => { e.stopPropagation(); onDelete(item.id); }}
                                title="Eliminar"
                                aria-label={'Eliminar ' + (item.process ?? item.name ?? '')}
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

export default memo(Row);
