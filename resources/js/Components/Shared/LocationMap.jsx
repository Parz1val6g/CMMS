import { useRef } from 'react';
import { usePage } from '@inertiajs/react';
import { GoogleMap, MarkerF, InfoWindowF, useJsApiLoader } from '@react-google-maps/api';
import { t } from '@/utils/i18n';

const MAP_HEIGHT = 250;
const MAP_ZOOM = 16;
const CONTAINER_STYLE = {
  width: '100%',
  height: MAP_HEIGHT,
  borderRadius: '0.5rem',
  border: '1px solid rgba(107, 114, 128, 0.3)',
};

function buildAddressLines(location) {
  const lines = [];

  if (location.street) {
    lines.push(location.street);
  }
  if (location.landmark && location.landmark !== location.street) {
    lines.push(location.landmark);
  }

  const hierarchy = [];
  const parishValue = location.parish;
  if (parishValue) {
    if (typeof parishValue === 'string') {
      hierarchy.push(parishValue);
    } else {
      if (parishValue.name) hierarchy.push(parishValue.name);
      if (parishValue.municipality?.name) hierarchy.push(parishValue.municipality.name);
      if (parishValue.municipality?.district?.name) hierarchy.push(parishValue.municipality.district.name);
    }
  }

  if (hierarchy.length) {
    lines.push(hierarchy.join(' · '));
  }

  if (location.postal_code) {
    lines.push(location.postal_code);
  }

  return lines;
}

function MapInner({ gmapsKey, latitude, longitude, location }) {
  const mapRef = useRef(null);
  const center = { lat: parseFloat(latitude), lng: parseFloat(longitude) };

  const { isLoaded } = useJsApiLoader({
    id: 'google-maps-script',
    googleMapsApiKey: gmapsKey,
  });

  const onLoad = (m) => {
    mapRef.current = m;
    m.setCenter(center);
    m.setZoom(MAP_ZOOM);
  };

  const onUnmount = () => { mapRef.current = null; };

  if (!isLoaded) {
    return (
      <div
        className="flex items-center justify-center rounded-lg bg-brand-light"
        style={{ width: '100%', height: MAP_HEIGHT }}
      >
        <p className="text-sm text-brand-mid">{t('pages.dashboard.map_loading')}</p>
      </div>
    );
  }

  const addressLines = buildAddressLines(location);

  return (
    <div style={{ width: '100%', height: MAP_HEIGHT, position: 'relative' }}>
      <GoogleMap
        mapContainerStyle={CONTAINER_STYLE}
        center={center}
        zoom={MAP_ZOOM}
        onLoad={onLoad}
        onUnmount={onUnmount}
        options={{
          mapTypeId: 'satellite',
          mapTypeControl: false,
          streetViewControl: false,
          fullscreenControl: false,
          zoomControl: true,
          zoomControlOptions: { position: 9 },
        }}
      >
        <MarkerF position={center} />
        {addressLines.length > 0 && (
          <InfoWindowF position={center} options={{ headerDisabled: true }}>
            <div className="min-w-[160px] text-gray-900">
              {addressLines.map((line, i) => (
                <p key={i} className={i === 0 ? 'text-sm font-semibold mb-0.5' : 'text-xs text-gray-600'}>
                  {line}
                </p>
              ))}
            </div>
          </InfoWindowF>
        )}
      </GoogleMap>
    </div>
  );
}

export default function LocationMap({ location }) {
  const { googleMapsApiKey: gmapsKey } = usePage().props;

  const hasCoords = location?.latitude != null && location?.longitude != null;

  if (!hasCoords) {
    return (
      <div className="text-sm text-gray-400 italic py-2">
        {t('pages.service_orders.value_missing')}
      </div>
    );
  }

  if (!gmapsKey) {
    const lines = buildAddressLines(location);
    return (
      <div>
        {lines.map((line, i) => (
          <p key={i} className={i === 0 ? 'text-sm text-brand-darkest' : 'text-xs text-brand-mid mt-0.5'}>
            {line}
          </p>
        ))}
      </div>
    );
  }

  return <MapInner gmapsKey={gmapsKey} latitude={location.latitude} longitude={location.longitude} location={location} />;
}
