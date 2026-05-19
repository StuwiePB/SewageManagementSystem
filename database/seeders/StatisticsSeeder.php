<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class StatisticsSeeder extends Seeder
{
    /**
     * No-op: hotspot and statistics views read live data from operations_reports and work_orders.
     * Run: php artisan db:seed --class=StatisticsSeeder
     */
    public function run(): void
    {
        $this->command->info('StatisticsSeeder: skipped — hotspot reads live data.');
    }
}
