<?php

namespace App\Services\Reports;

use App\Models\LogEvent;
use App\Enums\EventType;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class ConversionFunnelReportService
{
    public function getConversionFunnelData(array $marketIds, string $startDate, string $endDate): array
    {
        $startDateTime = Carbon::parse($startDate)->startOfDay();
        $endDateTime = Carbon::parse($endDate)->endOfDay();

        // Get all sessions that started in the date range
        $sessionsQuery = LogEvent::forDateRange($startDateTime, $endDateTime)
            ->forEventType(EventType::JOB_TYPE_ZIP_COMPLETED);

        if (!empty($marketIds)) {
            $sessionsQuery->forMarkets($marketIds);
        }

        $totalSessions = $sessionsQuery->distinct('session_id')->count();

        if ($totalSessions === 0) {
            return $this->getEmptyFunnelData();
        }

        // Get conversion counts for each step
        $funnelData = [];
        $steps = EventType::getStepOrder();
        $previousStepCount = $totalSessions;

        foreach ($steps as $index => $eventType) {
            $eventQuery = LogEvent::forDateRange($startDateTime, $endDateTime)
                ->forEventType($eventType);

            if (!empty($marketIds)) {
                $eventQuery->forMarkets($marketIds);
            }

            $currentStepCount = $eventQuery->distinct('session_id')->count();
            $conversionRate = $previousStepCount > 0 ? ($currentStepCount / $previousStepCount) * 100 : 0;

            $funnelData[] = [
                'step' => $index + 1,
                'event' => $eventType->getDisplayName(),
                'conversions_total' => $currentStepCount,
                'conversions_percentage' => round($conversionRate, 1),
                'previous_step_count' => $previousStepCount,
            ];

            $previousStepCount = $currentStepCount;
        }

        return [
            'total_sessions' => $totalSessions,
            'funnel_data' => $funnelData,
        ];
    }

    public function getConversionFunnelDataForCsv(array $marketIds, string $startDate, string $endDate): Collection
    {
        $startDateTime = Carbon::parse($startDate)->startOfDay();
        $endDateTime = Carbon::parse($endDate)->endOfDay();

        // Get market names
        $marketNames = [];
        if (!empty($marketIds)) {
            $markets = \App\Models\Market::whereIn('id', $marketIds)->get();
            $marketNames = $markets->pluck('name', 'id')->toArray();
        }

        $csvData = collect();
        $steps = EventType::getStepOrder();

        foreach ($marketIds as $marketId) {
            $marketName = $marketNames[$marketId] ?? 'Unknown';
            
            // Get sessions for this specific market
            $totalSessions = LogEvent::forDateRange($startDateTime, $endDateTime)
                ->forEventType(EventType::JOB_TYPE_ZIP_COMPLETED)
                ->forMarkets([$marketId])
                ->distinct('session_id')
                ->count();

            if ($totalSessions === 0) {
                continue;
            }

            $previousStepCount = $totalSessions;

            foreach ($steps as $eventType) {
                $currentStepCount = LogEvent::forDateRange($startDateTime, $endDateTime)
                    ->forEventType($eventType)
                    ->forMarkets([$marketId])
                    ->distinct('session_id')
                    ->count();

                $conversionRate = $previousStepCount > 0 ? ($currentStepCount / $previousStepCount) * 100 : 0;

                $csvData->push([
                    'market' => $marketName,
                    'event' => $eventType->getDisplayName(),
                    'conversions_total' => $currentStepCount,
                    'conversions_percentage' => round($conversionRate, 1),
                ]);

                $previousStepCount = $currentStepCount;
            }
        }

        return $csvData;
    }

    private function getEmptyFunnelData(): array
    {
        $steps = EventType::getStepOrder();
        $funnelData = [];

        foreach ($steps as $index => $eventType) {
            $funnelData[] = [
                'step' => $index + 1,
                'event' => $eventType->getDisplayName(),
                'conversions_total' => 0,
                'conversions_percentage' => 0,
                'previous_step_count' => 0,
            ];
        }

        return [
            'total_sessions' => 0,
            'funnel_data' => $funnelData,
        ];
    }
}