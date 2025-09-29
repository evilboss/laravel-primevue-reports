<?php

namespace Database\Seeders;

use App\Models\Market;
use App\Models\LogServiceTitanJob;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class LogServiceTitanJobSeeder extends Seeder
{
    public function run(): void
    {
        $markets = Market::all();
        $startDate = Carbon::now()->subDays(90);
        $endDate = Carbon::now();

        foreach ($markets as $market) {
            for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
                // Generate different patterns for different markets
                $baseBookings = $this->getBaseBookingsForMarket($market->code);
                $dailyVariation = rand(-3, 8); // Random daily variation
                $bookingsCount = max(0, $baseBookings + $dailyVariation);
                
                // Create individual booking records for the day
                for ($i = 0; $i < $bookingsCount; $i++) {
                    LogServiceTitanJob::create([
                        'market_id' => $market->id,
                        'job_id' => 'JOB_' . $market->code . '_' . $date->format('Ymd') . '_' . ($i + 1),
                        'booking_date' => $date->toDateString(),
                        'booking_amount' => rand(15000, 85000) / 100, // $150-$850
                        'customer_type' => rand(0, 1) ? 'new' : 'returning',
                        'service_type' => ['plumbing', 'electrical', 'hvac', 'general'][rand(0, 3)],
                        'metadata' => [
                            'source' => ['website', 'phone', 'app'][rand(0, 2)],
                            'priority' => ['normal', 'urgent', 'emergency'][rand(0, 2)]
                        ]
                    ]);
                }
            }
        }
    }

    private function getBaseBookingsForMarket(string $marketCode): int
    {
        return match($marketCode) {
            'NYC' => 12, // New York is the busiest
            'LA' => 10,
            'CHI' => 8,
            'HOU' => 7,
            'PHX' => 6,
            'PHL' => 9,
            default => 5
        };
    }
}
