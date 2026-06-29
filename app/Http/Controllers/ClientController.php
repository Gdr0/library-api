<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Client;
use App\Services\ClientService;

class ClientController extends Controller
{
    public function __construct(
        private ClientService $clientService,
    ) {}

    public function CreateOrUpdateClient(Request $request) {
        $clientId = $request->integer('id') ?: null;
        $uniquePhoneNumber = 'unique:clients,phone_number'.($clientId ? ','.$clientId : '');
        $uniqueEmail = 'unique:clients,email'.($clientId ? ','.$clientId : '');

        $validated = $request->validate([
            'id' => 'nullable | integer | exists:clients,id',
            'name' => 'required | string | max:25',
            'last_name' => 'required | string | max:25',
            'phone_number' => 'required | string | max:25 | '.$uniquePhoneNumber,
            'email' => 'required | string | email | max:255 | '.$uniqueEmail,
            ]);

        $client = $this->clientService->createOrUpdate($validated, $clientId);
        $isUpdated = $clientId !== null;

        return response()->json([
            'client' => $client,
            'message' => $isUpdated ? 'client updated successfully' : 'client created successfully',
        ], $isUpdated ? 200 : 201);
    }

    public function clientIndex() {
        $clients = Client::paginate(10);

        return response()->json(['clients' => $clients]);
    }

    public function getClientById($id) {
        $client = Client::findOrFail($id);

        return response()->json([
            'client' => $client,
        ]);
    }
}
