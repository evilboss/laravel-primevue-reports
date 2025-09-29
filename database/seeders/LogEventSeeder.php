<?php

namespace Database\Seeders;

use App\Models\Market;
use App\Models\LogEvent;
use App\Enums\EventType;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class LogEventSeeder extends Seeder
{
    public function run(): void
    {
        $markets = Market::all();
        $startDate = Carbon::now()->subDays(30);
        $endDate = Carbon::now();
        $sessionCounter = 1;

        foreach ($markets as $market) {
            for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
                $sessionsForDay = $this->getSessionsForMarket($market->code);
                
                for ($session = 0; $session < $sessionsForDay; $session++) {
                    $sessionId = 'SESSION_' . $market->code . '_' . $sessionCounter++;
                    $this->createConversionFunnel($market, $sessionId, $date);
                }
            }
        }
    }

    private function createConversionFunnel(Market $market, string $sessionId, Carbon $date): void
    {
        $events = EventType::getStepOrder();
        $baseTime = $date->copy()->addHours(rand(8, 20))->addMinutes(rand(0, 59));
        
        // Step 1 always happens (100% reach this step)
        $this->createEvent($market, $sessionId, $events[0], $baseTime);
        
        // Each subsequent step has a drop-off rate
        $dropOffRates = [0.0, 0.25, 0.20, 0.15, 0.10]; // Step 1 has 0% drop-off, step 2 has 25%, etc.
        
        for ($i = 1; $i < count($events); $i++) {
            // Determine if user continues to this step
            if (rand(1, 100) <= ($dropOffRates[$i] * 100)) {
                break; // User drops off at this step
            }
            
            $baseTime->addMinutes(rand(1, 15)); // Add some time between events
            $this->createEvent($market, $sessionId, $events[$i], $baseTime);
        }
    }

    private function createEvent(Market $market, string $sessionId, EventType $eventType, Carbon $timestamp): void
    {
        LogEvent::create([
            'market_id' => $market->id,
            'session_id' => $sessionId,
            'event_type' => $eventType,
            'event_timestamp' => $timestamp,
            'event_data' => [
                'user_agent' => 'Mozilla/5.0 (compatible)',
                'ip_address' => '192.168.1.' . rand(1, 254),
            ]
        ]);
    }

    private function getSessionsForMarket(string $marketCode): int
    {
        return match($marketCode) {
            'NYC' => rand(80, 120), // New York has most traffic
            'LA' => rand(70, 100),
            'CHI' => rand(60, 90),
            'HOU' => rand(55, 85),
            'PHX' => rand(50, 80),
            'PHL' => rand(65, 95),
            default => rand(40, 70)
        };
    }
}
