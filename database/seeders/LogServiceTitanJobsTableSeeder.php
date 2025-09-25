<?php

            namespace Database\Seeders;

            use Illuminate\Database\Seeder;
            use Illuminate\Support\Facades\DB;
            use Illuminate\Support\Facades\Log;

            class LogServiceTitanJobsTableSeeder extends Seeder
            {
                public function run()
                {
                    $filePath = database_path('data/log_service_titan_jobs.csv');
                    $chunkSize = 1000;

                    if (!file_exists($filePath)) {
                        Log::error("CSV file not found: {$filePath}");
                        return;
                    }

                    $handle = fopen($filePath, 'r');
                    if (!$handle) {
                        Log::error("Unable to open CSV file: {$filePath}");
                        return;
                    }

                    $header = null;
                    $rows = [];
                    $skippedRows = 0;
                    $totalRows = 0;

                    while (($line = fgetcsv($handle)) !== false) {
                        if (!$header) {
                            $header = $line;
                            continue;
                        }

                        $totalRows++;
                        $row = array_combine($header, $line);
                        $row = array_map('trim', $row);
                        $row = array_map(fn($v) => $v === '' ? null : $v, $row);

                        // Validate required fields
                        if (empty($row['service_titan_job_id']) || empty($row['business_unit_id'])) {
                            Log::warning('Skipping row: missing required fields', $row);
                            $skippedRows++;
                            continue;
                        }

                        // Validate market_id foreign key if present
                        if ($row['market_id'] && !DB::table('markets')->where('id', $row['market_id'])->exists()) {
                            Log::warning('Skipping row: invalid market_id', $row);
                            $skippedRows++;
                            continue;
                        }

                        $rows[] = [
                            'market_id'              => $row['market_id'],
                            'service_titan_job_id'   => $row['service_titan_job_id'],
                            'business_unit_id'       => $row['business_unit_id'],
                            'job_type_id'            => $row['job_type_id'],
                            'tag_type_ids'           => $row['tag_type_ids'],
                            'technician_id'          => $row['technician_id'],
                            'campaign_id'            => $row['campaign_id'],
                            'start'                  => $row['start'],
                            'end'                    => $row['end'],
                            'arrival_window_start'   => $row['arrival_window_start'],
                            'arrival_window_end'     => $row['arrival_window_end'],
                            'customer_id'            => $row['customer_id'],
                            'location_id'            => $row['location_id'],
                            'latitude'               => $row['latitude'],
                            'longitude'              => $row['longitude'],
                            'summary'                => $row['summary'],
                            'chargebee'              => $row['chargebee'] ?? false,
                            'web_session_data'       => $row['web_session_data'],
                            'attributions_sent'      => $row['attributions_sent'] ?? false,
                            'job_status'             => $row['job_status'],
                            's2f'                    => $row['s2f'] ?? false,
                            'referral_id'            => $row['referral_id'],
                            'created_at'             => $row['created_at'] ?? now(),
                            'updated_at'             => $row['updated_at'] ?? now(),
                        ];

                        if (count($rows) === $chunkSize) {
                            DB::table('log_service_titan_jobs')->insert($rows);
                            $rows = [];
                        }
                    }

                    if (!empty($rows)) {
                        DB::table('log_service_titan_jobs')->insert($rows);
                    }

                    fclose($handle);

                    Log::info("LogServiceTitanJobsTableSeeder completed. Total rows: {$totalRows}, Skipped: {$skippedRows}");
                }
            }
