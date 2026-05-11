<?php

namespace App\Features\Clients\Services;

use App\Core\Helpers\InputSanitizer;
use App\Core\Services\TransactionHandler;
use App\Features\Clients\Models\Client;
use App\Features\Clients\Models\ClientLocation;
use App\Features\Locations\Models\Location;
use App\Shared\Models\Role;
use App\Shared\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ClientService
{
    public function __construct(
        private TransactionHandler $transactions
    ) {}

    public function create(array $data): Client
    {
        return $this->transactions->execute(function () use ($data) {
            $user = User::create([
                'first_name' => $data['first_name'],
                'last_name'  => $data['last_name'],
                'email'      => $data['email'],
                'phone'      => $data['phone'] ?? null,
                'password'   => Hash::make(Str::random(24)),
                'status'     => 'active',
            ]);

            $clientRole = Role::where('name', 'client')->first();
            if ($clientRole) {
                $user->roles()->attach($clientRole->id);
            }

            $client = Client::create([
                'user_id' => $user->id,
                'nif'     => $data['nif'],
            ]);

            foreach ($data['locations'] ?? [] as $locData) {
                $location = Location::create([
                    'parish_id'      => $locData['parish_id'] ?? null,
                    'postal_code'    => $locData['postal_code'] ?? '',
                    'street_address' => InputSanitizer::sanitize($locData['street_address'] ?? ''),
                    'landmark'       => InputSanitizer::sanitize($locData['landmark'] ?? ''),
                    'latitude'       => $locData['latitude'] ?? null,
                    'longitude'      => $locData['longitude'] ?? null,
                ]);

                ClientLocation::create([
                    'client_id'   => $client->id,
                    'location_id' => $location->id,
                    'name'        => $locData['name'],
                    'is_primary'  => !empty($locData['is_primary']),
                ]);
            }

            return $client;
        });
    }

    public function update(Client $client, array $data): Client
    {
        return $this->transactions->execute(function () use ($client, $data) {
            $client->update($data);
            return $client;
        });
    }

    public function delete(Client $client): ?bool
    {
        return $this->transactions->execute(function () use ($client) {
            return $client->delete();
        });
    }
}
