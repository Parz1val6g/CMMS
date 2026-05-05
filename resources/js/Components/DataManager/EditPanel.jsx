import { useState, useEffect, useMemo } from 'react';
import { X } from 'lucide-react';
import FormField from '@/Components/Common/FormField';
import { replaceId, buildQuery } from '@/utils/url';

/* ── Fields to hide per workflow type ────────────────────────── */
const LOAN_HIDDEN = new Set([
    'service_type_id', 'priority',
    'section-photo', 'photo',
    'section-location', 'parish_id', 'street', 'reference_point', 'postal_code',
    'section-map', 'location',
]);

function navigateWithQuery(params) {
    const qs = buildQuery(params);
    window.history.replaceState(null, '', window.location.pathname + (qs ? `?${qs}` : ''));
    window.location.reload();
}

/**
 * Resolve a display value from a record, normalising related-object shapes.
 * "_ids" fields → extract IDs from the related array; "_id" fields → extract id from related object.
 */
function resolveFieldValue(field, item) {
    const fieldName = field.name ?? field.key;
    const raw = item[fieldName];

    if (raw !== undefined && raw !== null) return raw;

    const guessKey = fieldName.replace(/_(?:id|ids)$/, '') + 's';
    const singularKey = fieldName.replace(/_(?:id|ids)$/, '');
    const related = item[guessKey] ?? item[fieldName.replace(/s$/, '')] ?? item[singularKey];
    if (related === undefined || related === null) return '';

    if (field.multiple) {
        if (Array.isArray(related)) {
            return related.map(i => (i && typeof i === 'object' ? i.id : i)).filter(Boolean);
        }
        return related;
    }

    if (related && typeof related === 'object' && 'id' in related) return related.id;
    return related;
}

export default function EditPanel({ entityName, formSchema, routes, selectedItem, onClose, onDelete, onError }) {
    const fields = useMemo(
        () => Array.isArray(formSchema) ? formSchema : (formSchema?.inputs ?? []),
        [formSchema]
    );
    const [errors, setErrors] = useState({});
    const [saving, setSaving] = useState(false);
    const [workflowType, setWorkflowType] = useState('');

    /* ── Track workflow_type changes via DOM delegation ──────── */
    useEffect(() => {
        const el = document.querySelector('select[name="workflow_type"]');
        if (!el) return;
        const handler = () => setWorkflowType(el.value);
        el.addEventListener('change', handler);
        setWorkflowType(el.value);
        return () => el.removeEventListener('change', handler);
    }, []);

    /* ── Compute visible fields based on workflow type ───────── */
    const visibleFields = useMemo(() => {
        if (workflowType !== 'loan') return fields;
        return fields.filter(f => {
            const key = f.key ?? f.name ?? '';
            return !LOAN_HIDDEN.has(key);
        });
    }, [fields, workflowType]);

    // Derive formData from selectedItem — read-only snapshot for defaultValue bindings
    const formData = useMemo(() => {
        if (!selectedItem) return {};
        const data = {};
        fields.forEach((f) => {
            data[f.name ?? f.key] = resolveFieldValue(f, selectedItem);
        });
        return data;
    }, [selectedItem, fields]);

    useEffect(() => {
        if (selectedItem) {
            setErrors({});
        }
    }, [selectedItem, formSchema]);

    const handleSubmit = async (e) => {
        e.preventDefault();
        if (!routes.update || !selectedItem) return;
        setSaving(true);

        const form = e.target;
        const data = {};
        fields.forEach((f) => {
            const fieldName = f.name ?? f.key;
            const isMap = f.type === 'map' || f.type === 'map-picker';
            const isMulti = f.multiple || f.type === 'multiselect';
            if (isMap) {
                /* Map field: extract lat/lng from hidden inputs */
                const latField = f.metadata?.latField ?? 'latitude';
                const lngField = f.metadata?.lngField ?? 'longitude';
                const latInput = form.elements[latField];
                const lngInput = form.elements[lngField];
                if (latInput) data[latField] = latInput.value;
                if (lngInput) data[lngField] = lngInput.value;
            } else if (isMulti) {
                /* Multi-select: collect all hidden inputs with this name */
                const els = form.querySelectorAll(`input[name="${CSS.escape(fieldName)}"]`);
                data[fieldName] = Array.from(els).map(el => el.value);
            } else {
                const input = form.elements[fieldName];
                if (input) data[fieldName] = input.value;
            }
        });

        const token = document.querySelector('meta[name="csrf-token"]')?.content;

        try {
            const url = replaceId(routes.update, selectedItem.id);
            const res = await fetch(url, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': token ?? '',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify(data),
            });
            const resData = await res.json();

            if (res.ok) {
                onClose();
                navigateWithQuery({});
            } else {
                if (resData.errors) setErrors(resData.errors);
                else onError?.('Update Failed', resData.error ?? 'Failed to update this item. Please try again.');
            }
        } catch (error) {
            onError?.('Update Error', error?.message || 'An unexpected error occurred while updating.');
        } finally {
            setSaving(false);
        }
    };

    if (!selectedItem) return null;

    return (
        <div className="flex w-96 shrink-0 flex-col overflow-y-auto rounded-lg border border-slate-700 bg-slate-800 shadow-xl max-h-full">
            {/* Header */}
            <div className="flex items-center justify-between border-b border-slate-700 px-4 py-3">
                <h6 className="text-sm font-bold text-white">Edit {entityName}</h6>
                <button
                    type="button"
                    className="rounded-lg p-1 text-slate-400 hover:bg-slate-700 hover:text-white transition-colors"
                    onClick={onClose}
                    aria-label="Close"
                >
                    <X className="h-4 w-4" />
                </button>
            </div>

            {/* Form */}
            <form id="sm-edit-form" onSubmit={handleSubmit} className="flex flex-1 flex-col" encType="multipart/form-data" noValidate>
                <input type="hidden" name="_token" value={document.querySelector('meta[name="csrf-token"]')?.content ?? ''} />

                <div className="flex-1 overflow-y-auto p-4">
                    {Object.keys(errors).length > 0 && (
                        <div className="mb-3 rounded-lg bg-red-500/10 p-3 text-sm text-red-300">
                            {Object.entries(errors).map(([field, msgs]) => (
                                <p key={field}>{(Array.isArray(msgs) ? msgs : [msgs]).join(', ')}</p>
                            ))}
                        </div>
                    )}

                    {visibleFields.map((field, i) => {
                        const fieldName = field.name ?? field.key;
                        const isMap = field.type === 'map' || field.type === 'map-picker';
                        const fieldError = errors[fieldName]?.join?.(' ') ?? errors[fieldName];
                        return (
                            <div key={i} className="mb-4">
                                <FormField
                                    field={field}
                                    value={isMap ? formData : formData[fieldName]}
                                    error={fieldError}
                                />
                            </div>
                        );
                    })}
                </div>

                {/* Footer */}
                <div className="sticky bottom-0 border-t border-slate-700 bg-slate-800 px-4 py-3">
                    <div className="flex flex-col gap-2">
                        <button
                            type="submit"
                            disabled={saving}
                            className="inline-flex items-center justify-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 disabled:opacity-50 transition-colors"
                        >
                            {saving ? 'Saving...' : 'Save Changes'}
                        </button>
                        <button
                            type="button"
                            onClick={onClose}
                            className="inline-flex items-center justify-center rounded-lg border border-slate-700 bg-slate-800/60 px-4 py-2 text-sm font-medium text-slate-400 hover:bg-slate-700 transition-colors"
                        >
                            Cancel
                        </button>
                        {routes.destroy && (
                            <button
                                type="button"
                                onClick={() => onDelete(selectedItem.id)}
                                className="inline-flex items-center justify-center rounded-lg border border-red-800/50 px-4 py-2 text-sm font-medium text-red-400 hover:bg-red-500/10 transition-colors"
                            >
                                Remove
                            </button>
                        )}
                    </div>
                </div>
            </form>
        </div>
    );
}
