import { useEffect, useRef, useCallback, useState } from 'react';
import { usePage } from '@inertiajs/react';
import MultiSelect from '@/Components/Common/MultiSelect';
import SearchableSelect from '@/Components/Common/SearchableSelect';

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
  const mapRef = useRef(null);
  const latField = field.metadata?.latField ?? 'latitude';
  const lngField = field.metadata?.lngField ?? 'longitude';
  const initialLat = value?.[latField] ?? field.metadata?.latitude ?? '';
  const initialLng = value?.[lngField] ?? field.metadata?.longitude ?? '';
  const [lat, setLat] = useState(initialLat);
  const [lng, setLng] = useState(initialLng);
  const [loaded, setLoaded] = useState(false);
  const [mapInstance, setMapInstance] = useState(null);
  const [marker, setMarker] = useState(null);
  const { googleMapsApiKey: gmapsKey } = usePage().props;

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

  /* ── Init map ──────────────────────────────────────────── */
  const initMap = useCallback(() => {
    if (!mapRef.current || !window.google?.maps) return;
    const latNum = parseFloat(lat) || 40.4923;
    const lngNum = parseFloat(lng) || -7.5936;
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
    setMapInstance(m);
    setMarker(mk);
    if (!lat && !lng) {
      setLat(startPos.lat.toFixed(6));
      setLng(startPos.lng.toFixed(6));
    }

    /* Click to place marker */
    m.addListener('click', (e) => {
      const pos = e.latLng;
      mk.setPosition(pos);
      setLat(pos.lat().toFixed(6));
      setLng(pos.lng().toFixed(6));
    });

    /* Drag to update coords */
    mk.addListener('dragend', () => {
      const pos = mk.getPosition();
      setLat(pos.lat().toFixed(6));
      setLng(pos.lng().toFixed(6));
    });
  }, []);

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

/**
 * Normalize a value to a scalar ID — handles Laravel relation objects.
 * If `raw` is an object with an `id` or `value` property, extracts it.
 */
function toScalar(raw) {
  if (raw === null || raw === undefined) return '';
  return (typeof raw === 'object') ? (raw.id ?? raw.value ?? '') : raw;
}

function StandardField({ field, value = '', error }) {
  const type = field.type ?? 'text';
  const name = field.name ?? field.key ?? '';
  const required = !!field.required;
  const step = field.step ?? null;
  const pattern = field.pattern ?? null;
  const options = field.options ?? null;
  const isMultiple = !!field.multiple;

  const baseClass = `block w-full rounded-lg border bg-slate-800/60 px-3 py-2 text-sm text-slate-200 placeholder:text-slate-500 focus:ring-1 transition-colors ${
    error ? 'border-red-500 focus:border-red-500 focus:ring-red-500' : 'border-slate-700 focus:border-indigo-500 focus:ring-indigo-500'
  }`;

  /* Normalize select/multiselect options */
  const opts = options ?? [];
  const ph = field.placeholder ?? 'Select...';

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
        />
      );
    }
    return (
      <select name={name} className={baseClass} required={required} defaultValue={scalarVal}>
        <option value="">{ph}</option>
        {opts.map((opt, i) => (
          <option key={i} value={opt.value}>{opt.label}</option>
        ))}
      </select>
    );
  }

  if (type === 'textarea') {
    return (
      <textarea name={name} className={baseClass} rows={4} required={required} defaultValue={value} />
    );
  }

  if (type === 'file') {
    return <FileDropzone name={name} required={required} error={error} />;
  }

  return (
    <input
      type={type}
      name={name}
      className={baseClass}
      defaultValue={value}
      required={required}
      step={step ?? undefined}
      pattern={pattern ?? undefined}
    />
  );
}

/* ── File Dropzone ───────────────────────────────────────────── */
function FileDropzone({ name, required, error }) {
  const [dragOver, setDragOver] = useState(false);
  const [file, setFile] = useState(null);
  const inputRef = useRef(null);

  const handleClick = () => inputRef.current?.click();

  const handleChange = (e) => {
    const f = e.target.files?.[0] ?? null;
    setFile(f);
  };

  const handleDrop = (e) => {
    e.preventDefault();
    setDragOver(false);
    const f = e.dataTransfer?.files?.[0] ?? null;
    if (f) {
      setFile(f);
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
        className={`relative flex cursor-pointer flex-col items-center justify-center rounded-lg border-2 border-dashed p-6 transition-colors ${
          error
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

export default function FormField({ field, value = '', error }) {
  const type = field.type ?? 'text';
  const label = field.label ?? '';

  if (type === 'section-header') {
    return <SectionHeader label={label} />;
  }

  if (type === 'map-picker' || type === 'map') {
    return <MapPicker field={field} value={value} />;
  }

  return (
    <div className="mb-4">
      {label && (
        <label className="block text-sm font-medium text-slate-300 mb-1.5">
          {label}
          {field.required && <span className="ml-1 text-red-500">*</span>}
        </label>
      )}
      <StandardField field={field} value={value} error={error} />
      {error && (
        <p className="text-xs text-red-500 mt-1.5">{error}</p>
      )}
    </div>
  );
}
