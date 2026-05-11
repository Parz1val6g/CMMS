import { useState, useEffect, useMemo } from 'react';
import { t } from '@/utils/i18n';

const selectClass =
    'w-full rounded-lg bg-slate-700 border border-slate-600 text-slate-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500';
const labelClass = 'block text-xs font-medium text-slate-400 mb-1';
const errClass   = 'mt-1 text-xs text-red-400';

/**
 * Three-level cascading selector: District → Municipality → Parish.
 *
 * Props:
 *   districts      [{value, label}]
 *   municipalities [{value, label, district_id}]
 *   parishes       [{value, label, municipality_id}]
 *   value          current parish_id (string | '')
 *   onChange       (parishId: string) => void
 *   error          string | undefined   — shown below the parish select
 */
export default function CascadingParishSelect({ districts, municipalities, parishes, value, onChange, error }) {
    // Derive the initial district/municipality from the current parish value
    const [districtId, setDistrictId]         = useState('');
    const [municipalityId, setMunicipalityId] = useState('');

    // Sync upward when value changes externally (e.g. autofill from client location)
    useEffect(() => {
        if (!value) {
            setDistrictId('');
            setMunicipalityId('');
            return;
        }
        const parish = parishes.find(p => p.value === value);
        if (!parish) return;
        const municipality = municipalities.find(m => m.value === parish.municipality_id);
        if (!municipality) return;
        setMunicipalityId(municipality.value);
        setDistrictId(municipality.district_id);
    }, [value, parishes, municipalities]);

    const filteredMunicipalities = useMemo(
        () => municipalities.filter(m => !districtId || m.district_id === districtId),
        [municipalities, districtId]
    );

    const filteredParishes = useMemo(
        () => parishes.filter(p => !municipalityId || p.municipality_id === municipalityId),
        [parishes, municipalityId]
    );

    const handleDistrictChange = e => {
        setDistrictId(e.target.value);
        setMunicipalityId('');
        onChange('');
    };

    const handleMunicipalityChange = e => {
        setMunicipalityId(e.target.value);
        onChange('');
    };

    const handleParishChange = e => {
        onChange(e.target.value);
    };

    return (
        <div className="grid grid-cols-3 gap-3">
            {/* District */}
            <div>
                <label className={labelClass}>{t('pages.cascading_parish.district')}</label>
                <select className={selectClass} value={districtId} onChange={handleDistrictChange}>
                    <option value="">—</option>
                    {districts.map(d => (
                        <option key={d.value} value={d.value}>{d.label}</option>
                    ))}
                </select>
            </div>

            {/* Municipality */}
            <div>
                <label className={labelClass}>{t('pages.cascading_parish.municipality')}</label>
                <select
                    className={selectClass}
                    value={municipalityId}
                    onChange={handleMunicipalityChange}
                    disabled={!districtId}
                >
                    <option value="">—</option>
                    {filteredMunicipalities.map(m => (
                        <option key={m.value} value={m.value}>{m.label}</option>
                    ))}
                </select>
            </div>

            {/* Parish */}
            <div>
                <label className={labelClass}>{t('pages.cascading_parish.parish')}</label>
                <select
                    className={selectClass}
                    value={value}
                    onChange={handleParishChange}
                    disabled={!municipalityId}
                >
                    <option value="">—</option>
                    {filteredParishes.map(p => (
                        <option key={p.value} value={p.value}>{p.label}</option>
                    ))}
                </select>
                {error && <p className={errClass}>{error}</p>}
            </div>
        </div>
    );
}
