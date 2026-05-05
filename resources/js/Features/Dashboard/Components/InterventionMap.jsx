import { useMemo, useState, useRef } from 'react';
import { usePage } from '@inertiajs/react';
import { GoogleMap, Marker, InfoWindow, useJsApiLoader } from '@react-google-maps/api';

// Gouveia center — map stays zoomed here regardless of marker spread
const GOUVEIA_CENTER = { lat: 40.4923, lng: -7.5936 };
const MAP_ZOOM = 15;
const MAP_STYLES = {
  borderRadius: '0.5rem',
  border: '1px solid rgba(107, 114, 128, 0.3)',
};

const PIN_COLORS = {
  urgent: '#EF4444',
  high: '#EF4444',
  normal: '#EAB308',
  medium: '#EAB308',
  low: '#22C55E',
};

function getPinColor(priority) {
  return PIN_COLORS[priority] ?? '#6B7280';
}

function getPriorityLabel(priority) {
  const labels = { urgent: 'Urgente', high: 'Alta', normal: 'Normal', medium: 'Média', low: 'Baixa' };
  return labels[priority] ?? priority;
}

function createPinSvg(color) {
  return {
    path: 'M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z',
    fillColor: color,
    fillOpacity: 1,
    strokeColor: '#ffffff',
    strokeWeight: 2,
    scale: 1.5,
    anchor: new window.google.maps.Point(12, 22),
  };
}

/* ── Inner map — only rendered when gmapsKey is truthy ─────── */
function MapInner({ gmapsKey, orders }) {
  const [selected, setSelected] = useState(null);
  const mapRef = useRef(null);

  const { isLoaded } = useJsApiLoader({
    id: 'google-map-script',
    googleMapsApiKey: gmapsKey,
    mapIds: ['INTERVENTION_MAP'],
  });

  const markers = useMemo(() => {
    if (!orders?.length) return [];
    return orders
      .filter((o) => o.latitude && o.longitude)
      .map((o) => ({
        id: o.id,
        lat: o.latitude,
        lng: o.longitude,
        process: o.process,
        priority: o.priority,
        description: o.description,
      }));
  }, [orders]);

  const onLoad = (m) => {
    mapRef.current = m;
    m.setCenter(GOUVEIA_CENTER);
    m.setZoom(MAP_ZOOM);
  };

  const onUnmount = () => { mapRef.current = null; };

  if (!isLoaded) {
    return (
      <div className="flex h-full min-h-[320px] items-center justify-center rounded-lg bg-gray-100 dark:bg-gray-700">
        <p className="text-sm text-gray-500 dark:text-gray-400">A carregar mapa...</p>
      </div>
    );
  }

  return (
    <GoogleMap
      mapContainerStyle={{ width: '100%', height: '100%', ...MAP_STYLES }}
      center={GOUVEIA_CENTER}
      zoom={MAP_ZOOM}
      onLoad={onLoad}
      onUnmount={onUnmount}
      options={{
        mapTypeId: 'hybrid',
        mapTypeControl: false,
        streetViewControl: false,
        fullscreenControl: false,
        zoomControl: true,
        styles: [
          { featureType: 'all', elementType: 'labels', stylers: [{ visibility: 'on' }] },
        ],
      }}
    >
      {markers.map((m) => (
        <Marker
          key={m.id}
          position={{ lat: m.lat, lng: m.lng }}
          icon={createPinSvg(getPinColor(m.priority))}
          onClick={() => setSelected(m)}
        />
      ))}

      {selected && (
        <InfoWindow
          position={{ lat: selected.lat, lng: selected.lng }}
          onCloseClick={() => setSelected(null)}
        >
          <div className="min-w-[200px] text-gray-900">
            <p className="mb-1 text-sm font-semibold">{selected.process}</p>
            <p className="mb-1 text-xs text-gray-600">
              Prioridade: <span className="font-medium">{getPriorityLabel(selected.priority)}</span>
            </p>
            {selected.description && (
              <p className="text-xs text-gray-500">{selected.description}</p>
            )}
          </div>
        </InfoWindow>
      )}
    </GoogleMap>
  );
}

/* ── Public wrapper — guards gmapsKey before mounting loader hooks ─ */
export default function InterventionMap({ orders }) {
  const { googleMapsApiKey: gmapsKey } = usePage().props;

  if (!gmapsKey) {
    return (
      <div className="flex h-full min-h-[320px] items-center justify-center rounded-lg bg-gray-100 dark:bg-gray-700">
        <p className="text-sm text-gray-500 dark:text-gray-400">Loading Map Configuration...</p>
      </div>
    );
  }

  return <MapInner gmapsKey={gmapsKey} orders={orders} />;
}
