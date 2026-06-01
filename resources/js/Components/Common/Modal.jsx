import { useEffect, useRef, useState, useMemo, useCallback } from 'react';
import FormField from '@/Components/Common/FormField';
import FormInput from '@/Components/Common/FormInput';
import { X } from 'lucide-react';
import { useToast } from '@/Components/Toast/ToastContext';
import { t } from '@/utils/i18n';
import { useFocusTrap } from '@/Hooks/useFocusTrap';
import { useBodyLock } from '@/Hooks/useBodyLock';

import { csrfHeader } from '@/utils/csrf';

/* ── Fields to hide per workflow type ────────────────────────── */
// BACKEND: StoreServiceOrderRequest — prohibited fields for workflow_type=loan
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
  'service_type_id',
  'sector_ids',
]);
// BACKEND: StoreServiceOrderRequest — prohibited fields for workflow_type=regular
const REGULAR_HIDDEN = new Set([
  'equipment_ids',
]);

function getInitialValues(fields) {
  const vals = {};
  fields.forEach((f) => {
    const name = f.name ?? f.key;
    if (f.type === 'repeater') {
      vals[name] = f.value ?? [];
    } else {
      vals[name] = f.value ?? '';
    }
  });
  return vals;
}

function collectFormData(form, fields, formValues) {
  const data = {};
  fields.forEach((f) => {
    const name = f.name ?? f.key;
    if (f.multiple || f.type === 'multiselect') {
      data[name] = formValues[name] ?? [];
    } else if (f.type === 'repeater') {
      const repeaterItems = formValues[name] ?? [];
      const drSubs = (f.subFields ?? []).filter(
        sf => sf.type === 'date-picker' && (sf.metadata?.dateMode === 'range' || sf.dateMode === 'range')
      );
      if (drSubs.length === 0) {
        data[name] = repeaterItems;
      } else {
        data[name] = repeaterItems.map(rowItem => {
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
    } else if (f.type === 'map' || f.type === 'map-picker') {
      const latField = f.metadata?.latField ?? 'latitude';
      const lngField = f.metadata?.lngField ?? 'longitude';
      if (form.elements[latField]) data[latField] = form.elements[latField].value;
      if (form.elements[lngField]) data[lngField] = form.elements[lngField].value;
    } else if (f.type === 'checkbox' || f.type === 'toggle') {
      // Boolean toggles use React state — DOM el.value is always "on"/"off"
      data[name] = formValues[name] ?? false;
    } else if (f.type === 'date-picker' && (f.dateMode === 'range' || f.metadata?.dateMode === 'range')) {
      const startFieldName = f.metadata?.startName ?? f.startName ?? `${name}_start`;
      const endFieldName   = f.metadata?.endName   ?? f.endName   ?? `${name}_end`;
      const rangeVal = formValues[name];
      if (rangeVal && typeof rangeVal === 'object') {
        data[startFieldName] = rangeVal.start ?? '';
        data[endFieldName]   = rangeVal.end   ?? '';
      } else {
        data[startFieldName] = form.elements[startFieldName]?.value ?? '';
        data[endFieldName]   = form.elements[endFieldName]?.value   ?? '';
      }
    } else if (f.type === 'file') {
      data[name] = formValues[name] ?? null;
    } else {
      const el = form.elements[name];
      if (!el) return;
      data[name] = el.value;
    }
  });
  return data;
}

export default function Modal({ entityName = t('common.entity_name'), title, formSchema = [], routes = {}, size = '', open, onClose, onSubmit: externalSubmit, children, injectAfterField, externalErrors = {} }) {
  const formRef = useRef(null);
  const containerRef = useRef(null);
  const [saving, setSaving] = useState(false);
  const [errors, setErrors] = useState({});
  const [formValues, setFormValues] = useState({});
  const [workflowType, setWorkflowType] = useState('regular');
  const toast = useToast();

  /* ── Locked workers — derived from selected team_ids ─────── */
  const lockedWorkerIds = useMemo(() => {
    const isObj = !Array.isArray(formSchema) && formSchema !== null;
    const schemaFields = isObj ? (formSchema?.inputs ?? []) : formSchema;
    const workerField = schemaFields.find(f => (f.key ?? f.name) === 'worker_ids');
    const workerOpts = workerField?.options ?? [];
    const selectedTeams = Array.isArray(formValues.team_ids) ? formValues.team_ids : [];
    if (selectedTeams.length === 0) return [];
    return workerOpts
      .filter(w => w.team_id && selectedTeams.includes(w.team_id))
      .map(w => w.value);
  }, [formValues.team_ids, formSchema]);
  /* ── Extract fields & schemaTitle from formSchema ──────────── */
  const { fields, schemaTitle, columns } = useMemo(() => {
    const isObj = !Array.isArray(formSchema) && formSchema !== null;
    return {
      fields: isObj ? (formSchema?.inputs ?? []) : formSchema,
      schemaTitle: isObj ? (formSchema?.title ?? null) : null,
      columns: isObj ? (formSchema?.columns ?? 1) : 1,
    };
  }, [formSchema]);

  /* ── Initialize form values when schema/modal opens ──────── */
  useEffect(() => {
    if (open) setFormValues(getInitialValues(fields));
  }, [open, fields]);

  /* ── Track workflow_type changes via custom toggle-change event ── */
  useEffect(() => {
    if (!open) { setWorkflowType('regular'); return; }

    const handler = (e) => {
      if (e.detail.name === 'workflow_type') {
        setWorkflowType(e.detail.value);
      }
    };
    document.addEventListener('toggle-change', handler);

    // Pick up initial value from hidden input if already rendered
    const hidden = document.querySelector('input[name="workflow_type"]');
    if (hidden?.value) setWorkflowType(hidden.value);

    return () => document.removeEventListener('toggle-change', handler);
  }, [open]);

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

  useEffect(() => {
    const handler = (e) => { if (e.key === 'Escape') onClose?.(); };
    if (open) document.addEventListener('keydown', handler);
    return () => document.removeEventListener('keydown', handler);
  }, [open, onClose]);

  useFocusTrap(containerRef, open);
  useBodyLock(open);

  useEffect(() => {
    if (open) { setErrors({}); setSaving(false); }
  }, [open]);
  
  /* ── Generic onChange to update formValues state ─────────── */
  const updateValue = useCallback((name, val) => {
    setFormValues(prev => {
      const next = { ...prev, [name]: val };
      if (name === 'team_ids') {
        const isObj = !Array.isArray(formSchema) && formSchema !== null;
        const schemaFields = isObj ? (formSchema?.inputs ?? []) : formSchema;
        const workerField = schemaFields.find(f => (f.key ?? f.name) === 'worker_ids');
        const workerOpts = workerField?.options ?? [];
        const selectedTeams = Array.isArray(val) ? val : [];
        const newLockedIds = workerOpts
          .filter(w => w.team_id && selectedTeams.includes(w.team_id))
          .map(w => w.value);
        const existingWorkers = Array.isArray(prev.worker_ids) ? prev.worker_ids : [];
        const prevLockedIds = workerOpts
          .filter(w => w.team_id && Array.isArray(prev.team_ids) && prev.team_ids.includes(w.team_id))
          .map(w => w.value);
        const keptWorkers = existingWorkers.filter(id => !prevLockedIds.includes(id));
        next.worker_ids = [...new Set([...keptWorkers, ...newLockedIds])];
      }
      return next;
    });
    const el = formRef.current?.elements[name];
    if (el) el.value = val;
    document.dispatchEvent(new CustomEvent('modal-field-change', { detail: { name, value: val } }));
  }, [formSchema]);

  /* ── Listen for autofill-location events (from ClientLocationSelector) ── */
  useEffect(() => {
    if (!open) return;
    const handler = (e) => {
      Object.entries(e.detail).forEach(([name, val]) => updateValue(name, val));
    };
    document.addEventListener('autofill-location', handler);
    return () => document.removeEventListener('autofill-location', handler);
  }, [open, updateValue]);

  const handleSubmit = async (e) => {
    e.preventDefault();
    if (!routes.store) return;

    // If an external onSubmit was provided, delegate to it
    if (externalSubmit) {
      externalSubmit(e, formValues);
      return;
    }

    setSaving(true);
    setErrors({});

    const data = collectFormData(e.currentTarget, fields, formValues);

    try {
      const hasFiles = Object.values(data).some(v => v instanceof File);
      let reqBody, reqHeaders;
      if (hasFiles) {
        const fd = new FormData();
        Object.entries(data).forEach(([k, v]) => {
          if (v instanceof File) { fd.append(k, v); }
          else if (Array.isArray(v)) { v.forEach(item => fd.append(`${k}[]`, item ?? '')); }
          else if (v !== null && v !== undefined) { fd.append(k, String(v)); }
        });
        reqBody = fd;
        reqHeaders = { 'Accept': 'application/json', ...csrfHeader(), 'X-Requested-With': 'XMLHttpRequest' };
      } else {
        reqBody = JSON.stringify(data);
        reqHeaders = { 'Content-Type': 'application/json', 'Accept': 'application/json', ...csrfHeader(), 'X-Requested-With': 'XMLHttpRequest' };
      }
      const res = await fetch(routes.store, {
        method: 'POST',
        headers: reqHeaders,
        body: reqBody,
      });

      const body = await res.json();

      if (res.ok) {
        toast.success(t('pages.modal.save_success', { name: entityName }));
        onClose?.();
        // Small delay to let user see the toast before reload
        setTimeout(() => {
          window.location.reload();
        }, 500);
      } else {
        if (body.errors) {
          setErrors(body.errors);
        } else {
          const errorMsg = body.message ?? t('pages.modal.save_failed_desc');
          setErrors({ _general: errorMsg });
        }
      }
    } catch (err) {
      const errorMsg = err?.message || t('pages.datamanager.error_fallback');
      setErrors({ _general: errorMsg });
    } finally {
      setSaving(false);
    }
  };

  if (!open) return null;

  const sizeMap = { xl: 'max-w-5xl', lg: 'max-w-3xl', sm: 'max-w-md' };
  const sizeClass = size ? (sizeMap[size] ?? 'max-w-lg') : 'w-[82vw] max-w-[1400px]';
  const modalTitle = title ?? schemaTitle ?? t('pages.modal.create_title', { name: entityName });

  const fullWidthTypes = new Set(['map', 'map-picker', 'textarea']);

  function gridStyle(field) {
    if (columns <= 1) return {};
    const isFullWidth = fullWidthTypes.has(field.type) && !field.column && !(field.span && field.span > 1);
    if (isFullWidth) return { gridColumn: '1 / -1' };
    const style = {};
    if (field.column) {
      style.gridColumnStart = field.column;
    }
    if (field.span && field.span > 1) {
      style.gridColumn = `span ${Math.min(field.span, columns)}`;
    }
    return style;
  }

  function renderFieldContent(field) {
    const name = field.name ?? field.key;
    const fieldError = externalErrors[name]?.join?.(' ') ?? externalErrors[name] ?? errors[name]?.join?.(' ') ?? errors[name];
    const currentVal = formValues[name] ?? '';
    const basicInputTypes = ['text', 'email', 'number', 'password', 'phone', 'url'];

    if (basicInputTypes.includes(field.type)) {
      return (
        <FormInput
          field={field}
          value={currentVal}
          onChange={(e) => updateValue(name, e.target.value)}
          error={fieldError}
        />
      );
    }

    const fieldLockedValues = name === 'worker_ids' ? lockedWorkerIds : [];
    return (
      <FormField
        field={field}
        value={currentVal}
        error={fieldError}
        onChange={(val) => updateValue(name, val)}
        lockedValues={fieldLockedValues}
      />
    );
  }

  function renderFieldBlock(field, i) {
    const name = field.name ?? field.key;
    const shouldInjectAfter = injectAfterField && (
      (typeof injectAfterField === 'string' && injectAfterField === name) ||
      (Array.isArray(injectAfterField) && injectAfterField.some(entry => entry.fieldKey === name))
    );
    const injectedContent = Array.isArray(injectAfterField)
      ? injectAfterField.find(entry => entry.fieldKey === name)?.content
      : null;

    return (
      <div key={i}>
        {renderFieldContent(field)}
        {shouldInjectAfter && (
          <>
            {typeof injectAfterField === 'string' ? children : injectedContent}
          </>
        )}
      </div>
    );
  }

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center p-4">
      <div
        className="absolute inset-0 bg-black/60 backdrop-blur-sm"
        onClick={(e) => { if (e.target === e.currentTarget) onClose?.(); }}
      />

      <div
        ref={containerRef}
        role="dialog"
        aria-modal="true"
        aria-label={title ?? schemaTitle ?? t('pages.modal.create_title', { name: entityName })}
        className={`relative w-full ${sizeClass} max-h-[85vh] overflow-hidden rounded-xl bg-brand-white shadow-2xl border border-brand-mid/20`}
      >
        {/* Header */}
        <div className="flex items-center justify-between border-b border-brand-mid/20 px-6 py-4">
          <h3 className="text-lg font-semibold text-brand-darkest">{modalTitle}</h3>
          <button
            type="button"
            className="rounded-lg p-1.5 text-brand-mid hover:bg-brand-light hover:text-brand-darkest transition-colors"
            onClick={onClose}
            aria-label={t('pages.datamanager.close_aria')}
          >
            <X className="h-5 w-5" />
          </button>
        </div>

        {/* Form */}
        <form
          ref={formRef}
          onSubmit={handleSubmit}
          noValidate
        >
          {/* Global error */}
          {errors._general && (
            <div className="px-6 pt-2">
              <div className="rounded-lg bg-red-50 p-3 text-sm text-red-600">{errors._general}</div>
            </div>
          )}

          <div className="overflow-y-auto px-6 py-4 max-h-[calc(85vh-8rem)]">
            {columns > 1 ? (
              <>
                <div style={{ display: 'flex', gap: '1rem' }}>
                  <div style={{ flex: 1, minWidth: 0 }}>
                    {visibleFields.filter(f => f.column === 1).map((field, i) => renderFieldBlock(field, i))}
                  </div>
                  <div style={{ flex: 1, minWidth: 0 }}>
                    {visibleFields.filter(f => f.column === 2).map((field, i) => renderFieldBlock(field, i))}
                  </div>
                </div>
                {(() => {
                  const bottomFields = visibleFields.filter(f => !f.column);
                  if (bottomFields.length === 0) return null;
                  return (
                    <div className="grid gap-x-4 mt-6" style={{ gridTemplateColumns: `repeat(${columns}, minmax(0, 1fr))` }}>
                      {bottomFields.map((field, i) => (
                        <div key={i} style={gridStyle(field)}>
                          {renderFieldContent(field)}
                        </div>
                      ))}
                    </div>
                  );
                })()}
              </>
            ) : (
              <div>
                {visibleFields.map((field, i) => (
                  <div key={i}>
                    {renderFieldContent(field)}
                  </div>
                ))}
              </div>
            )}

            {!injectAfterField && children}
          </div>

          <div className="flex items-center justify-end gap-3 border-t border-brand-mid/20 px-6 py-4">
            <button
              type="button"
              className="rounded-lg border border-brand-mid/20 bg-brand-white px-4 py-2 text-sm font-medium text-brand-mid hover:bg-brand-light transition-colors"
              onClick={onClose}
              disabled={saving}
            >
              {t('pages.datamanager.cancel_btn')}
            </button>
            <button
              type="submit"
              disabled={saving}
              className="rounded-lg bg-brand-accent px-4 py-2 text-sm font-medium text-brand-white shadow-sm hover:bg-brand-accent/90 disabled:opacity-50 transition-colors"
            >
              {saving ? t('pages.datamanager.saving_btn') : t('pages.modal.save_entity_btn', { name: entityName })}
            </button>
          </div>
        </form>
      </div>
    </div>
  );
}
