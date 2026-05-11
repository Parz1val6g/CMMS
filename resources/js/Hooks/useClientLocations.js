import { useState, useEffect, useCallback } from 'react';

const csrfToken = () => document.querySelector('meta[name="csrf-token"]')?.content ?? '';

export function useClientLocations(clientId) {
    const [locations, setLocations] = useState([]);
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState(null);
    const [refreshKey, setRefreshKey] = useState(0);

    const refetch = useCallback(() => setRefreshKey(k => k + 1), []);

    useEffect(() => {
        if (!clientId) {
            setLocations([]);
            return;
        }

        setLoading(true);
        setError(null);

        fetch(`/api/clients/${clientId}/locations`, {
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken(),
                'X-Requested-With': 'XMLHttpRequest',
            },
        })
            .then(r => { if (!r.ok) throw new Error(`HTTP ${r.status}`); return r.json(); })
            .then(body => setLocations(body.data ?? body))
            .catch(err => setError(err.message))
            .finally(() => setLoading(false));
    }, [clientId, refreshKey]);

    return { locations, loading, error, refetch };
}
