import { useState, useEffect, useMemo } from 'react';
import { X } from 'lucide-react';
import FormField from '@/Components/Common/FormField';
import { replaceId } from '@/utils/url';
import { t } from '@/utils/i18n';

/* ── Fields to hide per workflow type ────────────────────────── */
// BACKEND: UpdateServiceOrderRequest — prohibited fields for workflow_type=loan
const LOAN_HIDDEN = new Set([
    'service_type_id', 'sector_ids',
]);
// BACKEND: UpdateServiceOrderRequest — prohibited fields for workflow_type=regular
const REGULAR_HIDDEN = new Set([
    'equipment_ids',
]);

/**
 * Resolve a display value from a record, normalising related-object shapes.
 * Handles:
 * - Direct field access: item[fieldName]
 * - Related arrays: "_ids" → item[singularPlural] with id extraction
 * - Related objects: "_id" → item[singularName] with id extraction
 * - Nested fields: location fields inside item.location, etc.
 * - Singular fields receiving arrays: extract first ID (e.g., sector_id from sectors array)
 */
function resolveFieldValue(field, item) {
    const fieldName = field.name ?? field.key;
    const raw = item[fieldName];

    // 1. Direct field access (covers most cases)
    if (raw !== undefined && raw !== null) return raw;

    // 2. Try guessing related arrays/objects
    const guessKey = fieldName.replace(/_(?:id|ids)$/, '') + 's';
    const singularKey = fieldName.replace(/_(?:id|ids)$/, '');
    const related = item[guessKey] ?? item[fieldName.replace(/s$/, '')] ?? item[singularKey];
    
    if (related !== undefined && related !== null) {
        // Handle multiple-select fields
        if (field.multiple) {
            if (Array.isArray(related)) {
                return related.map(i => (i && typeof i === 'object' ? i.id : i)).filter(Boolean);
            }
            return related;
        }

        // Handle single-select fields
        // If we got an array but field is singular, extract first ID
        if (Array.isArray(related)) {
            if (related.length === 0) return '';
            const first = related[0];
            return (first && typeof first === 'object' && 'id' in first) ? first.id : first;
        }

        // Handle single object with id
        if (related && typeof related === 'object' && 'id' in related) return related.id;
        return related;
    }

    // 3. Try nested objects for flattened fields (e.g., location.* fields)
    // For fields like "parish_id", "street", "reference_point", check item.location
    const nestedCandidates = ['location'];
    for (const nested of nestedCandidates) {
        if (item[nested] && typeof item[nested] === 'object') {
            // Direct match: item.location.parish_id
            if (item[nested][fieldName] !== undefined) return item[nested][fieldName];
            // Alias match: item.location.street_address → "street"
            if (fieldName === 'street' && item[nested]['street_address']) return item[nested]['street_address'];
            if (fieldName === 'reference_point' && item[nested]['landmark']) return item[nested]['landmark'];
        }
    }

    return '';
}

export default function EditPanel({ entityName, formSchema, routes, selectedItem, onClose, onDelete, onError, onSaved }) {
    const fields = useMemo(
        () => Array.isArray(formSchema) ? formSchema : (formSchema?.inputs ?? []),
        [formSchema]
    );
    const [errors, setErrors] = useState({});
    const [saving, setSaving] = useState(false);
    const [workflowType, setWorkflowType] = useState('');

    /* ── Controlled form values — keyed by field name (#4) ───────── */
    const [formValues, setFormValues] = useState({});

    /* ── Seed formValues whenever selectedItem changes only ─── */
    /* Only re-seed when the selected item changes, not on every fields array recreation */
    useEffect(() => {
        if (!selectedItem) return;
        const initial = {};
        fields.forEach((f) => {
            const fieldName = f.name ?? f.key;
            initial[fieldName] = resolveFieldValue(f, selectedItem);

            // Map fields need lat/lng available in formValues for MapPicker
            const isMap = f.type === 'map' || f.type === 'map-picker';
            if (isMap && selectedItem) {
                const latField = f.metadata?.latField ?? 'latitude';
                const lngField = f.metadata?.lngField ?? 'longitude';
                if (selectedItem[latField] !== undefined) initial[latField] = selectedItem[latField];
                if (selectedItem[lngField] !== undefined) initial[lngField] = selectedItem[lngField];
            }
        });
        setFormValues(initial);
        setErrors({});

        // Seed workflowType so visibleFields filters correctly for this item's type
        const wt = selectedItem.workflow_type;
        setWorkflowType(typeof wt === 'string' ? wt : (wt?.value ?? ''));
    }, [selectedItem?.id]); // Remove fields from dependency - only seed when item ID changes

    /* ── Track workflow_type changes via custom toggle-change event ── */
    useEffect(() => {
        const handler = (e) => {
            if (e.detail.name === 'workflow_type') {
                setWorkflowType(e.detail.value);
                setFormValues((prev) => ({ ...prev, workflow_type: e.detail.value }));
            }
        };
        document.addEventListener('toggle-change', handler);
        return () => document.removeEventListener('toggle-change', handler);
    }, []);

    /* ── Compute visible fields based on workflow type ───────── */
    const visibleFields = useMemo(() => {
        return fields.filter(f => {
            const key = f.key ?? f.name ?? '';
            if (workflowType === 'loan') return !LOAN_HIDDEN.has(key);
            if (workflowType === 'regular') return !REGULAR_HIDDEN.has(key);
            return true;
        });
    }, [fields, workflowType]);

    /* ── Field change handler — used by FormField via onChange prop ── */
    const handleFieldChange = (fieldName, value) => {
        setFormValues((prev) => {
            const updated = { ...prev, [fieldName]: value };
            return updated;
        });
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        if (!routes.update || !selectedItem) return;
        setSaving(true);

        // Build payload from controlled state (#4)
        // Map fields still need hidden inputs as FormField handles them internally;
        // we merge those from the DOM only for map types.
        const form = e.target;
        const data = { ...formValues };
        fields.forEach((f) => {
            const fieldName = f.name ?? f.key;
            const isMap = f.type === 'map' || f.type === 'map-picker';
            if (isMap) {
                const latField = f.metadata?.latField ?? 'latitude';
                const lngField = f.metadata?.lngField ?? 'longitude';
                const latInput = form.elements[latField];
                const lngInput = form.elements[lngField];
                if (latInput) data[latField] = latInput.value;
                if (lngInput) data[lngField] = lngInput.value;
                delete data[fieldName];
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
                // Notify parent to refetch — no page reload (#3)
                onSaved?.();
            } else {
                if (resData.errors) setErrors(resData.errors);
                else onError?.(t('pages.datamanager.update_failed_title'), resData.error ?? t('pages.datamanager.update_failed_desc'));
            }
        } catch (error) {
            onError?.(t('pages.datamanager.update_error_title'), error?.message || t('pages.datamanager.update_error_desc'));
        } finally {
            setSaving(false);
        }
    };

    if (!selectedItem) return null;

    return (
        <div className="flex w-96 shrink-0 flex-col overflow-y-auto rounded-lg border border-brand-mid/20 bg-brand-white shadow-xl max-h-full">
            {/* Header */}
            <div className="flex items-center justify-between border-b border-brand-mid/20 px-4 py-3">
                <h6 className="text-sm font-bold text-brand-darkest">{t('pages.datamanager.edit_title', { name: entityName })}</h6>
                <button
                    type="button"
                    className="rounded-lg p-1 text-brand-mid hover:bg-brand-light hover:text-brand-darkest transition-colors"
                    onClick={onClose}
                    aria-label={t('pages.datamanager.close_aria')}
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
                            <div key={fieldName ?? i} className="mb-4">
                                <FormField
                                    field={field}
                                    value={isMap ? formValues : formValues[fieldName]}
                                    error={fieldError}
                                    onChange={(val) => handleFieldChange(fieldName, val)}
                                />
                            </div>
                        );
                    })}
                </div>

                {/* Footer */}
                <div className="sticky bottom-0 border-t border-brand-mid/20 bg-brand-white px-4 py-3">
                    <div className="flex flex-col gap-2">
                        <button
                            type="submit"
                            disabled={saving}
                            className="inline-flex items-center justify-center rounded-lg bg-brand-accent px-4 py-2 text-sm font-medium text-brand-white shadow-sm hover:bg-brand-accent/90 disabled:opacity-50 transition-colors"
                        >
                            {saving ? t('pages.datamanager.saving_btn') : t('pages.datamanager.save_btn')}
                        </button>
                        <button
                            type="button"
                            onClick={onClose}
                            className="inline-flex items-center justify-center rounded-lg border border-brand-mid/20 bg-brand-light px-4 py-2 text-sm font-medium text-brand-mid hover:bg-brand-mid/10 transition-colors"
                        >
                            {t('pages.datamanager.cancel_btn')}
                        </button>
                        {routes.destroy && (
                            <button
                                type="button"
                                onClick={() => onDelete(selectedItem.id)}
                                className="inline-flex items-center justify-center rounded-lg border border-red-800/50 px-4 py-2 text-sm font-medium text-red-400 hover:bg-red-500/10 transition-colors"
                            >
                                {t('pages.datamanager.remove_btn')}
                            </button>
                        )}
                    </div>
                </div>
            </form>
        </div>
    );
}
