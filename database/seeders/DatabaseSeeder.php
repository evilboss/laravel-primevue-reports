<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Market;
use App\Enums\UserRole;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create markets first
        $this->call([
            MarketSeeder::class,
        ]);

        // Create admin user
        $admin = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'role' => UserRole::ADMIN,
        ]);

        // Create market users
        $markets = Market::all();
        
        $marketUser1 = User::factory()->create([
            'name' => 'Market User NYC/LA',
            'email' => 'market1@example.com',
            'password' => bcrypt('password'),
            'role' => UserRole::MARKET_USER,
        ]);
        
        $marketUser2 = User::factory()->create([
            'name' => 'Market User CHI/HOU',
            'email' => 'market2@example.com',  
            'password' => bcrypt('password'),
            'role' => UserRole::MARKET_USER,
        ]);

        // Assign markets to market users
        $marketUser1->markets()->attach([
            $markets->where('code', 'NYC')->first()->id,
            $markets->where('code', 'LA')->first()->id,
        ]);

        $marketUser2->markets()->attach([
            $markets->where('code', 'CHI')->first()->id,
            $markets->where('code', 'HOU')->first()->id,
        ]);

        // Create test data for reports
        $this->call([
            LogServiceTitanJobSeeder::class,
            LogEventSeeder::class,
        ]);
    }
}
