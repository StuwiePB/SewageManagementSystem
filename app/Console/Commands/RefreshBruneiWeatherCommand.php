<?php

namespace App\Console\Commands;

use App\Services\BruneiWeatherService;
use Illuminate\Console\Command;

class RefreshBruneiWeatherCommand extends Command
{
    protected $signature = 'weather:refresh-brunei';

    protected $description = 'Force-refresh the cached Brunei weather snapshot used by the admin/operations GIS map';

    public function handle(BruneiWeatherService $weather): int
    {
        $data = $weather->refresh();

        if ($data === null) {
            $this->error('Failed to fetch Brunei weather from Open-Meteo.');

            return self::FAILURE;
        }

        $this->info('Brunei weather cache refreshed at '.now()->toDateTimeString().'.');

        return self::SUCCESS;
    }
}
