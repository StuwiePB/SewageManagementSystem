<?php

namespace App\Console\Commands;

use App\Models\Report;
use App\Models\RiskCell;
use App\Services\RiskScoreService;
use App\Services\WeatherService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

class SyncRiskGridCommand extends Command
{
    protected $signature = 'risk:sync';

    protected $description = 'Refresh live rainfall-driven risk scores for every cell in the drainage risk grid';

    public function handle(WeatherService $weather, RiskScoreService $scorer): int
    {
        $cells = RiskCell::all();

        if ($cells->isEmpty()) {
            $this->warn('No risk cells found. Run `php artisan db:seed --class=Database\\Seeders\\RiskCellSeeder` first.');

            return self::FAILURE;
        }

        $blockageCounts = $this->openBlockageCountsByCell();

        $points = $cells->mapWithKeys(
            static fn (RiskCell $cell): array => [$cell->h3_index => ['lat' => $cell->lat, 'lng' => $cell->lng]]
        )->all();

        $rainfall = $weather->hourlyRainfall($points);

        $bar = $this->output->createProgressBar($cells->count());
        $bar->start();

        $bandCounts = ['critical' => 0, 'high' => 0, 'moderate' => 0, 'low' => 0];
        $synced = 0;

        foreach ($cells as $cell) {
            // Open blockage-report counts come straight from our own database, so they're
            // refreshed every run regardless of whether the weather API is reachable.
            $cell->open_blockage_reports = $blockageCounts[$cell->h3_index] ?? 0;

            $forecast = $rainfall[$cell->h3_index] ?? null;

            if ($forecast === null || $forecast['times'] === []) {
                // Weather fetch failed for this cell's batch — leave current_score/current_band/
                // forecast_* exactly as they were rather than zeroing the map out. We still
                // persist the fresh blockage count above.
                $cell->save();
                $bandCounts[$cell->current_band] = ($bandCounts[$cell->current_band] ?? 0) + 1;
                $bar->advance();

                continue;
            }

            $series = $scorer->scoreSeries($cell, $forecast['times'], $forecast['precipitation'], $forecast['probability']);
            $current = $series[0];

            $cell->forceFill([
                'current_score' => $current['score'],
                'current_band' => $current['band'],
                'forecast_scores' => array_map(static fn (array $s): float => $s['score'], $series),
                'forecast_times' => array_map(static fn (array $s): string => $s['time'], $series),
                'synced_at' => now(),
            ])->save();

            $bandCounts[$current['band']] = ($bandCounts[$current['band']] ?? 0) + 1;
            $synced++;
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();

        $this->info(sprintf(
            'risk:sync complete — %d/%d cells refreshed with fresh rainfall data. Bands: %d critical, %d high, %d moderate, %d low.',
            $synced,
            $cells->count(),
            $bandCounts['critical'],
            $bandCounts['high'],
            $bandCounts['moderate'],
            $bandCounts['low']
        ));

        return self::SUCCESS;
    }

    /**
     * @return array<string, int>
     */
    private function openBlockageCountsByCell(): array
    {
        if (! Schema::hasTable('reports') || ! Schema::hasColumn('reports', 'h3_index')) {
            return [];
        }

        return Report::query()
            ->whereNotNull('h3_index')
            ->whereNotIn('status', ['resolved', 'cancelled'])
            ->selectRaw('h3_index, count(*) as cnt')
            ->groupBy('h3_index')
            ->pluck('cnt', 'h3_index')
            ->map(static fn ($count): int => (int) $count)
            ->all();
    }
}
