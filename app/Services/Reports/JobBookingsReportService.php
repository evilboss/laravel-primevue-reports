<?php

namespace App\Services\Reports;

use App\Models\LogServiceTitanJob;
use App\Models\Market;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class JobBookingsReportService
{
    public function getJobBookingsData(array $marketIds, string $startDate, string $endDate): array
    {
        $query = LogServiceTitanJob::with('market')
            ->forDateRange($startDate, $endDate);

        if (!empty($marketIds)) {
            $query->forMarkets($marketIds);
        }

        $bookings = $query->selectRaw('
                market_id,
                booking_date,
                COUNT(*) as bookings_count
            ')
            ->groupBy('market_id', 'booking_date')
            ->orderBy('booking_date')
            ->get();

        return $this->formatJobBookingsForChart($bookings, $startDate, $endDate);
    }

    public function getJobBookingsDataForCsv(array $marketIds, string $startDate, string $endDate): Collection
    {
        $query = LogServiceTitanJob::with('market')
            ->forDateRange($startDate, $endDate);

        if (!empty($marketIds)) {
            $query->forMarkets($marketIds);
        }

        return $query->selectRaw('
                markets.name as market,
                booking_date as date,
                COUNT(*) as bookings
            ')
            ->join('markets', 'markets.id', '=', 'log_service_titan_jobs.market_id')
            ->groupBy('markets.id', 'markets.name', 'booking_date')
            ->orderBy('markets.name', 'booking_date')
            ->get();
    }

    private function formatJobBookingsForChart(Collection $bookings, string $startDate, string $endDate): array
    {
        // Get all markets involved
        $markets = Market::whereIn('id', $bookings->pluck('market_id')->unique())
            ->orderBy('name')
            ->get();

        // Create date range
        $dates = [];
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);
        
        while ($start->lte($end)) {
            $dates[] = $start->copy()->toDateString();
            $start->addDay();
        }

        // Organize data by market
        $datasets = [];
        foreach ($markets as $market) {
            $marketBookings = $bookings->where('market_id', $market->id);
            $data = [];

            foreach ($dates as $date) {
                $dayBookings = $marketBookings->where('booking_date', $date)->first();
                $data[] = $dayBookings ? (int) $dayBookings->bookings_count : 0;
            }

            $datasets[] = [
                'label' => $market->name,
                'data' => $data,
                'borderColor' => $this->getColorForMarket($market->code),
                'backgroundColor' => $this->getColorForMarket($market->code, 0.1),
                'tension' => 0.4,
            ];
        }

        return [
            'labels' => $dates,
            'datasets' => $datasets,
        ];
    }

    private function getColorForMarket(string $marketCode, float $alpha = 1): string
    {
        $colors = [
            'NYC' => "rgba(59, 130, 246, $alpha)", // Blue
            'LA' => "rgba(239, 68, 68, $alpha)",   // Red
            'CHI' => "rgba(34, 197, 94, $alpha)",  // Green
            'HOU' => "rgba(168, 85, 247, $alpha)", // Purple
            'PHX' => "rgba(245, 158, 11, $alpha)", // Amber
            'PHL' => "rgba(236, 72, 153, $alpha)", // Pink
        ];

        return $colors[$marketCode] ?? "rgba(156, 163, 175, $alpha)"; // Gray fallback
    }
}