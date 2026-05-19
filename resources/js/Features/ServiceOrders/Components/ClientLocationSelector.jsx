import { useState, useEffect, useRef, useCallback } from 'react';
import { MapPin, X } from 'lucide-react';
import { csrfHeader } from '@/utils/csrf';
import { t } from '@/utils/i18n';

const LOCATION_FIELDS = ['parish_id', 'street', 'reference_point', 'postal_code', 'latitude', 'longitude'];

export default function ClientLocationSelector({ isOpen, clientId, onClientLocationChange, onDirtyChange }) {
  const [locations, setLocations] = useState([]);
  const [selectedId, setSelectedId] = useState('');
  const [loading, setLoading] = useState(false);

  const snapshotRef = useRef(null);
  const isAutoFillingRef = useRef(false);
  const prevOpenRef = useRef(isOpen);

  /* ── Fetch locations when clientId changes ────────────────── */
  useEffect(() => {
    if (!clientId) { setLocations([]); setSelectedId(''); return; }

    setLoading(true);

    fetch(`/api/clients/${clientId}/locations`, {
      headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest', ...csrfHeader() },
    })
      .then(r => r.ok ? r.json() : [])
      .then(data => {
        setLocations(Array.isArray(data) ? data : (data.data ?? []));
      })
      .catch(() => setLocations([]))
      .finally(() => setLoading(false));
  }, [clientId]);

  /* ── Reset on modal close ─────────────────────────────────── */
  useEffect(() => {
    if (prevOpenRef.current && !isOpen) {
      snapshotRef.current = null;
      setSelectedId('');
      onDirtyChange?.(false);
    }
    prevOpenRef.current = isOpen;
  }, [isOpen, onDirtyChange]);

  /* ── Listen for manual field edits ────────────────────────── */
  useEffect(() => {
    if (!isOpen) return;

    const handler = (e) => {
      const { name, value } = e.detail;
      if (!LOCATION_FIELDS.includes(name)) return;       // non-location field → ignore
      if (isAutoFillingRef.current) return;               // still autofilling → ignore
      if (!snapshotRef.current) return;                   // no snapshot → ignore

      // Check if value differs from snapshot
      const snapshot = snapshotRef.current;
      if (snapshot[name] !== undefined && String(snapshot[name]) !== String(value)) {
        setSelectedId('');
        onClientLocationChange?.('');
        onDirtyChange?.(true);
        snapshotRef.current = null;
      }
    };

    document.addEventListener('modal-field-change', handler);
    return () => document.removeEventListener('modal-field-change', handler);
  }, [isOpen, onClientLocationChange, onDirtyChange]);

  /* ── Handle location selection ────────────────────────────── */
  const handleSelect = useCallback((e) => {
    const id = e.target.value;
    setSelectedId(id);

    if (!id) {
      onClientLocationChange?.('');
      return;
    }

    const cl = locations.find(l => String(l.id) === String(id));
    if (!cl?.location) return;

    isAutoFillingRef.current = true;
    onDirtyChange?.(false);

    const loc = cl.location;
    const detail = {
      parish_id:      loc.parish_id ?? '',
      street:         loc.street_address ?? '',
      reference_point: loc.landmark ?? '',
      postal_code:    loc.postal_code ?? '',
      latitude:       loc.latitude ?? '',
      longitude:      loc.longitude ?? '',
    };

    // Store snapshot before dispatching
    snapshotRef.current = { ...detail };

    document.dispatchEvent(new CustomEvent('autofill-location', { detail }));
    onClientLocationChange?.(id);

    // Release autofill guard after microtask
    setTimeout(() => { isAutoFillingRef.current = false; }, 0);
  }, [locations, onClientLocationChange, onDirtyChange]);

  /* ── Clear selection ──────────────────────────────────────── */
  const handleClear = useCallback(() => {
    setSelectedId('');
    onClientLocationChange?.('');
    onDirtyChange?.(true);
    snapshotRef.current = null;
  }, [onClientLocationChange, onDirtyChange]);

  if (!clientId || locations.length === 0) return null;

  return (
    <div className="rounded-lg border border-brand-mid/20 bg-brand-light p-3 space-y-2">
      <div className="flex items-center gap-2 text-sm font-medium text-brand-darkest">
        <MapPin className="h-4 w-4 text-brand-accent" />
        Saved location
      </div>

      <div className="flex items-center gap-2">
        <select
          value={selectedId}
          onChange={handleSelect}
          disabled={loading}
          className="block w-full rounded-lg border border-brand-mid/20 bg-brand-white px-3 py-2 text-sm text-brand-darkest focus:border-brand-accent focus:ring-1 focus:ring-brand-accent disabled:opacity-50"
          data-testid="client-location-select"
        >
          <option value="">
            {loading ? 'Loading…' : 'Select a saved location…'}
          </option>
          {locations.map(cl => (
            <option key={cl.id} value={cl.id}>
              {cl.name}{cl.is_primary ? ' (Primary)' : ''}
            </option>
          ))}
        </select>

        {selectedId && (
          <button
            type="button"
            onClick={handleClear}
            className="flex-shrink-0 rounded-lg p-2 text-brand-mid hover:bg-brand-mid/10 hover:text-red-500 transition-colors"
            aria-label={t('pages.common.clear_location')}
            data-testid="clear-location"
          >
            <X className="h-4 w-4" />
          </button>
        )}
      </div>
    </div>
  );
}
