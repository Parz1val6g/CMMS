import { buildCreatePayload } from './payload';

const LOCATION_FIELDS = ['parish_id', 'street', 'reference_point', 'postal_code', 'latitude', 'longitude'];

describe('buildCreatePayload', () => {
  it('Scenario A: appends client_location_id and omits 6 location fields when location selected and not dirty', () => {
    const fd = new FormData();
    fd.append('parish_id', 'some-parish-id');
    fd.append('street', 'Rua X');
    fd.append('description', 'test order');

    buildCreatePayload(fd, 'loc-123', false, LOCATION_FIELDS);

    expect(fd.get('client_location_id')).toBe('loc-123');
    expect(fd.get('parish_id')).toBeNull();
    expect(fd.get('street')).toBeNull();
    expect(fd.get('reference_point')).toBeNull();
    expect(fd.get('postal_code')).toBeNull();
    expect(fd.get('latitude')).toBeNull();
    expect(fd.get('longitude')).toBeNull();
    // Non-location fields preserved
    expect(fd.get('description')).toBe('test order');
  });

  it('Scenario B: keeps all fields and does NOT add client_location_id when location selected but dirty', () => {
    const fd = new FormData();
    fd.append('parish_id', 'some-parish-id');
    fd.append('street', 'Rua X');
    fd.append('description', 'test order');

    buildCreatePayload(fd, 'loc-123', true, LOCATION_FIELDS);

    expect(fd.get('client_location_id')).toBeNull();
    expect(fd.get('parish_id')).toBe('some-parish-id');
    expect(fd.get('street')).toBe('Rua X');
    expect(fd.get('description')).toBe('test order');
  });

  it('Scenario C: keeps all fields and does NOT add client_location_id when no location selected', () => {
    const fd = new FormData();
    fd.append('parish_id', 'some-parish-id');
    fd.append('street', 'Rua X');
    fd.append('description', 'test order');

    buildCreatePayload(fd, null, false, LOCATION_FIELDS);

    expect(fd.get('client_location_id')).toBeNull();
    expect(fd.get('parish_id')).toBe('some-parish-id');
    expect(fd.get('street')).toBe('Rua X');
    expect(fd.get('description')).toBe('test order');
  });
});
