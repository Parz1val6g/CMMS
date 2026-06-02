import { useState, useCallback, useEffect, useRef } from 'react';
import { router } from '@inertiajs/react';
import { X, Plus, Trash2, Star, Loader2, AlertCircle } from 'lucide-react';
import { t } from '@/utils/i18n';
import { useToast } from '@/Components/Toast/ToastContext';
import { useFocusTrap } from '@/Hooks/useFocusTrap';
import { useBodyLock } from '@/Hooks/useBodyLock';
import { useApiRequest } from '@/composables/useApiRequest';
import CascadingParishSelect from '@/Components/Common/CascadingParishSelect';

const inputClass =
    'w-full rounded-lg bg-brand-white border border-brand-mid/20 text-brand-darkest px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-accent placeholder-brand-mid';
const labelClass = 'block text-xs font-medium text-brand-mid mb-1';
const errClass   = 'mt-1 text-xs text-red-500';
const errMsg = (e) => Array.isArray(e) ? e[0] : e;

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
                : 'border-brand-mid/20 bg-brand-white'
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
                        <p className={errClass}>{errMsg(errors[`locations.${index}.name`])}</p>
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
                                : 'bg-brand-light text-brand-mid hover:text-brand-darkest hover:bg-brand-mid/20'
                        }`}
                    >
                        <Star className="h-4 w-4" fill={loc.is_primary ? 'currentColor' : 'none'} />
                    </button>
                    <span className="text-xs text-brand-mid">{t('pages.client_create.hq_label')}</span>
                </div>

                {canRemove && (
                    <button
                        type="button"
                        onClick={onRemove}
                        className="mt-4 p-2 rounded-lg text-brand-mid hover:text-red-400 hover:bg-red-500/10 transition-colors"
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
                        placeholder={t('pages.client_locations.street_placeholder')}
                    />
                </div>
                <div>
                    <label className={labelClass}>{t('pages.client_locations.field_postal')}</label>
                    <input
                        className={inputClass}
                        value={loc.postal_code}
                        onChange={e => set('postal_code', e.target.value)}
                        placeholder={t('pages.client_locations.postal_placeholder')}
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
                error={errors?.[`locations.${index}.parish_id`] ? errMsg(errors[`locations.${index}.parish_id`]) : undefined}
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
    const [errors, setErrors]   = useState({});
    const [client, setClient]   = useState({ nif: '', first_name: '', last_name: '', email: '', phone: '' });
    const [locations, setLocations] = useState([{ ...emptyLocation(), name: t('pages.client_create.hq_default_name'), is_primary: true }]);
    const { submit, loading: saving } = useApiRequest();
    const toast = useToast();

    useFocusTrap(containerRef, open);
    useBodyLock(open);

    /* Reset state when modal opens */
    useEffect(() => {
        if (open) {
            setClient({ nif: '', first_name: '', last_name: '', email: '', phone: '' });
            setLocations([{ ...emptyLocation(), name: 'Sede', is_primary: true }]);
            setErrors({});
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

    const handleSubmit = e => {
        e.preventDefault();
        if (saving) return;

        const newErrors = {};
        const req = t('pages.validation.required');
        if (!client.nif.trim())        newErrors.nif        = req;
        if (!client.first_name.trim()) newErrors.first_name = req;
        if (!client.last_name.trim())  newErrors.last_name  = req;
        if (!client.email.trim())      newErrors.email      = req;
        locations.forEach((loc, i) => {
            if (!loc.name.trim()) newErrors[`locations.${i}.name`] = req;
        });

        if (!hasSede) {
            newErrors.locations = t('pages.client_create.hq_required_error');
        }

        if (Object.keys(newErrors).length > 0) {
            setErrors(newErrors);
            return;
        }

        setErrors({});

        const payload = {
            ...client,
            locations: locations.map(({ _id, ...rest }) => rest),
        };

        submit(storeUrl, {
            method: 'POST',
            body: payload,
            onSuccess: () => {
                toast.success(t('pages.client_create.success'));
                onClose();
                onCreated?.();
                router.reload();
            },
            onError: (msg, errs) => {
                if (errs) {
                    setErrors(errs);
                } else {
                    toast.error(msg || t('pages.client_create.error_generic'));
                }
            },
        });
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
                className="relative w-full max-w-2xl max-h-[92vh] overflow-hidden rounded-xl bg-brand-white shadow-2xl border border-brand-mid/20 flex flex-col"
            >
                {/* Header */}
                <div className="flex items-center justify-between border-b border-brand-mid/20 px-6 py-4 shrink-0">
                    <h3 className="text-lg font-semibold text-brand-darkest">{t('pages.client_create.modal_title')}</h3>
                    <button
                        type="button"
                        onClick={onClose}
                        className="rounded-lg p-1.5 text-brand-mid hover:bg-brand-light hover:text-brand-darkest transition-colors"
                        aria-label={t('pages.datamanager.close_aria')}
                    >
                        <X className="h-5 w-5" />
                    </button>
                </div>

                <form onSubmit={handleSubmit} noValidate className="flex flex-col flex-1 overflow-hidden">
                    <div className="overflow-y-auto px-6 py-5 space-y-6 flex-1">

                        {/* ── Section 1: Client Info ── */}
                        <section>
                            <h4 className="text-xs font-semibold text-brand-mid uppercase tracking-wider mb-3">
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
                                    {errors.nif && <p className={errClass}>{errMsg(errors.nif)}</p>}
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
                                        {errors.first_name && <p className={errClass}>{errMsg(errors.first_name)}</p>}
                                    </div>
                                    <div>
                                        <label className={labelClass}>{t('pages.client_create.label_last_name')} *</label>
                                        <input
                                            className={inputClass}
                                            value={client.last_name}
                                            onChange={e => setField('last_name', e.target.value)}
                                            required
                                        />
                                        {errors.last_name && <p className={errClass}>{errMsg(errors.last_name)}</p>}
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
                                    {errors.email && <p className={errClass}>{errMsg(errors.email)}</p>}
                                </div>

                                <div>
                                    <label className={labelClass}>{t('pages.client_create.label_phone')}</label>
                                    <input
                                        className={inputClass}
                                        value={client.phone}
                                        onChange={e => setField('phone', e.target.value)}
                                        placeholder="+351 910 000 000"
                                    />
                                    {errors.phone && <p className={errClass}>{errMsg(errors.phone)}</p>}
                                </div>
                            </div>
                        </section>

                        {/* ── Section 2: Locations ── */}
                        <section>
                            <div className="flex items-center justify-between mb-3">
                                <div>
                                    <h4 className="text-xs font-semibold text-brand-mid uppercase tracking-wider">
                                        {t('pages.client_create.section_locations')}
                                    </h4>
                                    <p className="text-xs text-brand-mid mt-0.5">
                                        {t('pages.client_create.section_locations_hint')}
                                    </p>
                                </div>
                                <button
                                    type="button"
                                    onClick={addLocation}
                                    className="flex items-center gap-1.5 px-3 py-1.5 text-xs rounded-lg bg-brand-light text-brand-mid hover:bg-brand-mid/20 hover:text-brand-darkest transition-colors"
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
                                <p className={errClass + ' mb-2'}>{errMsg(errors.locations)}</p>
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
                    <div className="flex items-center justify-between gap-3 border-t border-brand-mid/20 px-6 py-4 shrink-0">
                        {/* Sede status indicator */}
                        <div className={`flex items-center gap-1.5 text-xs text-brand-mid ${hasSede ? 'text-yellow-400' : 'text-brand-mid'}`}>
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
                                className="rounded-lg border border-brand-mid/20 bg-brand-light px-4 py-2 text-sm font-medium text-brand-mid hover:bg-brand-mid/20 transition-colors disabled:opacity-50"
                            >
                                {t('pages.datamanager.cancel_btn')}
                            </button>
                            <button
                                type="submit"
                                disabled={saving || !hasSede}
                                className="flex items-center gap-2 rounded-lg bg-brand-accent px-4 py-2 text-sm font-medium text-brand-white shadow-sm hover:bg-brand-accent/90 disabled:opacity-50 transition-colors"
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

