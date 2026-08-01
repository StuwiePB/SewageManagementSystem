<?php

namespace App\Http\Controllers;

use App\Models\RiskCell;
use App\Services\RiskScoreService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class RiskGridController extends Controller
{
    /**
     * Whole grid + shared forecast timeline + scoring weights, cached 10 minutes (matches
     * risk:sync's hourly cadence — the underlying data doesn't change more often than that, so
     * there's no point re-querying 1,100+ rows on every map load in between syncs).
     */
    public function index(Request $request): JsonResponse
    {
        $district = $request->query('district');
        $cacheKey = 'risk_grid:index:'.($district !== null ? strtolower((string) $district) : 'all');

        $payload = Cache::remember($cacheKey, now()->addMinutes(10), function () use ($district) {
            $query = RiskCell::query();
            if ($district) {
                $query->where('district', $district);
            }

            $cells = $query->orderBy('h3_index')->get();
            // Pick the timeline from any already-synced cell — a not-yet-synced cell (e.g. one
            // skipped by a rate-limited risk:sync chunk) would otherwise report an empty array
            // even though the grid as a whole has a perfectly good shared timeline.
            $forecastTimes = $cells->first(fn (RiskCell $c): bool => $c->forecast_times !== [])?->forecast_times ?? [];

            return [
                'cells' => $cells->map->toGrid()->values()->all(),
                'forecast_times' => $forecastTimes,
                'weights' => RiskScoreService::WEIGHTS,
                'generated_at' => now()->toIso8601String(),
            ];
        });

        return response()->json($payload);
    }

    /**
     * One cell with a full factor breakdown — powers the "why is this hexagon red" detail panel.
     */
    public function show(string $h3, RiskScoreService $scorer): JsonResponse
    {
        $cell = RiskCell::where('h3_index', $h3)->firstOrFail();

        $staticFactors = $scorer->staticFactors($cell);
        $rainfallFactor = $scorer->impliedRainfallFactor((float) $cell->current_score, $staticFactors);
        $factors = array_merge(['rainfall' => $rainfallFactor], $staticFactors);

        $breakdown = [];
        foreach ($factors as $name => $value) {
            $weight = RiskScoreService::WEIGHTS[$name];
            $breakdown[$name] = [
                'value' => $value,
                'weight' => $weight,
                'weighted_contribution' => round($value * $weight * 100, 2),
            ];
        }

        return response()->json([
            'cell' => array_merge($cell->toGrid(), [
                'elevation_m' => $cell->elevation_m,
                'drainage_capacity' => $cell->drainage_capacity,
                'historical_flood_events' => $cell->historical_flood_events,
            ]),
            'factors' => $breakdown,
            'forecast_scores' => $cell->forecast_scores,
            'forecast_times' => $cell->forecast_times,
        ]);
    }

    /**
     * Top 20 elevated-risk (high/critical band) cells, for an alerts list/feed.
     */
    public function alerts(): JsonResponse
    {
        $cells = RiskCell::query()
            ->elevatedRisk()
            ->orderByDesc('current_score')
            ->limit(20)
            ->get();

        return response()->json([
            'cells' => $cells->map->toGrid()->values()->all(),
        ]);
    }
}
