<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        \App\Models\User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => \Hash::make('password'),
        ]);

        $this->call([
            MarketsTableSeeder::class,
            EventNamesTableSeeder::class,
            LogServiceTitanJobsTableSeeder::class,
        ]);
    }
}
