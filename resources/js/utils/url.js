/**
 * Replace :id / __ID__ placeholders in a URL template.
 */
export function replaceId(url, id) {
    return url.replace(':id', id).replace('__ID__', id);
}

/**
 * Merge params into the current query string.
 * Null / undefined / empty string values remove the key.
 */
export function buildQuery(params) {
    const s = new URLSearchParams(window.location.search);
    Object.entries(params).forEach(([k, v]) => {
        if (v === '' || v === null || v === undefined) s.delete(k);
        else s.set(k, v);
    });
    return s.toString();
}

/**
 * Build a query string from an object, ignoring null/undefined/empty values.
 * Pure function — no window.location dependency.
 */
export function toQueryString(params) {
    const entries = Object.entries(params).filter(([, v]) => v !== '' && v !== null && v !== undefined);
    return new URLSearchParams(entries).toString();
}

/**
 * Normalize a value to a scalar ID — handles Laravel relation objects.
 * If `raw` is an object with an `id` or `value` property, extracts it.
 */
export function toScalar(raw) {
    if (raw === null || raw === undefined) return '';
    return (typeof raw === 'object') ? (raw.id ?? raw.value ?? '') : raw;
}
