import { render, screen, fireEvent, waitFor, act } from '@testing-library/react';
// waitFor is still used for assertion-based waits (e.g., fetch called)
import ClientLocationSelector from './ClientLocationSelector';

/* ── Fixtures ────────────────────────────────────────────────── */
const MOCK_LOCATIONS = [
  {
    id: 1,
    name: 'Office',
    is_primary: true,
    location: {
      id: 10,
      parish_id: 'parish-uuid-1',
      postal_code: '1000-001',
      street_address: 'Rua Principal, 123',
      landmark: 'Near the square',
      latitude: '38.7167',
      longitude: '-9.1333',
    },
  },
  {
    id: 2,
    name: 'Warehouse',
    is_primary: false,
    location: {
      id: 20,
      parish_id: 'parish-uuid-2',
      postal_code: '2000-002',
      street_address: 'Av. Industrial, 456',
      landmark: 'Next to the factory',
      latitude: '38.7500',
      longitude: '-9.1500',
    },
  },
];

/* ── Helpers ─────────────────────────────────────────────────── */
function mockFetchLocations(data = MOCK_LOCATIONS) {
  global.fetch = vi.fn().mockResolvedValue({
    ok: true,
    json: () => Promise.resolve(data),
  });
}

function renderSelector(props = {}) {
  const onClientLocationChange = vi.fn();
  const onDirtyChange = vi.fn();

  const result = render(
    <ClientLocationSelector
      isOpen={true}
      clientId={props.clientId ?? 'client-1'}
      onClientLocationChange={onClientLocationChange}
      onDirtyChange={onDirtyChange}
      {...props}
    />
  );

  return { ...result, onClientLocationChange, onDirtyChange };
}


/* ── Tests ───────────────────────────────────────────────────── */

describe('ClientLocationSelector', () => {
  beforeEach(() => {
    vi.restoreAllMocks();
    document.head.innerHTML = '<meta name="csrf-token" content="test-token">';
  });

  /* ── Test #1: Selector visibility ────────────────────────── */
  describe('Test #1 — visibility', () => {
    it('renders select when clientId is provided and locations exist', async () => {
      mockFetchLocations();
      renderSelector();

      // Wait for fetch to resolve and select to appear
      const select = await screen.findByTestId('client-location-select', {}, { timeout: 2000 });

      // Verify options rendered
      expect(screen.queryByText('Office (Primary)')).not.toBeNull();
      expect(screen.queryByText('Warehouse')).not.toBeNull();
    });

    it('does NOT render when no clientId is set', async () => {
      mockFetchLocations();
      const { container } = renderSelector({ clientId: null });
      expect(container.innerHTML).toBe('');
    });

    it('does NOT render when client has no locations', async () => {
      mockFetchLocations([]);
      renderSelector();

      // Wait for fetch to resolve
      await waitFor(() => {
        expect(global.fetch).toHaveBeenCalled();
      }, { timeout: 2000 });

      // Component returns null when no locations
      expect(screen.queryByTestId('client-location-select')).toBeNull();
    });
  });

  /* ── Test #2: Autofill dispatch ──────────────────────────── */
  describe('Test #2 — autofill dispatch', () => {
    it('dispatches autofill-location event with correct payload on selection', async () => {
      mockFetchLocations();
      const dispatchSpy = vi.spyOn(document, 'dispatchEvent');
      renderSelector();

      const select = await screen.findByTestId('client-location-select', {}, { timeout: 2000 });

      // Select Office
      fireEvent.change(select, { target: { value: '1' } });

      // Assert autofill-location dispatched
      const autofillCall = dispatchSpy.mock.calls.find(
        ([event]) => event.type === 'autofill-location'
      );
      expect(autofillCall).toBeDefined();

      const detail = autofillCall[0].detail;
      expect(detail.parish_id).toBe('parish-uuid-1');
      expect(detail.street).toBe('Rua Principal, 123');
      expect(detail.reference_point).toBe('Near the square');
      expect(detail.postal_code).toBe('1000-001');
      expect(detail.latitude).toBe('38.7167');
      expect(detail.longitude).toBe('-9.1333');
    });

    it('calls onClientLocationChange with selected id', async () => {
      mockFetchLocations();
      const { onClientLocationChange } = renderSelector();

      const select = await screen.findByTestId('client-location-select', {}, { timeout: 2000 });

      fireEvent.change(select, { target: { value: '2' } });
      expect(onClientLocationChange).toHaveBeenCalledWith('2');
    });
  });

  /* ── Test #3: Clear on manual edit ────────────────────────── */
  describe('Test #3 — clear on manual edit', () => {
    it('clears selection and signals dirty when a location field is edited after autofill', async () => {
      mockFetchLocations();
      const { onClientLocationChange, onDirtyChange } = renderSelector();

      const select = await screen.findByTestId('client-location-select', {}, { timeout: 2000 });

      // Select a location
      fireEvent.change(select, { target: { value: '1' } });
      expect(onClientLocationChange).toHaveBeenCalledWith('1');

      // Flush setTimeout(0) that releases isAutoFillingRef
      await new Promise(resolve => setTimeout(resolve, 0));

      // Simulate manual edit of a location field
      await act(async () => {
        document.dispatchEvent(
          new CustomEvent('modal-field-change', {
            detail: { name: 'street', value: 'Edited Street, 999' },
          })
        );
      });

      // Wait for React to commit the state update (setSelectedId(''))
      await waitFor(() => {
        expect(select.value).toBe('');
      });
      expect(onClientLocationChange).toHaveBeenCalledWith('');
      expect(onDirtyChange).toHaveBeenCalledWith(true);
    });

    it('does NOT clear when a non-location field is edited', async () => {
      mockFetchLocations();
      const { onClientLocationChange } = renderSelector();

      const select = await screen.findByTestId('client-location-select', {}, { timeout: 2000 });

      fireEvent.change(select, { target: { value: '1' } });
      onClientLocationChange.mockClear();

      // Edit a non-location field (description)
      document.dispatchEvent(
        new CustomEvent('modal-field-change', {
          detail: { name: 'description', value: 'New description' },
        })
      );

      // Selector should NOT have cleared
      expect(select.value).toBe('1');
      expect(onClientLocationChange).not.toHaveBeenCalledWith('');
    });

    it('resets cleanly when modal is closed and reopened', async () => {
      mockFetchLocations();
      const { onClientLocationChange, onDirtyChange, rerender } = renderSelector();

      const select = await screen.findByTestId('client-location-select', {}, { timeout: 2000 });

      // Select a location
      fireEvent.change(select, { target: { value: '1' } });

      // Close modal
      rerender(
        <ClientLocationSelector
          isOpen={false}
          clientId="client-1"
          onClientLocationChange={onClientLocationChange}
          onDirtyChange={onDirtyChange}
        />
      );

      // Reopen — wait for re-fetch to resolve
      const reopenedSelect = await screen.findByTestId('client-location-select', {}, { timeout: 2000 });
      expect(reopenedSelect.value).toBe('');
    });
  });
});
