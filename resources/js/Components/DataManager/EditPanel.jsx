import { useState, useEffect, useMemo, useCallback, useRef } from 'react';
import { X } from 'lucide-react';
import FormField from '@/Components/Common/FormField';
import { replaceId } from '@/utils/url';
import { t } from '@/utils/i18n';
import { csrfHeader } from '@/utils/csrf';
import { validateRequired } from '@/utils/validateRequired';

/* ── Fields to hide per workflow type ────────────────────────── */
// BACKEND: UpdateServiceOrderRequest — prohibited fields for workflow_type=loan
function evalCondition({ operator, value }, fieldValue) {
  switch (operator) {
    case '==':     return fieldValue === value;
    case '!=':     return fieldValue !== value;
    case '>':      return fieldValue > value;
    case '<':      return fieldValue < value;
    case '>=':     return fieldValue >= value;
    case '<=':     return fieldValue <= value;
    case 'in':     return Array.isArray(value) && value.includes(fieldValue);
    case 'not_in': return !Array.isArray(value) || !value.includes(fieldValue);
    default:       return true;
  }
}

const LOAN_HIDDEN = new Set([
    'sector_ids',
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

    // date-picker range: build { start, end } from the named sub-fields on the item
    if (field.type === 'date-picker' && (field.metadata?.dateMode === 'range' || field.dateMode === 'range')) {
        const startKey = field.metadata?.startName ?? field.startName ?? `${fieldName}_start`;
        const endKey   = field.metadata?.endName   ?? field.endName   ?? `${fieldName}_end`;
        return {
            start: item[startKey] ?? raw?.start ?? null,
            end:   item[endKey]   ?? raw?.end   ?? null,
        };
    }

    // repeater: transform each item's date-range sub-fields to { start, end }
    if (field.type === 'repeater' && Array.isArray(raw)) {
        const drSubs = (field.subFields ?? []).filter(
            sf => sf.type === 'date-picker' && (sf.metadata?.dateMode === 'range' || sf.dateMode === 'range')
        );
        if (drSubs.length === 0) return raw;
        return raw.map(rowItem => {
            const next = { ...rowItem };
            drSubs.forEach(sf => {
                const sfName   = sf.name ?? sf.key;
                const startKey = sf.metadata?.startName ?? sf.startName ?? `${sfName}_start`;
                const endKey   = sf.metadata?.endName   ?? sf.endName   ?? `${sfName}_end`;
                next[sfName] = { start: rowItem[startKey] ?? null, end: rowItem[endKey] ?? null };
            });
            return next;
        });
    }

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
    const [panelWidth, setPanelWidth] = useState(384);
    const resizingRef = useRef(false);
    const startXRef = useRef(0);
    const startWidthRef = useRef(0);

    /* ── Controlled form values — keyed by field name (#4) ───────── */
    const [formValues, setFormValues] = useState({});

    /* ── Seed helper — builds the controlled-state map from any item object ── */
    const seedFromItem = useCallback((item) => {
        if (!item) return;
        const initial = {};
        fields.forEach((f) => {
            const fieldName = f.name ?? f.key;
            initial[fieldName] = resolveFieldValue(f, item);
            const isMap = f.type === 'map' || f.type === 'map-picker';
            if (isMap) {
                const latField = f.metadata?.latField ?? 'latitude';
                const lngField = f.metadata?.lngField ?? 'longitude';
                if (item[latField] !== undefined) initial[latField] = item[latField];
                if (item[lngField] !== undefined) initial[lngField] = item[lngField];
            }
        });
        setFormValues(initial);
        const wt = item.workflow_type;
        setWorkflowType(typeof wt === 'string' ? wt : (wt?.value ?? ''));
    }, [fields]);

    /* ── Fetch full item from routes.show, fall back to selectedItem row data ── */
    /* The paginated row only carries display columns; show returns every field    */
    useEffect(() => {
        if (!selectedItem) return;
        setErrors({});
        setFormValues({});

        if (!routes.show) {
            seedFromItem(selectedItem);
            return;
        }

        const url = replaceId(routes.show, selectedItem.id);
        fetch(url, {
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                ...csrfHeader(),
            },
        })
            .then(r => r.ok ? r.json() : Promise.reject())
            .then(data => seedFromItem(data.data ?? data))
            .catch(() => seedFromItem(selectedItem)); // fallback to partial row data
    }, [selectedItem?.id]); // eslint-disable-line react-hooks/exhaustive-deps

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

    /* ── Compute visible fields based on workflow type + field conditions ── */
    const visibleFields = useMemo(() => {
        return fields.filter(f => {
            const key = f.key ?? f.name ?? '';
            if (workflowType === 'loan' && LOAN_HIDDEN.has(key)) return false;
            if (workflowType === 'regular' && REGULAR_HIDDEN.has(key)) return false;
            if (f.condition) {
                const cv = formValues[f.condition.field] ?? '';
                return evalCondition(f.condition, cv);
            }
            return true;
        });
    }, [fields, formValues, workflowType]);

    /* ── Locked workers — derived from selected team_ids ─────────── */
    const lockedWorkerIds = useMemo(() => {
        const workerField = fields.find(f => (f.key ?? f.name) === 'worker_ids');
        const workerOpts = workerField?.options ?? [];
        const selectedTeams = Array.isArray(formValues.team_ids) ? formValues.team_ids : [];
        if (selectedTeams.length === 0) return [];
        return workerOpts
            .filter(w => w.team_id && selectedTeams.includes(w.team_id))
            .map(w => w.value);
    }, [formValues.team_ids, fields]);

    /* ── Field change handler — used by FormField via onChange prop ── */
    const handleFieldChange = useCallback((fieldName, value) => {
        setFormValues((prev) => {
            const next = { ...prev, [fieldName]: value };
            // When team_ids change, auto-include workers belonging to selected teams
            if (fieldName === 'team_ids') {
                const workerField = fields.find(f => (f.key ?? f.name) === 'worker_ids');
                const workerOpts = workerField?.options ?? [];
                const selectedTeams = Array.isArray(value) ? value : [];
                const newLockedIds = workerOpts
                    .filter(w => w.team_id && selectedTeams.includes(w.team_id))
                    .map(w => w.value);
                const prevLockedIds = workerOpts
                    .filter(w => w.team_id && Array.isArray(prev.team_ids) && prev.team_ids.includes(w.team_id))
                    .map(w => w.value);
                const existingWorkers = Array.isArray(prev.worker_ids) ? prev.worker_ids : [];
                const keptWorkers = existingWorkers.filter(id => !prevLockedIds.includes(id));
                next.worker_ids = [...new Set([...keptWorkers, ...newLockedIds])];
            }
            return next;
        });
    }, [fields]);

    /* ── Resize ────────────────────────────────────────────── */
    const onResizeStart = useCallback((e) => {
        e.preventDefault();
        resizingRef.current = true;
        startXRef.current = e.clientX;
        startWidthRef.current = panelWidth;
        document.body.style.cursor = 'col-resize';
        document.body.style.userSelect = 'none';

        const onMove = (ev) => {
            if (!resizingRef.current) return;
            const delta = startXRef.current - ev.clientX;
            const next = Math.max(280, Math.min(900, startWidthRef.current + delta));
            setPanelWidth(next);
        };
        const onUp = () => {
            resizingRef.current = false;
            document.body.style.cursor = '';
            document.body.style.userSelect = '';
            document.removeEventListener('mousemove', onMove);
            document.removeEventListener('mouseup', onUp);
        };
        document.addEventListener('mousemove', onMove);
        document.addEventListener('mouseup', onUp);
    }, [panelWidth]);

    const handleSubmit = async (e) => {
        e.preventDefault();
        if (!routes.update || !selectedItem) return;

        const clientErrors = validateRequired(visibleFields, formValues);
        if (Object.keys(clientErrors).length > 0) {
            setErrors(clientErrors);
            return;
        }

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
                return;
            }
            // Flatten date-picker range field → startName / endName
            if (f.type === 'date-picker' && (f.metadata?.dateMode === 'range' || f.dateMode === 'range')) {
                const startKey = f.metadata?.startName ?? f.startName ?? `${fieldName}_start`;
                const endKey   = f.metadata?.endName   ?? f.endName   ?? `${fieldName}_end`;
                const rangeVal = data[fieldName];
                if (rangeVal && typeof rangeVal === 'object') {
                    data[startKey] = rangeVal.start ?? '';
                    data[endKey]   = rangeVal.end   ?? '';
                }
                delete data[fieldName];
                return;
            }
            // Flatten date-range sub-fields inside repeater items
            if (f.type === 'repeater') {
                const drSubs = (f.subFields ?? []).filter(
                    sf => sf.type === 'date-picker' && (sf.metadata?.dateMode === 'range' || sf.dateMode === 'range')
                );
                if (drSubs.length > 0 && Array.isArray(data[fieldName])) {
                    data[fieldName] = data[fieldName].map(rowItem => {
                        const next = { ...rowItem };
                        drSubs.forEach(sf => {
                            const sfName   = sf.name ?? sf.key;
                            const startKey = sf.metadata?.startName ?? sf.startName ?? `${sfName}_start`;
                            const endKey   = sf.metadata?.endName   ?? sf.endName   ?? `${sfName}_end`;
                            const rangeVal = next[sfName];
                            if (rangeVal && typeof rangeVal === 'object') {
                                next[startKey] = rangeVal.start ?? '';
                                next[endKey]   = rangeVal.end   ?? '';
                            }
                            delete next[sfName];
                        });
                        return next;
                    });
                }
            }
        });

        try {
            const url = replaceId(routes.update, selectedItem.id);
            const hasFiles = Object.values(data).some(v => v instanceof File);
            let reqBody, reqMethod, reqHeaders;
            if (hasFiles) {
                const fd = new FormData();
                fd.append('_method', 'PUT');
                Object.entries(data).forEach(([k, v]) => {
                    if (v instanceof File) { fd.append(k, v); }
                    else if (Array.isArray(v)) { v.forEach(item => fd.append(`${k}[]`, item ?? '')); }
                    else if (v !== null && v !== undefined) { fd.append(k, String(v)); }
                });
                reqBody = fd;
                reqMethod = 'POST';
                reqHeaders = { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest', ...csrfHeader() };
            } else {
                reqBody = JSON.stringify(data);
                reqMethod = 'PUT';
                reqHeaders = { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest', ...csrfHeader() };
            }
            const res = await fetch(url, {
                method: reqMethod,
                headers: reqHeaders,
                body: reqBody,
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
        <div className="flex shrink-0 max-h-full relative select-none" style={{ width: panelWidth }}>
            {/* Resize handle */}
            <div
                className="absolute left-0 top-0 bottom-0 w-4 -ml-2 cursor-col-resize z-10 flex items-center justify-center group"
                onMouseDown={onResizeStart}
            >
                <div className="w-1 h-12 rounded-full bg-brand-mid/30 group-hover:bg-brand-accent transition-colors" />
            </div>

            <div className="flex flex-col overflow-y-auto rounded-lg border border-brand-mid/20 bg-brand-white shadow-xl w-full">
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
                    {errors._general && (
                        <div className="mb-3 rounded-lg bg-red-50 p-3 text-sm text-red-600">
                            {errors._general}
                        </div>
                    )}

                    {visibleFields.map((field, i) => {
                        const fieldName = field.name ?? field.key;
                        const isMap = field.type === 'map' || field.type === 'map-picker';
                        const fieldError = errors[fieldName]?.join?.(' ') ?? errors[fieldName];
                        const fieldLockedValues = fieldName === 'worker_ids' ? lockedWorkerIds : [];
                        return (
                            <div key={fieldName ?? i} className="mb-4">
                                <FormField
                                    field={field}
                                    value={isMap ? formValues : formValues[fieldName]}
                                    error={fieldError}
                                    onChange={(val) => handleFieldChange(fieldName, val)}
                                    lockedValues={fieldLockedValues}
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
                            className="inline-flex items-center justify-center rounded-lg border border-brand-mid/20 bg-brand-white px-4 py-2 text-sm font-medium text-brand-mid hover:bg-brand-light transition-colors"
                        >
                            {t('pages.datamanager.cancel_btn')}
                        </button>
                        {routes.destroy && (
                            <button
                                type="button"
                                onClick={() => onDelete(selectedItem.id)}
                                className="inline-flex items-center justify-center rounded-lg border border-red-200 px-4 py-2 text-sm font-medium text-red-600 hover:bg-red-50 transition-colors"
                            >
                                {t('pages.datamanager.remove_btn')}
                            </button>
                        )}
                    </div>
                </div>
            </form>
        </div>
        </div>
    );
}
