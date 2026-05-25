import { useFetch } from '@/composables/useFetch';

export function useClientLocations(clientId) {
    const url = clientId ? `/api/clients/${clientId}/locations` : null;
    const { data: locations, loading, error, refetch } = useFetch(url);

    return { locations: locations ?? [], loading, error, refetch };
}
