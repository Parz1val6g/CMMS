<?php

namespace App\Features\Locations\Services;

use App\Core\Helpers\InputSanitizer;
use App\Core\Services\TransactionHandler;
use App\Features\Locations\Models\Location;

class LocationService
{
    public function __construct(
        private TransactionHandler $transactions
    ) {}

    public function create(array $data): Location
    {
        return $this->transactions->execute(function () use ($data) {
            return Location::create([
                'parish_id' => $data['parish_id'],
                'postal_code' => $data['postal_code'],
                'street_address' => InputSanitizer::sanitize($data['street_address']),
                'landmark' => isset($data['landmark']) ? InputSanitizer::sanitize($data['landmark']) : null,
                'latitude' => $data['latitude'] ?? null,
                'longitude' => $data['longitude'] ?? null,
            ]);
        });
    }

    public function update(Location $location, array $data): Location
    {
        return $this->transactions->execute(function () use ($location, $data) {
            $location->update($data);
            return $location;
        });
    }

    public function delete(Location $location): ?bool
    {
        return $this->transactions->execute(function () use ($location) {
            return $location->delete();
        });
    }
}
