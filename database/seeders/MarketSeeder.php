<?php

namespace Database\Seeders;

use App\Models\Market;
use Illuminate\Database\Seeder;

class MarketSeeder extends Seeder
{
    public function run(): void
    {
        $markets = [
            ['name' => 'New York City', 'code' => 'NYC', 'timezone' => 'America/New_York'],
            ['name' => 'Los Angeles', 'code' => 'LA', 'timezone' => 'America/Los_Angeles'],
            ['name' => 'Chicago', 'code' => 'CHI', 'timezone' => 'America/Chicago'],
            ['name' => 'Houston', 'code' => 'HOU', 'timezone' => 'America/Chicago'],
            ['name' => 'Phoenix', 'code' => 'PHX', 'timezone' => 'America/Phoenix'],
            ['name' => 'Philadelphia', 'code' => 'PHL', 'timezone' => 'America/New_York'],
        ];

        foreach ($markets as $market) {
            Market::create($market);
        }
    }
}
