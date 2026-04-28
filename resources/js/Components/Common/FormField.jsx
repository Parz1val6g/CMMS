import { useEffect, useRef, useCallback, useState } from 'react';

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

function MapPicker({ field }) {
  const mapRef = useRef(null);
  const [lat, setLat] = useState('');
  const [lng, setLng] = useState('');
  const [loaded, setLoaded] = useState(false);
  const [mapInstance, setMapInstance] = useState(null);
  const [marker, setMarker] = useState(null);
  const apiKey = field.apiKey ?? '';

  /* ── Load Google Maps script ────────────────────────────── */
  useEffect(() => {
    if (loaded || !apiKey || window.google?.maps) {
      if (window.google?.maps) setLoaded(true);
      return;
    }
    const script = document.createElement('script');
    script.src = `https://maps.googleapis.com/maps/api/js?key=${apiKey}&libraries=places`;
    script.async = true;
    script.onload = () => setLoaded(true);
    document.head.appendChild(script);
    return () => { document.head.removeChild(script); };
  }, [apiKey, loaded]);

  /* ── Init map ──────────────────────────────────────────── */
  const initMap = useCallback(() => {
    if (!mapRef.current || !window.google?.maps) return;
    const defaultPos = { lat: 40.4923, lng: -7.5936 }; // Gouveia
    const m = new window.google.maps.Map(mapRef.current, {
      center: defaultPos,
      zoom: 17,
      mapTypeId: 'hybrid',
      mapTypeControl: false,
      streetViewControl: false,
      fullscreenControl: false,
    });
    const mk = new window.google.maps.Marker({
      map: m,
      position: defaultPos,
      draggable: true,
    });
    setMapInstance(m);
    setMarker(mk);
    setLat(defaultPos.lat.toFixed(6));
    setLng(defaultPos.lng.toFixed(6));

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

  /* ── Sync lat/lng on change ────────────────────────────── */
  const onLatChange = useCallback((e) => {
    const v = e.target.value;
    setLat(v);
    if (v && lng && mapInstance && marker) {
      const pos = { lat: parseFloat(v), lng: parseFloat(lng) };
      marker.setPosition(pos);
      mapInstance.setCenter(pos);
    }
  }, [lng, mapInstance, marker]);

  const onLngChange = useCallback((e) => {
    const v = e.target.value;
    setLng(v);
    if (lat && v && mapInstance && marker) {
      const pos = { lat: parseFloat(lat), lng: parseFloat(v) };
      marker.setPosition(pos);
      mapInstance.setCenter(pos);
    }
  }, [lat, mapInstance, marker]);

  if (!apiKey) {
    return (
      <div className="mb-3 rounded-lg bg-yellow-50 p-3 text-sm text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-300">
        Map disabled: no API key configured.
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

      {/* Hidden inputs for form submission */}
      <input type="hidden" name="latitude" value={lat} readOnly />
      <input type="hidden" name="longitude" value={lng} readOnly />

      <div className="mb-2 grid grid-cols-2 gap-2">
        <div>
          <label className="mb-1 block text-sm font-medium text-gray-500 dark:text-gray-400">Latitude</label>
          <input
            type="text"
            step="any"
            value={lat}
            onChange={onLatChange}
            className="block w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm shadow-none focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200"
            placeholder="38.716654"
          />
        </div>
        <div>
          <label className="mb-1 block text-sm font-medium text-gray-500 dark:text-gray-400">Longitude</label>
          <input
            type="text"
            step="any"
            value={lng}
            onChange={onLngChange}
            className="block w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm shadow-none focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200"
            placeholder="-9.139594"
          />
        </div>
      </div>

      <p className="text-xs text-gray-400">
        Click on the map or drag the marker to set coordinates
      </p>
    </div>
  );
}

function StandardField({ field, value }) {
  const type = field.type ?? 'text';
  const name = field.name ?? field.key ?? '';
  const label = field.label ?? '';
  const required = !!field.required;
  const step = field.step ?? null;
  const pattern = field.pattern ?? null;
  const options = field.options ?? null;

  const baseClass = 'block w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm shadow-none focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200';

  if (type === 'select') {
    const ph = field.placeholder ?? 'Select...';
    return (
      <select name={name} className={baseClass} required={required} defaultValue={value ?? ''}>
        <option value="">{ph}</option>
        {options?.map((opt, i) => (
          <option key={i} value={opt.value}>{opt.label}</option>
        ))}
      </select>
    );
  }

  if (type === 'textarea') {
    return (
      <textarea name={name} className={baseClass} rows={4} required={required} defaultValue={value ?? ''} />
    );
  }

  if (type === 'file') {
    return (
      <input type="file" name={name} className={`${baseClass} file:mr-2 file:rounded file:border-0 file:bg-indigo-50 file:px-3 file:py-1 file:text-xs file:font-medium file:text-indigo-700 hover:file:bg-indigo-100 dark:file:bg-indigo-900/50 dark:file:text-indigo-300`} required={required} />
    );
  }

  return (
    <input
      type={type}
      name={name}
      className={baseClass}
      defaultValue={value ?? ''}
      required={required}
      step={step ?? undefined}
      pattern={pattern ?? undefined}
    />
  );
}

export default function FormField({ field, value }) {
  const type = field.type ?? 'text';
  const label = field.label ?? '';

  if (type === 'map_input') return null;

  if (type === 'section-header') {
    return <SectionHeader label={label} />;
  }

  if (type === 'map-picker') {
    return <MapPicker field={field} />;
  }

  return (
    <div className="mb-3">
      {label && (
        <label className="mb-1 block text-sm font-medium text-gray-500 dark:text-gray-400">
          {label}
          {field.required && <span className="ml-1 text-red-500">*</span>}
        </label>
      )}
      <StandardField field={field} value={value} />
    </div>
  );
}
