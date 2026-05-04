import { useState, useEffect, useRef } from 'react';

/**
 * Hook for managing when to show validation errors
 *
 * Shows errors after:
 * - User leaves field (blur), OR
 * - User stops typing for specified delay (default 2 seconds)
 *
 * Usage:
 *   const { shouldShowError, onBlur, onChange } = useDebounceValidation({
 *     delay: 2000, // 2 seconds
 *   });
 */
export function useDebounceValidation({ delay = 2000 }) {
  const [hasBlurred, setHasBlurred] = useState(false);
  const [shouldShowError, setShouldShowError] = useState(false);
  const debounceTimer = useRef(null);

  // Handle blur events - show errors immediately
  const onBlur = () => {
    setHasBlurred(true);
    setShouldShowError(true);
  };

  // Handle change events - show errors after delay
  const onChange = () => {
    setShouldShowError(false);

    // Cancel previous debounce timer
    if (debounceTimer.current) {
      clearTimeout(debounceTimer.current);
    }

    // Show error after delay
    if (hasBlurred) {
      debounceTimer.current = setTimeout(() => {
        setShouldShowError(true);
      }, delay);
    }
  };

  // Cleanup on unmount
  useEffect(() => {
    return () => {
      if (debounceTimer.current) {
        clearTimeout(debounceTimer.current);
      }
    };
  }, []);

  return {
    shouldShowError,
    onBlur,
    onChange,
  };
}
