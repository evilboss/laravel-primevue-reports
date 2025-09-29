<?php

namespace App\Http\Controllers\Api\Reports;

use App\Http\Controllers\Controller;
use App\Http\Requests\Reports\ReportsRequest;
use App\Services\Reports\JobBookingsReportService;

class JobBookingsController extends Controller
{
    public function __construct(private JobBookingsReportService $reportService)
    {
        //
    }

    public function index(ReportsRequest $request)
    {
        try {
            $data = $this->reportService->getJobBookingsData(
                $request->validated()['market_ids'] ?? [],
                $request->validated()['start_date'],
                $request->validated()['end_date']
            );

            return response()->json([
                'success' => true,
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve job bookings data.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    public function export(ReportsRequest $request)
    {
        try {
            $data = $this->reportService->getJobBookingsDataForCsv(
                $request->validated()['market_ids'] ?? [],
                $request->validated()['start_date'],
                $request->validated()['end_date']
            );

            $filename = 'job_bookings_' . now()->format('Y_m_d_His') . '.csv';

            $headers = [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => "attachment; filename=\"$filename\"",
            ];

            $callback = function () use ($data) {
                $file = fopen('php://output', 'w');
                
                // Add UTF-8 BOM for proper Excel handling
                fwrite($file, "\xEF\xBB\xBF");
                
                // CSV Headers
                fputcsv($file, ['Market', 'Date', 'Bookings']);
                
                foreach ($data as $row) {
                    fputcsv($file, [
                        $row->market,
                        $row->date,
                        $row->bookings,
                    ]);
                }
                
                fclose($file);
            };

            return response()->stream($callback, 200, $headers);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to export job bookings data.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }
}
