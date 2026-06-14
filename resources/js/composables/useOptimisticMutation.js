import { useCallback } from 'react';
import { useToast } from '@/Components/Toast/ToastContext';
import { csrfHeader } from '@/utils/csrf';

/**
 * Fires a mutation optimistically: applies local state immediately, rolls back
 * on failure, and shows an error toast. Never shows a success toast.
 *
 * Usage:
 *   const { mutate } = useOptimisticMutation();
 *   await mutate({
 *     url: '/api/...', method: 'POST', body: { ... },
 *     applyOptimistic: () => {
 *       setState(next);          // apply immediately
 *       return () => setState(prev); // return rollback
 *     },
 *     onSuccess: (data) => { ... },   // optional
 *     onError:   (body) => { ... },   // optional, after rollback
 *     errorMessage: 'Falhou.' | (body) => body.message,
 *   });
 */
export function useOptimisticMutation() {
    const toast = useToast();

    const mutate = useCallback(async ({
        url,
        method = 'POST',
        body = null,
        applyOptimistic,
        onSuccess,
        onError,
        errorMessage,
    }) => {
        const rollback = applyOptimistic?.();

        const headers = {
            Accept: 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            ...csrfHeader(),
        };
        if (body !== null && method !== 'DELETE') {
            headers['Content-Type'] = 'application/json';
        }

        try {
            const res = await fetch(url, {
                method,
                credentials: 'include',
                headers,
                body: body !== null ? JSON.stringify(body) : undefined,
            });

            if (!res.ok) {
                const responseBody = await res.json().catch(() => ({}));
                rollback?.();
                const msg = typeof errorMessage === 'function'
                    ? errorMessage(responseBody)
                    : (errorMessage ?? responseBody.message ?? `HTTP ${res.status}`);
                toast.error(msg);
                onError?.(responseBody);
                return;
            }

            const responseBody = await res.json().catch(() => ({}));
            onSuccess?.(responseBody);
        } catch (err) {
            rollback?.();
            const msg = typeof errorMessage === 'function'
                ? errorMessage(err)
                : (errorMessage ?? 'Erro de rede. Tente novamente.');
            toast.error(msg);
            onError?.(err);
        }
    }, [toast]);

    return { mutate };
}
