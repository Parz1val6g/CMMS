import { useEffect, useRef, useCallback, useState } from 'react';
import { usePage } from '@inertiajs/react';
import { t } from '@/utils/i18n';

export default function MapPicker({ latFieldName = 'latitude', lngFieldName = 'longitude', initialLat, initialLng, label = null, compact = false }) {
  const mapRef    = useRef(null);
  const markerRef = useRef(null);

  const latRef = useRef('');
  const lngRef = useRef('');

  const [lat, setLat] = useState('');
  const [lng, setLng] = useState('');
  const [loaded, setLoaded] = useState(false);
  const [mapInstance, setMapInstance] = useState(null);
  const { googleMapsApiKey: gmapsKey, defaultLocation } = usePage().props;
  const fallbackLat = defaultLocation?.lat ?? 40.4923;
  const fallbackLng = defaultLocation?.lng ?? -7.5936;

  useEffect(() => {
    if (initialLat != null && initialLat !== '') {
      latRef.current = String(initialLat);
      setLat(String(initialLat));
    }
    if (initialLng != null && initialLng !== '') {
      lngRef.current = String(initialLng);
      setLng(String(initialLng));
    }
  }, [initialLat, initialLng]);

  useEffect(() => {
    if (!mapInstance || !markerRef.current || !lat || !lng) return;
    const latNum = parseFloat(lat);
    const lngNum = parseFloat(lng);
    if (isNaN(latNum) || isNaN(lngNum)) return;
    const pos = { lat: latNum, lng: lngNum };
    mapInstance.setCenter(pos);
    markerRef.current.setPosition(pos);
  }, [lat, lng, mapInstance]);

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

  const initMap = useCallback(() => {
    if (!mapRef.current || !window.google?.maps) return;

    const hasSavedCoords = latRef.current !== '' && lngRef.current !== '';

    function renderMap(center) {
      const m = new window.google.maps.Map(mapRef.current, {
        center,
        zoom: 17,
        mapTypeId: 'hybrid',
        mapTypeControl: false,
        streetViewControl: false,
        fullscreenControl: false,
      });
      const mk = new window.google.maps.Marker({
        map: m,
        position: hasSavedCoords ? center : null,
        draggable: !hasSavedCoords,
        visible: hasSavedCoords,
      });
      markerRef.current = mk;
      setMapInstance(m);

      m.addListener('click', (e) => {
        const pos = e.latLng;
        mk.setPosition(pos);
        mk.setVisible(true);
        mk.setDraggable(true);
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
    }

    if (hasSavedCoords) {
      const latNum = parseFloat(latRef.current) || fallbackLat;
      const lngNum = parseFloat(lngRef.current) || fallbackLng;
      renderMap({ lat: latNum, lng: lngNum });
      return;
    }

    fetch('https://ipapi.co/json/')
      .then(r => r.json())
      .then(data => {
        if (data.latitude && data.longitude) {
          renderMap({ lat: data.latitude, lng: data.longitude });
        } else {
          renderMap({ lat: fallbackLat, lng: fallbackLng });
        }
      })
      .catch(() => {
        renderMap({ lat: fallbackLat, lng: fallbackLng });
      });
  }, []);

  useEffect(() => {
    if (loaded && mapRef.current && !mapInstance) initMap();
  }, [loaded, mapRef, mapInstance, initMap]);

  if (!gmapsKey) {
    return (
      <div className="mb-3 rounded-lg bg-yellow-50 p-3 text-sm text-yellow-700">
        {t('pages.common.map_picker.missing_key')}
      </div>
    );
  }

  return (
    <div className="mb-3">
      <div className="mb-2 flex items-center justify-between">
        <label className="text-sm font-medium text-brand-mid">
          <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="currentColor" className="mr-1 inline text-brand-accent" viewBox="0 0 16 16">
            <path fillRule="evenodd" d="M4 4a4 4 0 1 1 4.5 3.969V13.5a.5.5 0 0 1-1 0V7.97A4 4 0 0 1 4 3.999z" />
          </svg>
          {label ?? t('pages.common.map_picker.select_label')}
        </label>
        {(lat && lng) && (
          <span className="inline-flex items-center rounded bg-brand-accent/10 px-2 py-0.5 text-xs font-medium text-brand-accent">
            {lat}, {lng}
          </span>
        )}
      </div>

      <div
        ref={mapRef}
        className="mb-3 rounded-lg border border-brand-mid/20"
        style={{ height: compact ? 220 : 260, background: '#e5e7eb' }}
      >
        {!loaded && (
          <div className="flex h-full items-center justify-center text-sm text-brand-mid">
            {t('pages.common.map_picker.loading')}
          </div>
        )}
      </div>

      <input type="hidden" name={latFieldName} value={lat} readOnly />
      <input type="hidden" name={lngFieldName} value={lng} readOnly />

      <p className="text-xs text-brand-mid">
        {t('pages.common.map_picker.hint')}
      </p>
    </div>
  );
}
