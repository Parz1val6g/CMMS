import { useEffect } from 'react';

/**
 * Attaches a change event listener to a file input identified by CSS selector.
 * Returns null — renders nothing.
 */
export function usePhotoListener(inputSelector, onFileChange) {
  useEffect(() => {
    const el = document.querySelector(inputSelector);
    if (!el) return;
    el.addEventListener('change', onFileChange);
    return () => el.removeEventListener('change', onFileChange);
  }, [inputSelector, onFileChange]);
}

/**
 * Render-null component wrapper for usePhotoListener.
 * Use when a hook-in-component is more convenient than a bare hook call.
 */
export default function PhotoListener({ inputSelector, onFileChange }) {
  usePhotoListener(inputSelector, onFileChange);
  return null;
}
