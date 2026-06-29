<?php

namespace App\Services;

use App\Models\Client;

class ClientService
{

    // decentralizzo la creazione del cliente visto che può essere generato sia in fase di creazione prestito che direttamente nella creazione clienti

    public function createOrUpdate(array $data, ?int $clientId = null): Client
    {
        if (! empty($clientId)) {
            $client = Client::findOrFail($clientId);
            $client->update($data);

            return $client;
        } else {
            $client = Client::create($data);

            return $client;
        }
    }
}
