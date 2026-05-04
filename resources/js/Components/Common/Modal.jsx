import { useEffect, useRef, useState, useMemo } from 'react';
import FormField from '@/Components/Common/FormField';
import FormInput from '@/Components/Form/FormInput';
import { X } from 'lucide-react';
import { useToast } from '@/Components/Toast/ToastContext';

const csrfToken = () => document.querySelector('meta[name="csrf-token"]')?.content ?? '';

/* ── Fields to hide per workflow type ────────────────────────── */
const LOAN_HIDDEN = new Set([
  'service_type_id', 'priority',
  'section-photo', 'photo',
  'section-location', 'parish_id', 'street', 'reference_point', 'postal_code',
  'section-map', 'location',
]);

function collectFormData(form, fields) {
  const data = {};
  fields.forEach((f) => {
    const name = f.name ?? f.key;
    const el = form.elements[name];
    if (!el) return;
    if (f.multiple || f.type === 'multiselect') {
      const checked = form.querySelectorAll(`input[name="${CSS.escape(name)}"]:checked`);
      data[name] = Array.from(checked).map((c) => c.value);
    } else if (f.type === 'map' || f.type === 'map-picker') {
      const latField = f.metadata?.latField ?? 'latitude';
      const lngField = f.metadata?.lngField ?? 'longitude';
      if (form.elements[latField]) data[latField] = form.elements[latField].value;
      if (form.elements[lngField]) data[lngField] = form.elements[lngField].value;
    } else {
      data[name] = el.value;
    }
  });
  return data;
}

export default function Modal({ entityName = 'Record', title, formSchema = [], routes = {}, size = '', open, onClose, onSubmit: externalSubmit, children }) {
  const formRef = useRef(null);
  const [saving, setSaving] = useState(false);
  const [errors, setErrors] = useState({});
  const [workflowType, setWorkflowType] = useState('regular');
  const toast = useToast();
  const fields = Array.isArray(formSchema) ? formSchema : (formSchema?.inputs ?? []);

  /* ── Track workflow_type changes via DOM delegation ──────── */
  useEffect(() => {
    if (!open) { setWorkflowType('regular'); return; }

    const findAndWatch = () => {
      const el = document.querySelector('select[name="workflow_type"]');
      if (!el) return false;

      // Remove stale listener before re-adding
      el._wtHandler?._remove?.();
      const handler = () => setWorkflowType(el.value);
      handler._remove = () => el.removeEventListener('change', handler);
      el._wtHandler = handler;
      el.addEventListener('change', handler);
      setWorkflowType(el.value);
      return true;
    };

    if (!findAndWatch()) {
      // Select not yet rendered — poll via MutationObserver
      const observer = new MutationObserver(() => findAndWatch());
      observer.observe(document.body, { childList: true, subtree: true });
      return () => observer.disconnect();
    }

    return () => {
      const handler = document.querySelector('select[name="workflow_type"]')?._wtHandler;
      handler?._remove?.();
    };
  }, [open]);

  /* ── Compute visible fields based on workflow type ───────── */
  const visibleFields = useMemo(() => {
    if (workflowType !== 'loan') return fields;
    return fields.filter(f => {
      const key = f.key ?? f.name ?? '';
      return !LOAN_HIDDEN.has(key);
    });
  }, [fields, workflowType]);

  useEffect(() => {
    const handler = (e) => { if (e.key === 'Escape') onClose?.(); };
    if (open) document.addEventListener('keydown', handler);
    return () => document.removeEventListener('keydown', handler);
  }, [open, onClose]);

  useEffect(() => {
    if (open) { setErrors({}); setSaving(false); }
  }, [open]);

  useEffect(() => {
    if (open) document.body.style.overflow = 'hidden';
    else document.body.style.overflow = '';
    return () => { document.body.style.overflow = ''; };
  }, [open]);

  const handleSubmit = async (e) => {
    e.preventDefault();
    if (!routes.store) return;

    // If an external onSubmit was provided, delegate to it
    if (externalSubmit) {
      externalSubmit(e);
      return;
    }

    setSaving(true);
    setErrors({});

    const data = collectFormData(e.currentTarget, fields);

    try {
      const res = await fetch(routes.store, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-CSRF-TOKEN': csrfToken(),
          'X-Requested-With': 'XMLHttpRequest',
        },
        body: JSON.stringify(data),
      });

      const body = await res.json();

      if (res.ok) {
        toast.success(`${entityName} saved successfully!`);
        onClose?.();
        // Small delay to let user see the toast before reload
        setTimeout(() => {
          window.location.reload();
        }, 500);
      } else {
        if (body.errors) {
          setErrors(body.errors);
        } else {
          const errorMsg = body.message ?? 'Failed to save. Please try again.';
          setErrors({ _general: errorMsg });
        }
      }
    } catch (err) {
      const errorMsg = err?.message || 'An unexpected error occurred.';
      setErrors({ _general: errorMsg });
    } finally {
      setSaving(false);
    }
  };

  if (!open) return null;

  const sizeMap = { xl: 'max-w-5xl', lg: 'max-w-3xl', sm: 'max-w-md' };
  const sizeClass = sizeMap[size] ?? 'max-w-lg';
  const modalTitle = title ?? `Create ${entityName}`;

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center p-4">
      <div
        className="absolute inset-0 bg-black/60 backdrop-blur-sm"
        onClick={(e) => { if (e.target === e.currentTarget) onClose?.(); }}
      />

      <div className={`relative w-full ${sizeClass} max-h-[90vh] overflow-hidden rounded-xl bg-slate-800 shadow-2xl border border-slate-700`}>
        {/* Header */}
        <div className="flex items-center justify-between border-b border-slate-700 px-6 py-4">
          <h3 className="text-lg font-semibold text-white">{modalTitle}</h3>
          <button
            type="button"
            className="rounded-lg p-1.5 text-slate-400 hover:bg-slate-700 hover:text-white transition-colors"
            onClick={onClose}
            aria-label="Close"
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
              <div className="rounded-lg bg-red-500/10 p-3 text-sm text-red-300">{errors._general}</div>
            </div>
          )}

          <div className="overflow-y-auto px-6 py-4 max-h-[60vh] space-y-4">
            {visibleFields.map((field, i) => {
              const name = field.name ?? field.key;
              const fieldError = errors[name]?.join?.(' ') ?? errors[name];
              
              // Use FormInput for basic input types
              const basicInputTypes = ['text', 'email', 'number', 'password', 'phone', 'url'];
              if (basicInputTypes.includes(field.type)) {
                return (
                  <FormInput
                    key={i}
                    field={field}
                    value={field.value || ''}
                    onChange={(e) => {
                      // Track in form for submission
                      const form = formRef.current;
                      if (form && form.elements[name]) {
                        form.elements[name].value = e.target.value;
                      }
                    }}
                    error={fieldError}
                  />
                );
              }

              // Use FormField for complex types (map, select, file, etc)
              return (
                <FormField
                  key={i}
                  field={field}
                  value={field.value || ''}
                  error={fieldError}
                />
              );
            })}
          </div>

          <div className="flex items-center justify-end gap-3 border-t border-slate-700 px-6 py-4">
            <button
              type="button"
              className="rounded-lg border border-slate-600 bg-slate-700 px-4 py-2 text-sm font-medium text-slate-300 hover:bg-slate-600 transition-colors"
              onClick={onClose}
              disabled={saving}
            >
              Cancel
            </button>
            <button
              type="submit"
              disabled={saving}
              className="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 disabled:opacity-50 transition-colors"
            >
              {saving ? 'Saving...' : `Save ${entityName}`}
            </button>
          </div>
        </form>
      </div>
    </div>
  );
}
