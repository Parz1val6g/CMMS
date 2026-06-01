import { useState, useEffect, useMemo } from 'react';
import { t } from '@/utils/i18n';
import SearchableSelect from '@/Components/Common/SearchableSelect';

const SEARCH_THRESHOLD = 8;

const labelClass = 'block text-xs font-medium text-brand-mid mb-1';
const errClass   = 'mt-1 text-xs text-red-400';
const selectClass = 'w-full rounded-lg bg-brand-white border border-brand-mid/20 text-brand-darkest px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-accent disabled:opacity-50 disabled:cursor-not-allowed';

function CascadeLevel({ name, label, options, value, onChange, disabled, required }) {
    const ph = t('common.select_placeholder');
    if (options.length > SEARCH_THRESHOLD) {
        return (
            <div>
                <label className={labelClass}>{label}</label>
                <SearchableSelect
                    name={name}
                    options={options}
                    value={disabled ? '' : value}
                    onChange={disabled ? () => {} : onChange}
                    placeholder={ph}
                    disabled={disabled}
                    required={required}
                />
            </div>
        );
    }
    return (
        <div>
            <label className={labelClass}>{label}</label>
            <select
                className={selectClass}
                value={disabled ? '' : value}
                onChange={e => onChange(e.target.value)}
                disabled={disabled}
                required={required}
            >
                <option value="">{ph}</option>
                {options.map(o => (
                    <option key={o.value} value={o.value}>{o.label}</option>
                ))}
            </select>
        </div>
    );
}

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
 *   name           string               — base name (default 'parish_id')
 *   required       bool
 */
export default function CascadingParishSelect({ districts, municipalities, parishes, value, onChange, error, name = 'parish_id', required, lockedDistrictId = null, lockedMunicipalityId = null }) {
    const [districtId, setDistrictId]         = useState(lockedDistrictId ?? '');
    const [municipalityId, setMunicipalityId] = useState(lockedMunicipalityId ?? '');

    // Sync upward when value changes externally (e.g. autofill from client location)
    useEffect(() => {
        if (!value) {
            setDistrictId(lockedDistrictId ?? '');
            setMunicipalityId(lockedMunicipalityId ?? '');
            return;
        }
        const parish = parishes.find(p => p.value === value);
        if (!parish) return;
        const municipality = municipalities.find(m => m.value === parish.municipality_id);
        if (!municipality) return;
        setMunicipalityId(municipality.value);
        setDistrictId(municipality.district_id);
    }, [value, parishes, municipalities, lockedDistrictId, lockedMunicipalityId]);

    const filteredMunicipalities = useMemo(
        () => municipalities.filter(m => !districtId || m.district_id === districtId),
        [municipalities, districtId]
    );

    const filteredParishes = useMemo(
        () => parishes.filter(p => !municipalityId || p.municipality_id === municipalityId),
        [parishes, municipalityId]
    );

    const handleDistrictChange = val => {
        setDistrictId(val);
        setMunicipalityId('');
        onChange('');
    };

    const handleMunicipalityChange = val => {
        setMunicipalityId(val);
        onChange('');
    };

    return (
        <div className="grid grid-cols-3 gap-3">
            <CascadeLevel
                name={`_${name}_district`}
                label={t('pages.cascading_parish.district')}
                options={districts}
                value={districtId}
                onChange={handleDistrictChange}
                disabled={!!lockedDistrictId}
            />
            <CascadeLevel
                name={`_${name}_municipality`}
                label={t('pages.cascading_parish.municipality')}
                options={filteredMunicipalities}
                value={municipalityId}
                onChange={handleMunicipalityChange}
                disabled={!!lockedMunicipalityId || !districtId}
            />
            <div>
                <CascadeLevel
                    name={name}
                    label={t('pages.cascading_parish.parish')}
                    options={filteredParishes}
                    value={value}
                    onChange={onChange}
                    disabled={!municipalityId}
                    required={required}
                />
                {error && <p className={errClass}>{error}</p>}
            </div>
        </div>
    );
}
