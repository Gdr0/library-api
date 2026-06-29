<?php

namespace Database\Seeders;

use App\Models\LoanStatus;
use Illuminate\Database\Seeder;

class LoanStatusSeeder extends Seeder
{
    public function run(): void
    {
        $statuses = [
            ['id' => 1, 'status' => 'open', 'label' => 'aperto'],
            ['id' => 2, 'status' => 'closed', 'label' => 'chiuso'],
            ['id' => 3, 'status' => 'overdue', 'label' => 'in ritardo'],
            ['id' => 4, 'status' => 'closed_late', 'label' => 'chiuso in ritardo'],
        ];

        foreach ($statuses as $statusData) {
            LoanStatus::updateOrCreate(
                ['id' => $statusData['id']],
                [
                    'status' => $statusData['status'],
                    'label' => $statusData['label'],
                ],
            );
        }
    }
}
