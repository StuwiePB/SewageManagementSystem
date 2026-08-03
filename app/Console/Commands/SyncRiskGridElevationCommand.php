<?php

namespace App\Console\Commands;

use App\Models\RiskCell;
use App\Services\WeatherService;
use Illuminate\Console\Command;

class SyncRiskGridElevationCommand extends Command
{
    protected $signature = 'risk:sync-elevation';

    protected $description = 'Backfill real elevation (Open-Meteo DEM) and derived slope for every risk grid cell. Occasional/one-off — unlike risk:sync, not scheduled, since terrain doesn\'t change day to day.';

    /**
     * Neighbor search radius (km) for slope calculation. The H3 resolution-7 grid used here has
     * a ~2.1km average center-to-center spacing between adjacent cells, so 3km comfortably
     * covers the immediate ring around a cell (with margin for the hex grid's spacing variance)
     * without reaching two rings out.
     */
    private const NEIGHBOR_RADIUS_KM = 3.0;

    public function handle(WeatherService $weather): int
    {
        $cells = RiskCell::all();

        if ($cells->isEmpty()) {
            $this->warn('No risk cells found. Run `php artisan db:seed --class=Database\\Seeders\\RiskCellSeeder` first.');

            return self::FAILURE;
        }

        $points = $cells->mapWithKeys(
            static fn (RiskCell $cell): array => [$cell->h3_index => ['lat' => $cell->lat, 'lng' => $cell->lng]]
        )->all();

        $this->info('Fetching elevation for '.count($points).' cells from Open-Meteo...');
        $elevations = $weather->elevations($points);

        if ($elevations === []) {
            $this->error('Elevation fetch failed for all cells — aborting without touching stored data.');

            return self::FAILURE;
        }

        $bar = $this->output->createProgressBar($cells->count());
        $bar->start();

        $updated = 0;
        foreach ($cells as $cell) {
            $elevation = $elevations[$cell->h3_index] ?? null;
            if ($elevation !== null) {
                $cell->elevation_m = $elevation;
                $cell->save();
                $updated++;
            }
            $bar->advance();
        }
        $bar->finish();
        $this->newLine();
        $this->info("Elevation backfilled for {$updated}/{$cells->count()} cells.");

        $this->info('Computing slope from neighboring cells...');
        $cells = RiskCell::all()->filter(fn (RiskCell $c): bool => $c->elevation_m !== null)->values();
        $bar = $this->output->createProgressBar($cells->count());
        $bar->start();

        $slopeUpdated = 0;
        foreach ($cells as $cell) {
            $maxGradePct = 0.0;

            foreach ($cells as $other) {
                if ($other->h3_index === $cell->h3_index) {
                    continue;
                }

                $distKm = $this->haversineKm($cell->lat, $cell->lng, $other->lat, $other->lng);
                if ($distKm > self::NEIGHBOR_RADIUS_KM || $distKm < 0.05) {
                    continue;
                }

                $deltaM = abs($cell->elevation_m - $other->elevation_m);
                $gradePct = ($deltaM / ($distKm * 1000)) * 100;
                $maxGradePct = max($maxGradePct, $gradePct);
            }

            $cell->slope_pct = round($maxGradePct, 2);
            $cell->save();
            $slopeUpdated++;
            $bar->advance();
        }
        $bar->finish();
        $this->newLine();

        $this->info("Slope computed for {$slopeUpdated}/{$cells->count()} cells. Run `php artisan risk:sync` next to refresh scores against the new terrain data.");

        return self::SUCCESS;
    }

    private function haversineKm(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earth = 6371;
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        $a = sin($dLat / 2) ** 2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;

        return $earth * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }
}
