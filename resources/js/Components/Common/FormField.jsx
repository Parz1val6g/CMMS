import { useEffect, useRef, useCallback, useState } from 'react';
import { usePage } from '@inertiajs/react';
import MultiSelect from '@/Components/Common/MultiSelect';
import SearchableSelect from '@/Components/Common/SearchableSelect';
import { toScalar } from '@/Utils/url';

const SEARCH_THRESHOLD = 8;

function SectionHeader({ label }) {
  return (
    <div className="flex items-center gap-2 py-2">
      <hr className="flex-1 border-gray-200 dark:border-gray-700" />
      <span className="shrink-0 text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
        {label}
      </span>
      <hr className="flex-1 border-gray-200 dark:border-gray-700" />
    </div>
  );
}

function MapPicker({ field, value }) {
  const mapRef    = useRef(null);
  const markerRef = useRef(null);
  const latField = field.metadata?.latField ?? 'latitude';
  const lngField = field.metadata?.lngField ?? 'longitude';

  // Refs hold the authoritative coords so initMap always reads the latest
  // values even before React has applied the state update.
  const latRef = useRef('');
  const lngRef = useRef('');

  const [lat, setLat] = useState('');
  const [lng, setLng] = useState('');
  const [loaded, setLoaded] = useState(false);
  const [mapInstance, setMapInstance] = useState(null);
  const { googleMapsApiKey: gmapsKey } = usePage().props;

  /* ── Sync coords from value prop ────────────────────────────
     Writes to refs immediately (synchronous) so initMap can read
     them even in the same effect flush, then queues the state
     update for the re-centre effect / hidden inputs.             */
  useEffect(() => {
    const newLat = value?.[latField];
    const newLng = value?.[lngField];
    if (newLat != null && newLat !== '') {
      latRef.current = String(newLat);
      setLat(String(newLat));
    }
    if (newLng != null && newLng !== '') {
      lngRef.current = String(newLng);
      setLng(String(newLng));
    }
  }, [value?.[latField], value?.[lngField]]); // eslint-disable-line react-hooks/exhaustive-deps

  /* ── Re-centre map and reposition marker when state coords change ── */
  useEffect(() => {
    if (!mapInstance || !markerRef.current || !lat || !lng) return;
    const latNum = parseFloat(lat);
    const lngNum = parseFloat(lng);
    if (isNaN(latNum) || isNaN(lngNum)) return;
    const pos = { lat: latNum, lng: lngNum };
    mapInstance.setCenter(pos);
    markerRef.current.setPosition(pos);
  }, [lat, lng, mapInstance]);

  /* ── Load Google Maps script ────────────────────────────── */
  useEffect(() => {
    if (loaded || !gmapsKey || window.google?.maps) {
      if (window.google?.maps) setLoaded(true);
      return;
    }
    const script = document.createElement('script');
    script.src = `https://maps.googleapis.com/maps/api/js?key=${gmapsKey}&libraries=places`;
    script.async = true;
    script.onload = () => setLoaded(true);
    document.head.appendChild(script);
    return () => { document.head.removeChild(script); };
  }, [gmapsKey, loaded]);

  /* ── Init map ──────────────────────────────────────────────
     Reads from refs (not state) so it always gets the latest
     incoming coords regardless of batching order.
     Never writes back to lat/lng state — the sync effect owns
     that, and writing here caused the default-overwrite race.  */
  const initMap = useCallback(() => {
    if (!mapRef.current || !window.google?.maps) return;
    const latNum = parseFloat(latRef.current) || 40.4923;
    const lngNum = parseFloat(lngRef.current) || -7.5936;
    const startPos = { lat: latNum, lng: lngNum };
    const m = new window.google.maps.Map(mapRef.current, {
      center: startPos,
      zoom: 17,
      mapTypeId: 'hybrid',
      mapTypeControl: false,
      streetViewControl: false,
      fullscreenControl: false,
    });
    const mk = new window.google.maps.Marker({
      map: m,
      position: startPos,
      draggable: true,
    });
    markerRef.current = mk;
    setMapInstance(m);

    m.addListener('click', (e) => {
      const pos = e.latLng;
      mk.setPosition(pos);
      latRef.current = pos.lat().toFixed(6);
      lngRef.current = pos.lng().toFixed(6);
      setLat(latRef.current);
      setLng(lngRef.current);
    });

    mk.addListener('dragend', () => {
      const pos = mk.getPosition();
      latRef.current = pos.lat().toFixed(6);
      lngRef.current = pos.lng().toFixed(6);
      setLat(latRef.current);
      setLng(lngRef.current);
    });
  }, []); // no deps — reads refs, never reads state

  useEffect(() => {
    if (loaded && mapRef.current && !mapInstance) initMap();
  }, [loaded, mapRef, mapInstance, initMap]);

  if (!gmapsKey) {
    return (
      <div className="mb-3 rounded-lg bg-yellow-50 p-3 text-sm text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-300">
        Loading Map Configuration...
      </div>
    );
  }

  return (
    <div className="mb-3">
      <div className="mb-2 flex items-center justify-between">
        <label className="text-sm font-medium text-gray-500 dark:text-gray-400">
          <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="currentColor" className="mr-1 inline text-indigo-500" viewBox="0 0 16 16">
            <path fillRule="evenodd" d="M4 4a4 4 0 1 1 4.5 3.969V13.5a.5.5 0 0 1-1 0V7.97A4 4 0 0 1 4 3.999z" />
          </svg>
          Select on Map
        </label>
        {(lat && lng) && (
          <span className="inline-flex items-center rounded bg-indigo-100 px-2 py-0.5 text-xs font-medium text-indigo-700 dark:bg-indigo-900/50 dark:text-indigo-300">
            {lat}, {lng}
          </span>
        )}
      </div>

      {/* Map canvas */}
      <div
        ref={mapRef}
        className="mb-3 rounded-lg border border-gray-200 dark:border-gray-700"
        style={{ height: 260, background: '#e5e7eb' }}
      >
        {!loaded && (
          <div className="flex h-full items-center justify-center text-sm text-gray-400">
            Loading map...
          </div>
        )}
      </div>

      {/* Hidden inputs — submit latitude/longitude on form POST */}
      <input type="hidden" name={latField} value={lat} readOnly />
      <input type="hidden" name={lngField} value={lng} readOnly />

      <p className="text-xs text-gray-400">
        Click on the map or drag the marker to set coordinates
      </p>
    </div>
  );
}

function StandardField({ field, value = '', error, onChange }) {
  const type = field.type ?? 'text';
  const name = field.name ?? field.key ?? '';
  const required = !!field.required;
  const step = field.step ?? null;
  const pattern = field.pattern ?? null;
  const options = field.options ?? null;
  const isMultiple = !!field.multiple;

  const baseClass = `block w-full rounded-lg border bg-slate-800/60 px-3 py-2 text-sm text-slate-200 placeholder:text-slate-500 focus:ring-1 transition-colors ${error ? 'border-red-500 focus:border-red-500 focus:ring-red-500' : 'border-slate-700 focus:border-indigo-500 focus:ring-indigo-500'
    }`;

  /* Normalize select/multiselect options */
  const opts = options ?? [];
  const ph = field.placeholder ?? 'Select...';

  // Track changes on native DOM element for form serialization
  const handleInputChange = (val) => {
    onChange?.(val);
  };

  if (type === 'select' || type === 'multiselect') {
    const isMulti = isMultiple || type === 'multiselect';
    if (isMulti) {
      return (
        <MultiSelect
          name={name}
          options={opts}
          value={Array.isArray(value) ? value : []}
          placeholder={ph}
          showSearch={opts.length > SEARCH_THRESHOLD}
          onChange={handleInputChange}
        />
      );
    }
    /* Single select: normalize value to scalar for React warnings */
    const scalarVal = toScalar(value);
    if (opts.length > SEARCH_THRESHOLD) {
      return (
        <SearchableSelect
          name={name}
          options={opts}
          value={scalarVal}
          placeholder={ph}
          required={required}
          onChange={handleInputChange}
        />
      );
    }
    return (
      <select name={name} className={baseClass} required={required} value={toScalar(value) ?? ''} onChange={(e) => handleInputChange(e.target.value)}>
        <option value="">{ph}</option>
        {opts.map((opt, i) => (
          <option key={i} value={opt.value}>{opt.label}</option>
        ))}
      </select>
    );
  }

  if (type === 'textarea') {
    return (
      <textarea name={name} className={baseClass} rows={4} required={required} value={value ?? ''} onChange={(e) => handleInputChange(e.target.value)} />
    );
  }

  if (type === 'file') {
    return <FileDropzone name={name} required={required} error={error} onChange={handleInputChange} />;
  }

  if (type === 'checkbox') {
    return (
      <input
        type="checkbox"
        name={name}
        className="h-4 w-4 rounded border-slate-700 bg-slate-800/60 text-indigo-600 focus:ring-indigo-500"
        checked={!!value}
        onChange={(e) => handleInputChange(e.target.checked)}
      />
    );
  }

  return (
    <input
      type={type}
      name={name}
      className={baseClass}
      value={value ?? ''}
      onChange={(e) => handleInputChange(e.target.value)}
      required={required}
      step={step ?? undefined}
      pattern={pattern ?? undefined}
    />
  );
}

/* ── Toggle Button Group ─────────────────────────────────────── */
function ToggleField({ field, value = '', onChange }) {
  const name = field.name ?? field.key;
  const options = field.options ?? [];
  const [active, setActive] = useState(value || options[0]?.value || '');

  // Update active state when value prop changes (for form pre-fill)
  useEffect(() => {
    if (value) setActive(value);
  }, [value]);

  const handleClick = (val) => {
    setActive(val);
    // Notify parent of change
    if (typeof onChange === 'function') {
      onChange(val);
    }
    /* Dispatch custom event so Modal/EditPanel track workflow_type */
    const ev = new CustomEvent('toggle-change', {
      detail: { name, value: val },
    });
    document.dispatchEvent(ev);
  };

  return (
    <div className="flex rounded-lg border border-slate-700 bg-slate-800/60 p-0.5">
      {options.map((opt) => (
        <button
          key={opt.value}
          type="button"
          data-toggle-value={opt.value}
          onClick={() => handleClick(opt.value)}
          className={`flex-1 rounded-md px-4 py-2 text-sm font-medium transition-all ${
            active === opt.value
              ? 'bg-indigo-600 text-white shadow-sm'
              : 'text-slate-400 hover:text-white'
          }`}
        >
          {opt.label}
        </button>
      ))}
      <input type="hidden" name={name} value={active} />
    </div>
  );
}

/* ── File Dropzone ───────────────────────────────────────────── */
function FileDropzone({ name, required, error, onChange }) {
  const [dragOver, setDragOver] = useState(false);
  const [file, setFile] = useState(null);
  const inputRef = useRef(null);

  const handleClick = () => inputRef.current?.click();

  const handleChange = (e) => {
    const f = e.target.files?.[0] ?? null;
    setFile(f);
    if (f && typeof onChange === 'function') {
      onChange(f);
    }
  };

  const handleDrop = (e) => {
    e.preventDefault();
    setDragOver(false);
    const f = e.dataTransfer?.files?.[0] ?? null;
    if (f) {
      setFile(f);
      if (typeof onChange === 'function') {
        onChange(f);
      }
      /* Sync the hidden input so FormData picks it up */
      const dt = new DataTransfer();
      dt.items.add(f);
      if (inputRef.current) inputRef.current.files = dt.files;
    }
  };

  const handleDragOver = (e) => { e.preventDefault(); setDragOver(true); };
  const handleDragLeave = () => setDragOver(false);

  return (
    <div>
      <div
        onClick={handleClick}
        onDrop={handleDrop}
        onDragOver={handleDragOver}
        onDragLeave={handleDragLeave}
        className={`relative flex cursor-pointer flex-col items-center justify-center rounded-lg border-2 border-dashed p-6 transition-colors ${error
            ? 'border-red-500 bg-red-50 dark:border-red-600 dark:bg-red-900/20'
            : dragOver
              ? 'border-indigo-400 bg-indigo-50 dark:border-indigo-500 dark:bg-indigo-900/20'
              : 'border-slate-600 bg-slate-800/40 hover:border-slate-500 dark:border-slate-600'
          }`}
      >
        <svg className={`mb-2 h-8 w-8 ${error ? 'text-red-500 dark:text-red-400' : 'text-slate-500 dark:text-slate-400'}`} xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round">
          <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
          <polyline points="17 8 12 3 7 8" />
          <line x1="12" x2="12" y1="3" y2="15" />
        </svg>
        {file ? (
          <p className={`text-sm font-medium ${error ? 'text-red-600 dark:text-red-400' : 'text-slate-200'}`}>{file.name}</p>
        ) : (
          <>
            <p className={`text-sm font-medium ${error ? 'text-red-600 dark:text-red-400' : 'text-slate-200'}`}>Click to upload or drag and drop</p>
            <p className={`mt-1 text-xs ${error ? 'text-red-500 dark:text-red-400' : 'text-slate-400 dark:text-slate-500'}`}>Any file type supported</p>
          </>
        )}
      </div>
      <input
        ref={inputRef}
        type="file"
        name={name}
        onChange={handleChange}
        className="hidden"
        required={required}
      />
    </div>
  );
}

export default function FormField({ field, value = '', error, onChange }) {
  const type = field.type ?? 'text';
  const label = field.label ?? '';

  if (type === 'section-header') {
    return <SectionHeader label={label} />;
  }

  if (type === 'map-picker' || type === 'map') {
    return <MapPicker field={field} value={value} onChange={onChange} />;
  }

  if (type === 'toggle') {
    return <ToggleField field={field} value={value} onChange={onChange} />;
  }

  return (
    <div className="mb-4">
      {label && (
        <label className="block text-sm font-medium text-slate-300 mb-1.5">
          {label}
          {field.required && <span className="ml-1 text-red-500">*</span>}
        </label>
      )}
      <StandardField field={field} value={value} error={error} onChange={onChange} />
      {error && (
        <p className="text-xs text-red-500 mt-1.5">{error}</p>
      )}
    </div>
  );
}
