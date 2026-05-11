<?php

namespace App\Features\Clients\Services;

use App\Core\Helpers\InputSanitizer;
use App\Core\Services\TransactionHandler;
use App\Features\Clients\Models\Client;
use App\Features\Clients\Models\ClientLocation;
use App\Features\Locations\Models\Location;

class ClientLocationService
{
    public function __construct(private TransactionHandler $transactions) {}

    public function create(Client $client, array $data): ClientLocation
    {
        return $this->transactions->execute(function () use ($client, $data) {
            $location = Location::create([
                'parish_id'      => $data['parish_id'] ?? null,
                'postal_code'    => $data['postal_code'] ?? '',
                'street_address' => InputSanitizer::sanitize($data['street_address'] ?? ''),
                'landmark'       => InputSanitizer::sanitize($data['landmark'] ?? ''),
                'latitude'       => $data['latitude'] ?? null,
                'longitude'      => $data['longitude'] ?? null,
            ]);

            if (!empty($data['is_primary'])) {
                $client->clientLocations()->update(['is_primary' => false]);
            }

            return ClientLocation::create([
                'client_id'   => $client->id,
                'location_id' => $location->id,
                'name'        => $data['name'],
                'is_primary'  => !empty($data['is_primary']),
            ]);
        });
    }

    public function update(ClientLocation $clientLocation, array $data): ClientLocation
    {
        return $this->transactions->execute(function () use ($clientLocation, $data) {
            $fieldMap = [
                'parish_id'      => 'parish_id',
                'postal_code'    => 'postal_code',
                'street_address' => 'street_address',
                'landmark'       => 'landmark',
                'latitude'       => 'latitude',
                'longitude'      => 'longitude',
            ];

            $locationData = [];
            foreach ($fieldMap as $key => $col) {
                if (!array_key_exists($key, $data)) continue;
                $val = $data[$key];
                if (in_array($key, ['street_address', 'landmark']) && $val !== null) {
                    $val = InputSanitizer::sanitize($val);
                }
                $locationData[$col] = $val;
            }

            if (!empty($locationData)) {
                $clientLocation->loadMissing('location');
                $clientLocation->location->update($locationData);
            }

            if (!empty($data['is_primary'])) {
                $clientLocation->loadMissing('client');
                $clientLocation->client->clientLocations()
                    ->where('id', '!=', $clientLocation->id)
                    ->update(['is_primary' => false]);
            }

            $pivot = [];
            if (array_key_exists('name', $data))       $pivot['name']       = $data['name'];
            if (array_key_exists('is_primary', $data)) $pivot['is_primary'] = (bool) $data['is_primary'];

            if (!empty($pivot)) {
                $clientLocation->update($pivot);
            }

            return $clientLocation->fresh(['location.parish']);
        });
    }

    public function delete(ClientLocation $clientLocation): void
    {
        $this->transactions->execute(fn () => $clientLocation->delete());
    }
}
