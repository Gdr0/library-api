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

    public function storeOrUpdate(Request $request) {
// costruisce dinamicamente le regole unique
// update: id cliente corrente viene escluso dalla verifica così email e numero di telefono già associati allo stesso cliente non generano errore.
// create: viene applicato unique alla colonna
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

    public function index() {
        $clients = Client::paginate(10);

        return response()->json(['clients' => $clients]);
    }

    public function show($id) {
        $client = Client::findOrFail($id);

        return response()->json([
            'client' => $client,
        ]);
    }
}
