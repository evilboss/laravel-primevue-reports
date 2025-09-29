<?php

namespace App\Http\Controllers\Api\Reports;

use App\Http\Controllers\Controller;
use App\Http\Requests\Reports\ReportsRequest;
use App\Services\Reports\ConversionFunnelReportService;

class ConversionFunnelController extends Controller
{
    public function __construct(private ConversionFunnelReportService $reportService)
    {
        //
    }

    public function index(ReportsRequest $request)
    {
        try {
            $data = $this->reportService->getConversionFunnelData(
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
                'message' => 'Failed to retrieve conversion funnel data.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    public function export(ReportsRequest $request)
    {
        try {
            $data = $this->reportService->getConversionFunnelDataForCsv(
                $request->validated()['market_ids'] ?? [],
                $request->validated()['start_date'],
                $request->validated()['end_date']
            );

            $filename = 'conversion_funnel_' . now()->format('Y_m_d_His') . '.csv';

            $headers = [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => "attachment; filename=\"$filename\"",
            ];

            $callback = function () use ($data) {
                $file = fopen('php://output', 'w');
                
                // Add UTF-8 BOM for proper Excel handling
                fwrite($file, "\xEF\xBB\xBF");
                
                // CSV Headers
                fputcsv($file, ['Market', 'Event', 'Conversions Total', 'Conversions Percentage']);
                
                foreach ($data as $row) {
                    fputcsv($file, [
                        $row['market'],
                        $row['event'],
                        $row['conversions_total'],
                        $row['conversions_percentage'] . '%',
                    ]);
                }
                
                fclose($file);
            };

            return response()->stream($callback, 200, $headers);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to export conversion funnel data.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }
}
