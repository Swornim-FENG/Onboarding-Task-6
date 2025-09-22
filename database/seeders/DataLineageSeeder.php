<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\DataLineage;


class DataLineageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DataLineage::create([
            'data_element' => 'customer_123',
            'action' => 'created',
            'source' => 'signup_form',
            'transformation' => 'none',
            'destination' => 'primary_db',
            'metadata' => ['ip' => '127.0.0.1', 'user_agent' => 'Seeder'],
            'occurred_at' => now()->subDays(10),
        ]);

        DataLineage::create([
            'data_element' => 'customer_123',
            'action' => 'updated',
            'source' => 'admin_panel',
            'transformation' => 'normalized_phone',
            'destination' => 'primary_db',
            'metadata' => ['admin_id' => 1],
            'occurred_at' => now()->subDays(5),
        ]);

        DataLineage::create([
            'data_element' => 'customer_123',
            'action' => 'exported',
            'source' => 'analytics_job',
            'transformation' => 'hashed_email',
            'destination' => 'analytics_db',
            'metadata' => ['job_id' => 'job_456'],
            'occurred_at' => now(),
        ]);
    }
}
