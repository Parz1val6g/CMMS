import { t } from '@/utils/i18n';

function isEmpty(value, field) {
    const type = field.type ?? 'text';
    const isMulti = field.multiple || type === 'multiselect';
    const isRange = type === 'date-picker' &&
        (field.dateMode === 'range' || field.metadata?.dateMode === 'range');
    const isSelect = type === 'select';

    if (isMulti) return !Array.isArray(value) || value.length === 0;
    if (isRange) return !value?.start || !value?.end;
    if (isSelect) return value === null || value === undefined || value === '';
    return value === null || value === undefined || value === '';
}

function requiredMessage(field) {
    const type = field.type ?? 'text';
    const isMulti = field.multiple || type === 'multiselect';
    const isRange = type === 'date-picker' &&
        (field.dateMode === 'range' || field.metadata?.dateMode === 'range');
    const isSelect = type === 'select';

    if (isRange) return t('pages.validation.required_date');
    if (isMulti || isSelect) return t('pages.validation.required_select');
    return t('pages.validation.required');
}

/**
 * Validates required fields against current form values.
 * Returns an errors object { fieldName: errorMessage } for any missing required fields.
 */
export function validateRequired(fields, values) {
    const errors = {};
    for (const field of fields) {
        if (!field.required) continue;
        const name = field.name ?? field.key;
        if (!name) continue;
        if (isEmpty(values[name], field)) {
            errors[name] = requiredMessage(field);
        }
    }
    return errors;
}
