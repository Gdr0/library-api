<?php

namespace Database\Seeders;

use App\Models\Client;
use Illuminate\Database\Seeder;

class ClientSeeder extends Seeder
{
    public function run(): void
    {
        $clients = [
            [
                'name' => 'Marco',
                'last_name' => 'Rossi',
                'phone_number' => '3331000001',
                'email' => 'marco.rossi@example.com',
            ],
            [
                'name' => 'Giulia',
                'last_name' => 'Bianchi',
                'phone_number' => '3331000002',
                'email' => 'giulia.bianchi@example.com',
            ],
            [
                'name' => 'Luca',
                'last_name' => 'Romano',
                'phone_number' => '3331000003',
                'email' => 'luca.romano@example.com',
            ],
            [
                'name' => 'Sara',
                'last_name' => 'Ferrari',
                'phone_number' => '3331000004',
                'email' => 'sara.ferrari@example.com',
            ],
            [
                'name' => 'Andrea',
                'last_name' => 'Esposito',
                'phone_number' => '3331000005',
                'email' => 'andrea.esposito@example.com',
            ],
            [
                'name' => 'Elena',
                'last_name' => 'Colombo',
                'phone_number' => '3331000006',
                'email' => 'elena.colombo@example.com',
            ],
            [
                'name' => 'Matteo',
                'last_name' => 'Ricci',
                'phone_number' => '3331000007',
                'email' => 'matteo.ricci@example.com',
            ],
            [
                'name' => 'Francesca',
                'last_name' => 'Marino',
                'phone_number' => '3331000008',
                'email' => 'francesca.marino@example.com',
            ],
            [
                'name' => 'Davide',
                'last_name' => 'Greco',
                'phone_number' => '3331000009',
                'email' => 'davide.greco@example.com',
            ],
            [
                'name' => 'Chiara',
                'last_name' => 'Conti',
                'phone_number' => '3331000010',
                'email' => 'chiara.conti@example.com',
            ],
        ];

        foreach ($clients as $client) {
            Client::updateOrCreate(
                ['email' => $client['email']],
                $client,
            );
        }
    }
}
