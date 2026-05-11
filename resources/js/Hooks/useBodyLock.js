import { useEffect } from 'react';

/**
 * Locks body scroll when `locked` is true.
 * Restores the previous overflow value on cleanup.
 *
 * @param {boolean} locked
 */
export function useBodyLock(locked) {
  useEffect(() => {
    if (!locked) return;
    const prev = document.body.style.overflow;
    document.body.style.overflow = 'hidden';
    return () => { document.body.style.overflow = prev; };
  }, [locked]);
}
