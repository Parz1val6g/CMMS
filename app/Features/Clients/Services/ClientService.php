<?php

namespace App\Features\Clients\Services;

use App\Core\Services\TransactionHandler;
use App\Features\Clients\Models\Client;

class ClientService
{
    public function __construct(
        private TransactionHandler $transactions
    ) {}

    public function create(array $data): Client
    {
        return $this->transactions->execute(function () use ($data) {
            return Client::create([
                'user_id' => $data['user_id'],
                'nif' => $data['nif'],
            ]);
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
