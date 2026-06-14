import { useState, useCallback } from 'react';
import { csrfHeader } from '@/utils/csrf';

export function useApiRequest() {
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState(null);

    const submit = useCallback(async (url, { method = 'POST', body = null, onSuccess, onError } = {}) => {
        setLoading(true);
        setError(null);

        const headers = {
            Accept: 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            ...csrfHeader(),
        };

        if (body && method !== 'DELETE') {
            headers['Content-Type'] = 'application/json';
        }

        try {
            const res = await fetch(url, {
                method,
                credentials: 'include',
                headers,
                body: body ? JSON.stringify(body) : undefined,
            });

            const responseBody = await res.json().catch(() => ({}));

            if (res.ok) {
                onSuccess?.(responseBody);
            } else {
                const errMsg = responseBody.message || `HTTP ${res.status}`;
                setError(errMsg);
                onError?.(errMsg, responseBody.errors);
            }
        } catch (err) {
            const errMsg = err.message || 'Network error';
            setError(errMsg);
            onError?.(errMsg);
        } finally {
            setLoading(false);
        }
    }, []);

    return { submit, loading, error };
}
