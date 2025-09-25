<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\EventName;
use Rap2hpoutre\FastExcel\FastExcel;

class EventNamesTableSeeder extends Seeder
{
    public function run(): void
    {
        $csvPath = database_path('data/event_names.csv');

        (new FastExcel)
            ->import($csvPath, function ($line) {
                return EventName::create([
                    'id' => $line['id'],
                    'name' => $line['name'],
                    // Convert the '1' or '0' string to a boolean
                    'display_on_client' => (bool)$line['display_on_client'],
                    'created_at' => $line['created_at'],
                    'updated_at' => $line['updated_at'],
                ]);
            });
    }
}
