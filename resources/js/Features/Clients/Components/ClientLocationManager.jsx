import { useState } from 'react';
import { Plus, Pencil, Trash2, Star, MapPin, Loader2, Check } from 'lucide-react';
import { useClientLocations } from '@/Hooks/useClientLocations';
import { useToast } from '@/Components/Toast/ToastContext';
import { t } from '@/utils/i18n';
import CascadingParishSelect from '@/Components/Common/CascadingParishSelect';

const csrfToken = () => document.querySelector('meta[name="csrf-token"]')?.content ?? '';

function emptyForm() {
    return {
        name: '',
        is_primary: false,
        parish_id: '',
        postal_code: '',
        street_address: '',
        landmark: '',
        latitude: '',
        longitude: '',
    };
}

function LocationForm({ client, editTarget, onSaved, onCancel, districts, municipalities, parishes }) {
    const [form, setForm] = useState(() =>
        editTarget
            ? {
                name: editTarget.name ?? '',
                is_primary: editTarget.is_primary ?? false,
                parish_id: editTarget.location?.parish_id ?? '',
                postal_code: editTarget.location?.postal_code ?? '',
                street_address: editTarget.location?.street_address ?? '',
                landmark: editTarget.location?.landmark ?? '',
                latitude: editTarget.location?.latitude ?? '',
                longitude: editTarget.location?.longitude ?? '',
            }
            : emptyForm()
    );
    const [submitting, setSubmitting] = useState(false);
    const [errors, setErrors] = useState({});
    const toast = useToast();

    const set = (key, val) => setForm(prev => ({ ...prev, [key]: val }));

    const handleSubmit = async (e) => {
        e.preventDefault();
        setSubmitting(true);
        setErrors({});

        const isEdit = editTarget !== null;
        const url = isEdit
            ? `/api/clients/${client.id}/locations/${editTarget.id}`
            : `/api/clients/${client.id}/locations`;

        try {
            const res = await fetch(url, {
                method: isEdit ? 'PUT' : 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken(),
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify(form),
            });

            const body = await res.json();

            if (res.ok) {
                toast.success(isEdit ? t('pages.client_locations.updated') : t('pages.client_locations.created'));
                onSaved();
            } else if (body.errors) {
                setErrors(body.errors);
            } else {
                toast.error(body.message ?? t('pages.client_locations.save_failed'));
            }
        } catch {
            toast.error(t('pages.client_locations.unexpected_error'));
        } finally {
            setSubmitting(false);
        }
    };

    const inputClass = 'w-full rounded-lg bg-brand-white border border-brand-mid/20 text-brand-darkest px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-accent';
    const labelClass = 'block text-xs font-medium text-brand-mid mb-1';
    const errClass = 'mt-1 text-xs text-red-400';

    return (
        <form
            onSubmit={handleSubmit}
            className="rounded-lg border border-brand-mid/20 bg-brand-white p-4 space-y-4"
        >
            <h4 className="text-sm font-semibold text-brand-darkest">
                {editTarget ? t('pages.client_locations.edit_title') : t('pages.client_locations.add_title')}
            </h4>

            {/* Name + is_primary */}
            <div className="grid grid-cols-2 gap-3">
                <div>
                    <label className={labelClass}>{t('pages.client_locations.field_name')} *</label>
                    <input
                        className={inputClass}
                        value={form.name}
                        onChange={e => set('name', e.target.value)}
                        placeholder="HQ, Armazém Norte…"
                        required
                    />
                    {errors.name && <p className={errClass}>{errors.name[0]}</p>}
                </div>
                <div className="flex items-end pb-1">
                    <label className="flex items-center gap-2 cursor-pointer">
                        <input
                            type="checkbox"
                            className="rounded border-brand-mid/20 bg-brand-white text-brand-accent"
                            checked={form.is_primary}
                            onChange={e => set('is_primary', e.target.checked)}
                        />
                        <span className="text-sm text-brand-mid">{t('pages.client_locations.field_primary')}</span>
                    </label>
                </div>
            </div>

            {/* Address row */}
            <div className="grid grid-cols-2 gap-3">
                <div>
                    <label className={labelClass}>{t('pages.client_locations.field_street')}</label>
                    <input
                        className={inputClass}
                        value={form.street_address}
                        onChange={e => set('street_address', e.target.value)}
                        placeholder="Rua da Liberdade, 45"
                    />
                    {errors.street_address && <p className={errClass}>{errors.street_address[0]}</p>}
                </div>
                <div>
                    <label className={labelClass}>{t('pages.client_locations.field_postal')}</label>
                    <input
                        className={inputClass}
                        value={form.postal_code}
                        onChange={e => set('postal_code', e.target.value)}
                        placeholder="1000-001"
                    />
                    {errors.postal_code && <p className={errClass}>{errors.postal_code[0]}</p>}
                </div>
            </div>

            {/* Cascading District → Municipality → Parish */}
            <CascadingParishSelect
                districts={districts}
                municipalities={municipalities}
                parishes={parishes}
                value={form.parish_id}
                onChange={parishId => set('parish_id', parishId)}
                error={errors.parish_id?.[0]}
            />

            {/* Landmark */}
            <div>
                <label className={labelClass}>{t('pages.client_locations.field_landmark')}</label>
                <input
                    className={inputClass}
                    value={form.landmark}
                    onChange={e => set('landmark', e.target.value)}
                    placeholder="Junto à entrada principal"
                />
                {errors.landmark && <p className={errClass}>{errors.landmark[0]}</p>}
            </div>

            {/* Coordinates */}
            <div className="grid grid-cols-2 gap-3">
                <div>
                    <label className={labelClass}>{t('pages.client_locations.field_lat')}</label>
                    <input
                        type="number"
                        step="any"
                        className={inputClass}
                        value={form.latitude}
                        onChange={e => set('latitude', e.target.value)}
                        placeholder="38.7167"
                    />
                    {errors.latitude && <p className={errClass}>{errors.latitude[0]}</p>}
                </div>
                <div>
                    <label className={labelClass}>{t('pages.client_locations.field_lon')}</label>
                    <input
                        type="number"
                        step="any"
                        className={inputClass}
                        value={form.longitude}
                        onChange={e => set('longitude', e.target.value)}
                        placeholder="-9.1388"
                    />
                    {errors.longitude && <p className={errClass}>{errors.longitude[0]}</p>}
                </div>
            </div>

            <div className="flex justify-end gap-2 pt-2">
                <button
                    type="button"
                    onClick={onCancel}
                    className="px-3 py-1.5 text-sm rounded-lg border border-brand-mid/20 text-brand-mid hover:bg-brand-light transition-colors"
                >
                    {t('pages.client_locations.cancel')}
                </button>
                <button
                    type="submit"
                    disabled={submitting}
                    className="flex items-center gap-1.5 px-3 py-1.5 text-sm rounded-lg bg-brand-accent text-brand-white hover:bg-brand-accent/90 disabled:opacity-50 transition-colors"
                >
                    {submitting ? <Loader2 className="h-3.5 w-3.5 animate-spin" /> : <Check className="h-3.5 w-3.5" />}
                    {submitting ? t('pages.client_locations.saving') : t('pages.client_locations.save')}
                </button>
            </div>
        </form>
    );
}

export default function ClientLocationManager({ client, districts = [], municipalities = [], parishes = [] }) {
    const { locations, loading, error, refetch } = useClientLocations(client?.id);
    const [showForm, setShowForm] = useState(false);
    const [editTarget, setEditTarget] = useState(null);
    const toast = useToast();

    const openCreate = () => { setEditTarget(null); setShowForm(true); };
    const openEdit = (loc) => { setEditTarget(loc); setShowForm(true); };
    const closeForm = () => { setShowForm(false); setEditTarget(null); };

    const handleSaved = () => { closeForm(); refetch(); };

    const handleDelete = async (loc) => {
        if (!window.confirm(`${t('pages.client_locations.confirm_delete')} "${loc.name}"?`)) return;

        try {
            const res = await fetch(`/api/clients/${client.id}/locations/${loc.id}`, {
                method: 'DELETE',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken(),
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });

            if (res.ok) {
                toast.success(t('pages.client_locations.deleted'));
                refetch();
            } else {
                toast.error(t('pages.client_locations.delete_failed'));
            }
        } catch {
            toast.error(t('pages.client_locations.unexpected_error'));
        }
    };

    if (!client) return null;

    return (
        <div className="space-y-4">
            {/* Header row */}
            <div className="flex items-center justify-between">
                <p className="text-xs text-brand-mid">
                    {locations.length} {t('pages.client_locations.location_count')}
                </p>
                {!showForm && (
                    <button
                        onClick={openCreate}
                        className="flex items-center gap-1.5 px-3 py-1.5 text-sm rounded-lg bg-brand-accent text-brand-white hover:bg-brand-accent/90 transition-colors"
                    >
                        <Plus className="h-3.5 w-3.5" />
                        {t('pages.client_locations.add_btn')}
                    </button>
                )}
            </div>

            {/* Inline form */}
            {showForm && (
                <LocationForm
                    client={client}
                    editTarget={editTarget}
                    districts={districts}
                    municipalities={municipalities}
                    parishes={parishes}
                    onSaved={handleSaved}
                    onCancel={closeForm}
                />
            )}

            {/* Loading */}
            {loading && (
                <div className="flex justify-center py-8">
                    <Loader2 className="h-5 w-5 animate-spin text-slate-500" />
                </div>
            )}

            {/* Error */}
            {error && (
                <p className="text-sm text-red-400">{error}</p>
            )}

            {/* Empty state */}
            {!loading && !error && locations.length === 0 && !showForm && (
                <div className="flex flex-col items-center justify-center py-10 text-slate-500">
                    <MapPin className="h-8 w-8 mb-2 opacity-40" />
                    <p className="text-sm">{t('pages.client_locations.empty')}</p>
                </div>
            )}

            {/* Location cards */}
            {locations.map(loc => (
                <div
                    key={loc.id}
                    className="rounded-lg border border-brand-mid/20 bg-brand-white p-4"
                >
                    <div className="flex items-start justify-between gap-2">
                        <div className="flex items-center gap-2 min-w-0">
                            {loc.is_primary && (
                                <Star className="h-3.5 w-3.5 text-yellow-400 flex-shrink-0" fill="currentColor" />
                            )}
                            <span className="text-sm font-semibold text-brand-darkest truncate">{loc.name}</span>
                            {loc.is_primary && (
                                <span className="inline-flex items-center px-1.5 py-0.5 text-xs rounded bg-yellow-500/20 text-yellow-300">
                                    {t('pages.client_locations.primary_badge')}
                                </span>
                            )}
                        </div>
                        <div className="flex items-center gap-1 flex-shrink-0">
                            <button
                                onClick={() => openEdit(loc)}
                                className="p-1.5 rounded text-brand-mid hover:text-brand-darkest hover:bg-brand-light transition-colors"
                                title={t('pages.client_locations.edit_btn')}
                            >
                                <Pencil className="h-3.5 w-3.5" />
                            </button>
                            <button
                                onClick={() => handleDelete(loc)}
                                className="p-1.5 rounded text-brand-mid hover:text-red-400 hover:bg-brand-light transition-colors"
                                title={t('pages.client_locations.delete_btn')}
                            >
                                <Trash2 className="h-3.5 w-3.5" />
                            </button>
                        </div>
                    </div>

                    {loc.location && (
                        <div className="mt-2 space-y-0.5">
                            {loc.location.street_address && (
                                <p className="text-xs text-brand-mid flex items-center gap-1.5">
                                    <MapPin className="h-3 w-3 flex-shrink-0" />
                                    {loc.location.street_address}
                                    {loc.location.postal_code ? `, ${loc.location.postal_code}` : ''}
                                </p>
                            )}
                            {loc.location.parish?.name && (
                                <p className="text-xs text-brand-mid pl-4">{loc.location.parish.name}</p>
                            )}
                            {loc.location.landmark && (
                                <p className="text-xs text-brand-mid pl-4 italic">{loc.location.landmark}</p>
                            )}
                        </div>
                    )}
                </div>
            ))}
        </div>
    );
}
