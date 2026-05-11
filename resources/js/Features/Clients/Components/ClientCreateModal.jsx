import { useState, useCallback, useEffect, useRef } from 'react';
import { X, Plus, Trash2, Star, Loader2, AlertCircle } from 'lucide-react';
import { t } from '@/utils/i18n';
import { useToast } from '@/Components/Toast/ToastContext';
import { useFocusTrap } from '@/Hooks/useFocusTrap';
import { useBodyLock } from '@/Hooks/useBodyLock';
import CascadingParishSelect from '@/Components/Common/CascadingParishSelect';

const csrfToken = () => document.querySelector('meta[name="csrf-token"]')?.content ?? '';

const inputClass =
    'w-full rounded-lg bg-slate-700 border border-slate-600 text-slate-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 placeholder-slate-500';
const labelClass = 'block text-xs font-medium text-slate-400 mb-1';
const errClass   = 'mt-1 text-xs text-red-400';

function emptyLocation() {
    return {
        _id:           Math.random().toString(36).slice(2),
        name:          '',
        is_primary:    false,
        street_address: '',
        postal_code:   '',
        parish_id:     '',
        landmark:      '',
        latitude:      '',
        longitude:     '',
    };
}

function LocationRow({ loc, index, districts, municipalities, parishes, onChange, onRemove, canRemove, errors }) {
    const set = (key, val) => onChange({ ...loc, [key]: val });

    return (
        <div className={`rounded-lg border p-4 space-y-3 ${
            loc.is_primary
                ? 'border-yellow-500/40 bg-yellow-500/5'
                : 'border-slate-700 bg-slate-800/40'
        }`}>
            {/* Row header */}
            <div className="flex items-center gap-3">
                <div className="flex-1">
                    <label className={labelClass}>
                        {t('pages.client_locations.field_name')} *
                    </label>
                    <input
                        className={inputClass}
                        value={loc.name}
                        onChange={e => set('name', e.target.value)}
                        placeholder={t('pages.client_create.loc_name_placeholder')}
                        required
                    />
                    {errors?.[`locations.${index}.name`] && (
                        <p className={errClass}>{errors[`locations.${index}.name`][0]}</p>
                    )}
                </div>

                {/* Sede (primary) toggle */}
                <div className="flex flex-col items-center gap-1 pt-4">
                    <button
                        type="button"
                        onClick={() => set('is_primary', !loc.is_primary)}
                        title={t('pages.client_create.hq_toggle_title')}
                        className={`p-2 rounded-lg transition-colors ${
                            loc.is_primary
                                ? 'bg-yellow-500/20 text-yellow-400 hover:bg-yellow-500/30'
                                : 'bg-slate-700 text-slate-500 hover:text-slate-300 hover:bg-slate-600'
                        }`}
                    >
                        <Star className="h-4 w-4" fill={loc.is_primary ? 'currentColor' : 'none'} />
                    </button>
                    <span className="text-xs text-slate-500">{t('pages.client_create.hq_label')}</span>
                </div>

                {canRemove && (
                    <button
                        type="button"
                        onClick={onRemove}
                        className="mt-4 p-2 rounded-lg text-slate-500 hover:text-red-400 hover:bg-red-500/10 transition-colors"
                        title={t('pages.client_locations.delete_btn')}
                    >
                        <Trash2 className="h-4 w-4" />
                    </button>
                )}
            </div>

            {/* Address fields */}
            <div className="grid grid-cols-2 gap-3">
                <div>
                    <label className={labelClass}>{t('pages.client_locations.field_street')}</label>
                    <input
                        className={inputClass}
                        value={loc.street_address}
                        onChange={e => set('street_address', e.target.value)}
                        placeholder="Rua da Paz, 123"
                    />
                </div>
                <div>
                    <label className={labelClass}>{t('pages.client_locations.field_postal')}</label>
                    <input
                        className={inputClass}
                        value={loc.postal_code}
                        onChange={e => set('postal_code', e.target.value)}
                        placeholder="3530-001"
                    />
                </div>
            </div>

            {/* Cascading District → Municipality → Parish */}
            <CascadingParishSelect
                districts={districts}
                municipalities={municipalities}
                parishes={parishes}
                value={loc.parish_id}
                onChange={parishId => set('parish_id', parishId)}
                error={errors?.[`locations.${index}.parish_id`]?.[0]}
            />

            <div>
                <label className={labelClass}>{t('pages.client_locations.field_landmark')}</label>
                <input
                    className={inputClass}
                    value={loc.landmark}
                    onChange={e => set('landmark', e.target.value)}
                    placeholder={t('pages.client_create.landmark_placeholder')}
                />
            </div>
        </div>
    );
}

export default function ClientCreateModal({ open, onClose, storeUrl, districts = [], municipalities = [], parishes = [], onCreated }) {
    const containerRef = useRef(null);
    const [saving, setSaving]   = useState(false);
    const [errors, setErrors]   = useState({});
    const [client, setClient]   = useState({ nif: '', first_name: '', last_name: '', email: '', phone: '' });
    const [locations, setLocations] = useState([{ ...emptyLocation(), name: t('pages.client_create.hq_default_name'), is_primary: true }]);
    const toast = useToast();

    useFocusTrap(containerRef, open);
    useBodyLock(open);

    /* Reset state when modal opens */
    useEffect(() => {
        if (open) {
            setClient({ nif: '', first_name: '', last_name: '', email: '', phone: '' });
            setLocations([{ ...emptyLocation(), name: 'Sede', is_primary: true }]);
            setErrors({});
            setSaving(false);
        }
    }, [open]);

    /* Escape key */
    useEffect(() => {
        if (!open) return;
        const handler = e => { if (e.key === 'Escape') onClose(); };
        document.addEventListener('keydown', handler);
        return () => document.removeEventListener('keydown', handler);
    }, [open, onClose]);

    const setField = (key, val) => setClient(prev => ({ ...prev, [key]: val }));

    const updateLocation = useCallback((index, updated) => {
        setLocations(prev => {
            const next = [...prev];
            // If marking this one as primary, unmark others
            if (updated.is_primary) {
                next.forEach((l, i) => { if (i !== index) l.is_primary = false; });
            }
            next[index] = updated;
            return next;
        });
    }, []);

    const addLocation = useCallback(() => {
        setLocations(prev => [...prev, emptyLocation()]);
    }, []);

    const removeLocation = useCallback((index) => {
        setLocations(prev => {
            const next = prev.filter((_, i) => i !== index);
            // If removed location was primary and others exist, auto-mark first as primary
            const hasPrimary = next.some(l => l.is_primary);
            if (!hasPrimary && next.length > 0) next[0].is_primary = true;
            return next;
        });
    }, []);

    const hasSede = locations.some(l => l.is_primary);

    const handleSubmit = async e => {
        e.preventDefault();
        if (saving) return;

        // Client-side sede guard
        if (!hasSede) {
            setErrors(prev => ({ ...prev, locations: [t('pages.client_create.hq_required_error')] }));
            return;
        }

        setSaving(true);
        setErrors({});

        const payload = {
            ...client,
            locations: locations.map(({ _id, ...rest }) => rest),
        };

        try {
            const res = await fetch(storeUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken(),
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify(payload),
            });

            const body = await res.json();

            if (res.ok) {
                toast.success(t('pages.client_create.success'));
                onClose();
                onCreated?.();
                setTimeout(() => window.location.reload(), 300);
            } else if (body.errors) {
                setErrors(body.errors);
            } else {
                toast.error(body.message ?? t('pages.client_create.error_generic'));
            }
        } catch {
            toast.error(t('pages.client_create.error_generic'));
        } finally {
            setSaving(false);
        }
    };

    if (!open) return null;

    return (
        <div className="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div
                className="absolute inset-0 bg-black/60 backdrop-blur-sm"
                onClick={e => { if (e.target === e.currentTarget) onClose(); }}
            />

            <div
                ref={containerRef}
                role="dialog"
                aria-modal="true"
                aria-label={t('pages.client_create.modal_title')}
                className="relative w-full max-w-2xl max-h-[92vh] overflow-hidden rounded-xl bg-slate-800 shadow-2xl border border-slate-700 flex flex-col"
            >
                {/* Header */}
                <div className="flex items-center justify-between border-b border-slate-700 px-6 py-4 shrink-0">
                    <h3 className="text-lg font-semibold text-white">{t('pages.client_create.modal_title')}</h3>
                    <button
                        type="button"
                        onClick={onClose}
                        className="rounded-lg p-1.5 text-slate-400 hover:bg-slate-700 hover:text-white transition-colors"
                        aria-label={t('pages.datamanager.close_aria')}
                    >
                        <X className="h-5 w-5" />
                    </button>
                </div>

                <form onSubmit={handleSubmit} className="flex flex-col flex-1 overflow-hidden">
                    <div className="overflow-y-auto px-6 py-5 space-y-6 flex-1">

                        {/* ── Section 1: Client Info ── */}
                        <section>
                            <h4 className="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-3">
                                {t('pages.client_create.section_info')}
                            </h4>
                            <div className="space-y-3">
                                <div>
                                    <label className={labelClass}>{t('pages.client_create.label_nif')} *</label>
                                    <input
                                        className={inputClass}
                                        value={client.nif}
                                        onChange={e => setField('nif', e.target.value)}
                                        placeholder="500 123 456"
                                        required
                                    />
                                    {errors.nif && <p className={errClass}>{errors.nif[0]}</p>}
                                </div>

                                <div className="grid grid-cols-2 gap-3">
                                    <div>
                                        <label className={labelClass}>{t('pages.client_create.label_first_name')} *</label>
                                        <input
                                            className={inputClass}
                                            value={client.first_name}
                                            onChange={e => setField('first_name', e.target.value)}
                                            required
                                        />
                                        {errors.first_name && <p className={errClass}>{errors.first_name[0]}</p>}
                                    </div>
                                    <div>
                                        <label className={labelClass}>{t('pages.client_create.label_last_name')} *</label>
                                        <input
                                            className={inputClass}
                                            value={client.last_name}
                                            onChange={e => setField('last_name', e.target.value)}
                                            required
                                        />
                                        {errors.last_name && <p className={errClass}>{errors.last_name[0]}</p>}
                                    </div>
                                </div>

                                <div>
                                    <label className={labelClass}>{t('pages.client_create.label_email')} *</label>
                                    <input
                                        type="email"
                                        className={inputClass}
                                        value={client.email}
                                        onChange={e => setField('email', e.target.value)}
                                        required
                                    />
                                    {errors.email && <p className={errClass}>{errors.email[0]}</p>}
                                </div>

                                <div>
                                    <label className={labelClass}>{t('pages.client_create.label_phone')}</label>
                                    <input
                                        className={inputClass}
                                        value={client.phone}
                                        onChange={e => setField('phone', e.target.value)}
                                        placeholder="+351 910 000 000"
                                    />
                                    {errors.phone && <p className={errClass}>{errors.phone[0]}</p>}
                                </div>
                            </div>
                        </section>

                        {/* ── Section 2: Locations ── */}
                        <section>
                            <div className="flex items-center justify-between mb-3">
                                <div>
                                    <h4 className="text-xs font-semibold text-slate-400 uppercase tracking-wider">
                                        {t('pages.client_create.section_locations')}
                                    </h4>
                                    <p className="text-xs text-slate-500 mt-0.5">
                                        {t('pages.client_create.section_locations_hint')}
                                    </p>
                                </div>
                                <button
                                    type="button"
                                    onClick={addLocation}
                                    className="flex items-center gap-1.5 px-3 py-1.5 text-xs rounded-lg bg-slate-700 text-slate-300 hover:bg-slate-600 hover:text-white transition-colors"
                                >
                                    <Plus className="h-3.5 w-3.5" />
                                    {t('pages.client_locations.add_btn')}
                                </button>
                            </div>

                            {/* Sede missing warning */}
                            {!hasSede && (
                                <div className="flex items-center gap-2 rounded-lg bg-yellow-500/10 border border-yellow-500/30 px-3 py-2 mb-3">
                                    <AlertCircle className="h-4 w-4 text-yellow-400 flex-shrink-0" />
                                    <p className="text-xs text-yellow-300">{t('pages.client_create.hq_missing_hint')}</p>
                                </div>
                            )}

                            {errors.locations && (
                                <p className={errClass + ' mb-2'}>{errors.locations[0]}</p>
                            )}

                            <div className="space-y-3">
                                {locations.map((loc, i) => (
                                    <LocationRow
                                        key={loc._id}
                                        loc={loc}
                                        index={i}
                                        districts={districts}
                                        municipalities={municipalities}
                                        parishes={parishes}
                                        onChange={updated => updateLocation(i, updated)}
                                        onRemove={() => removeLocation(i)}
                                        canRemove={locations.length > 1}
                                        errors={errors}
                                    />
                                ))}
                            </div>
                        </section>
                    </div>

                    {/* Footer */}
                    <div className="flex items-center justify-between gap-3 border-t border-slate-700 px-6 py-4 shrink-0">
                        {/* Sede status indicator */}
                        <div className={`flex items-center gap-1.5 text-xs ${hasSede ? 'text-yellow-400' : 'text-slate-500'}`}>
                            <Star className="h-3.5 w-3.5" fill={hasSede ? 'currentColor' : 'none'} />
                            {hasSede
                                ? t('pages.client_create.hq_set')
                                : t('pages.client_create.hq_not_set')}
                        </div>

                        <div className="flex items-center gap-3">
                            <button
                                type="button"
                                onClick={onClose}
                                disabled={saving}
                                className="rounded-lg border border-slate-600 bg-slate-700 px-4 py-2 text-sm font-medium text-slate-300 hover:bg-slate-600 transition-colors disabled:opacity-50"
                            >
                                {t('pages.datamanager.cancel_btn')}
                            </button>
                            <button
                                type="submit"
                                disabled={saving || !hasSede}
                                className="flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 disabled:opacity-50 transition-colors"
                            >
                                {saving && <Loader2 className="h-4 w-4 animate-spin" />}
                                {saving ? t('pages.client_locations.saving') : t('pages.client_create.submit_btn')}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    );
}
