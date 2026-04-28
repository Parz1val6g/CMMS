@props([
    'field' => [],
    'value' => null,
])

@php
    $type     = $field['type'] ?? 'text';
    $name     = $field['name'] ?? '';
    $label    = $field['label'] ?? '';
    $required = !empty($field['required']);
    $step     = $field['step'] ?? null;
    $pattern  = $field['pattern'] ?? null;
    $options  = $field['options'] ?? null;
@endphp

{{-- Fields marked map_input:true are rendered inside the map picker widget --}}
@if(!empty($field['map_input']))
    {{-- Intentionally empty — rendered inside the map-picker block below --}}
@elseif($type === 'section-header')

{{-- ── SECTION HEADER (visual divider between form groups) ─────── --}}
    <div class="d-flex align-items-center gap-2 mt-3 mb-1">
        <hr class="flex-grow-1 m-0 border-secondary-subtle">
        <span class="small fw-semibold text-body-secondary text-uppercase"
              style="font-size:0.68rem;letter-spacing:0.08em;">{{ $label }}</span>
        <hr class="flex-grow-1 m-0 border-secondary-subtle">
    </div>

@elseif($type === 'map-picker')

{{-- ── MAP PICKER (Google Maps) ─────────────────────────────────── --}}
    <div class="mb-3 js-map-picker-container">
        <div class="d-flex align-items-center justify-content-between mb-2">
            <label class="form-label small fw-medium text-body-secondary mb-0">
                <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="currentColor"
                     class="me-1 text-primary" viewBox="0 0 16 16" aria-hidden="true">
                    <path fill-rule="evenodd"
                          d="M4 4a4 4 0 1 1 4.5 3.969V13.5a.5.5 0 0 1-1 0V7.97A4 4 0 0 1 4 3.999z"/>
                </svg>
                {{ __('messages.select_on_map') }}
            </label>
            <span class="js-map-coords-display badge bg-primary-subtle text-primary"
                  style="font-size:.7rem;display:none;"></span>
        </div>

        {{-- Map canvas --}}
        <div class="js-gmap mb-3"
             style="height:260px;border-radius:.5rem;border:1px solid var(--bs-border-color);"></div>

        {{-- Lat / Lng side by side --}}
        <div class="row g-2 mb-2">
            <div class="col-6">
                <label class="form-label small fw-medium text-body-secondary mb-1">
                    {{ __('messages.latitude') }}
                </label>
                <input type="number"
                       name="latitude"
                       step="any"
                       class="form-control form-control-sm shadow-none bg-body-tertiary border js-lat-input"
                       placeholder="ex: 38.716654">
            </div>
            <div class="col-6">
                <label class="form-label small fw-medium text-body-secondary mb-1">
                    {{ __('messages.longitude') }}
                </label>
                <input type="number"
                       name="longitude"
                       step="any"
                       class="form-control form-control-sm shadow-none bg-body-tertiary border js-lng-input"
                       placeholder="ex: -9.139594">
            </div>
        </div>

        <p class="text-body-secondary mb-0" style="font-size:.72rem;">
            <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" fill="currentColor"
                 class="me-1" viewBox="0 0 16 16">
                <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
                <path d="m8.93 6.588-2.29.287-.082.38.45.083c.294.07.352.176.288.469l-.738
                         3.468c-.194.897.105 1.319.808 1.319.545 0 1.178-.252
                         1.465-.598l.088-.416c-.2.176-.492.246-.686.246-.275
                         0-.375-.193-.304-.533L8.93 6.588zM9 4.5a1 1 0 1 1-2 0 1 1 0 0 1 2 0z"/>
            </svg>
            {{ __('messages.click_map_to_set_coordinates') }}
        </p>
    </div>

    @pushOnce('scripts')
    <script>
    (function () {
        'use strict';

        var _maps = new Map(); // mapDiv → { map, marker }

        /* ── find the VISIBLE map container ─────────────── */
        function getVisibleMapDiv() {
            var divs = document.querySelectorAll('.js-gmap');
            for (var i = 0; i < divs.length; i++) {
                if (divs[i].offsetParent !== null) return divs[i];
            }
            return null;
        }

        function getInputsForMapDiv(mapDiv) {
            var container = mapDiv.closest('.js-map-picker-container');
            if (!container) return null;
            return {
                lat:     container.querySelector('.js-lat-input'),
                lng:     container.querySelector('.js-lng-input'),
                display: container.querySelector('.js-map-coords-display'),
            };
        }

        /* ── helpers ─────────────────────────────────────── */
        function setFields(inputs, lat, lng) {
            var la = parseFloat(lat.toFixed(6));
            var lo = parseFloat(lng.toFixed(6));
            if (inputs.lat) inputs.lat.value = la;
            if (inputs.lng) inputs.lng.value = lo;
            if (inputs.display) {
                inputs.display.textContent   = la + ', ' + lo;
                inputs.display.style.display = 'inline-block';
            }
        }

        function movePin(state, inputs, latLng) {
            if (!state.map) return;
            if (state.marker) {
                state.marker.setPosition(latLng);
            } else {
                state.marker = new google.maps.Marker({
                    position:  latLng,
                    map:       state.map,
                    draggable: true,
                    title:     '{{ __("messages.select_on_map") }}'
                });
                state.marker.addListener('dragend', function (e) {
                    setFields(inputs, e.latLng.lat(), e.latLng.lng());
                });
            }
            setFields(inputs, latLng.lat(), latLng.lng());
        }

        /* ── build map ───────────────────────────────────── */
        function buildMap() {
            if (!window.google || !window.google.maps) {
                document.addEventListener('gmap-ready', buildMap, { once: true });
                return;
            }

            var mapDiv = getVisibleMapDiv();
            if (!mapDiv) return;

            var inputs = getInputsForMapDiv(mapDiv);
            if (!inputs || !inputs.lat || !inputs.lng) return;

            // Destroy previous instance on this div
            if (_maps.has(mapDiv)) {
                var old = _maps.get(mapDiv);
                if (old.marker) old.marker.setMap(null);
                _maps.delete(mapDiv);
            }

            var hasCoords = inputs.lat.value !== '' && inputs.lng.value !== '';

            if (hasCoords) {
                // Record already has saved coordinates — use them directly
                initMap(mapDiv, inputs, parseFloat(inputs.lat.value), parseFloat(inputs.lng.value), 14);
            } else if (navigator.geolocation) {
                // No saved coords — ask browser for current location
                navigator.geolocation.getCurrentPosition(
                    function (pos) {
                        initMap(mapDiv, inputs, pos.coords.latitude, pos.coords.longitude, 14);
                    },
                    function () {
                        // Permission denied or unavailable → fallback to Portugal
                        initMap(mapDiv, inputs, 39.5, -8.0, 6);
                    },
                    { timeout: 6000 }
                );
            } else {
                // Geolocation not supported → fallback to Portugal
                initMap(mapDiv, inputs, 39.5, -8.0, 6);
            }
        }

        function initMap(mapDiv, inputs, lat0, lng0, zoom0) {
            var hasCoords = inputs.lat.value !== '' && inputs.lng.value !== '';

            var map = new google.maps.Map(mapDiv, {
                center:            new google.maps.LatLng(lat0, lng0),
                zoom:              zoom0,
                mapTypeControl:    false,
                streetViewControl: false,
                fullscreenControl: false,
            });

            var state = { map: map, marker: null };
            _maps.set(mapDiv, state);

            if (hasCoords) movePin(state, inputs, new google.maps.LatLng(lat0, lng0));

            map.addListener('click', function (e) {
                movePin(state, inputs, e.latLng);
            });

            function syncFromInputs() {
                var la = parseFloat(inputs.lat.value);
                var lo = parseFloat(inputs.lng.value);
                if (!isNaN(la) && !isNaN(lo)) {
                    var ll = new google.maps.LatLng(la, lo);
                    movePin(state, inputs, ll);
                    map.setCenter(ll);
                    map.setZoom(Math.max(map.getZoom(), 14));
                }
            }

            inputs.lat.addEventListener('change', syncFromInputs);
            inputs.lng.addEventListener('change', syncFromInputs);
        }

        /* ── lifecycle ────────────────────────────────────── */
        document.addEventListener('sm-panel-opened',  buildMap);
        document.addEventListener('shown.bs.modal',   buildMap);

        document.addEventListener('sm-panel-closed', function () {
            _maps.forEach(function (state, div) {
                if (div.offsetParent === null) {
                    if (state.marker) state.marker.setMap(null);
                    _maps.delete(div);
                }
            });
        });

    }());
    </script>
    @endPushOnce

@else

{{-- ── STANDARD FIELDS ──────────────────────────────────────────── --}}
    <div class="mb-3">
        @if($label)
            <label class="form-label small fw-medium text-body-secondary">{{ $label }}</label>
        @endif

        @if($type === 'select')
            <select class="form-select form-select-sm shadow-none bg-body-tertiary border"
                    name="{{ $name }}"
                    {{ $required ? 'required' : '' }}>
                        @if(!empty($field['enum']) && enum_exists($field['enum']))
                    {{-- Enum-backed select --}}
                    @foreach($field['enum']::cases() as $enumCase)
                        <option value="{{ $enumCase->value }}" @selected($value === $enumCase->value)>
                            {{ method_exists($enumCase, 'label') ? $enumCase->label() : $enumCase->value }}
                        </option>
                    @endforeach
                @elseif($options)
                    {{-- Static options array --}}
                    @foreach($options as $opt)
                        <option value="{{ $opt['value'] }}" @selected((string)$value === (string)$opt['value'])>
                            {{ $opt['label'] }}
                        </option>
                    @endforeach
                @endif
            </select>

        @elseif($type === 'textarea')
            <textarea class="form-control form-control-sm shadow-none bg-body-tertiary border"
                      name="{{ $name }}"
                      rows="4"
                      {{ $required ? 'required' : '' }}>{{ $value ?? '' }}</textarea>

        @elseif($type === 'file')
            <input type="file"
                   class="form-control form-control-sm shadow-none bg-body-tertiary border"
                   name="{{ $name }}"
                   {{ $required ? 'required' : '' }}>

        @else
            <input type="{{ $type }}"
                   class="form-control form-control-sm shadow-none bg-body-tertiary border"
                   name="{{ $name }}"
                   value="{{ $value ?? '' }}"
                   {{ $required ? 'required' : '' }}
                   @if($step)    step="{{ $step }}"       @endif
                   @if($pattern) pattern="{{ $pattern }}" @endif>
        @endif
    </div>

@endif