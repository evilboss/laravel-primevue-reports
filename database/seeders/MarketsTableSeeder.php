<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Market;
use Rap2hpoutre\FastExcel\FastExcel;

class MarketsTableSeeder extends Seeder
{
    public function run(): void
    {
        $csvPath = database_path('data/markets.csv');

        $normalizeDate = fn($value) => $value === '' || strtolower(trim($value)) === 'null' ? null : $value;

        Market::withoutEvents(function () use ($csvPath, $normalizeDate) {

            $data = (new FastExcel)
                ->import($csvPath, function ($line) use ($normalizeDate) {
                    return [
                        'name' => $line['name'],
                        'domain' => $line['domain'],
                        'path' => $line['path'],
                        'time_zone_id' => (int)$line['time_zone_id'],
                        'created_at' => $line['created_at'],
                        'updated_at' => $line['updated_at'],
                        'deleted_at' => $normalizeDate($line['deleted_at']),
                        'latest_unavailability' => $normalizeDate($line['latest_unavailability']),
                    ];
                });

            Market::unguard();
            Market::insert($data->toArray());
            Market::reguard();
        });

    }
}
