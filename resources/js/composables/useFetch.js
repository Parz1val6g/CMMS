import { useState, useEffect, useCallback } from 'react';
import { csrfHeader } from '@/utils/csrf';

export function useFetch(url, options = {}) {
    const { enabled = true, dependencies = [] } = options;
    const [data, setData] = useState(null);
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState(null);

    const fetchData = useCallback(async () => {
        if (!url) return;
        setLoading(true);
        setError(null);

        try {
            const res = await fetch(url, {
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    ...csrfHeader(),
                },
            });
            if (!res.ok) throw new Error(`HTTP ${res.status}`);
            const body = await res.json();
            setData(body.data ?? body);
        } catch (err) {
            setError(err.message);
        } finally {
            setLoading(false);
        }
    }, [url, ...dependencies]);

    useEffect(() => {
        if (enabled) {
            fetchData();
        }
    }, [fetchData, enabled]);

    return { data, loading, error, refetch: fetchData };
}
