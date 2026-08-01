<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class RiskCell extends Model
{
    protected $fillable = [
        'h3_index',
        'lat',
        'lng',
        'district',
        'elevation_m',
        'drainage_capacity',
        'historical_flood_events',
        'open_blockage_reports',
        'current_score',
        'current_band',
        'forecast_scores',
        'forecast_times',
        'synced_at',
    ];

    protected function casts(): array
    {
        return [
            'lat' => 'float',
            'lng' => 'float',
            'elevation_m' => 'float',
            'drainage_capacity' => 'float',
            'historical_flood_events' => 'integer',
            'open_blockage_reports' => 'integer',
            'current_score' => 'float',
            'forecast_scores' => 'array',
            'forecast_times' => 'array',
            'synced_at' => 'datetime',
        ];
    }

    public function scopeElevatedRisk(Builder $query): Builder
    {
        return $query->whereIn('current_band', ['high', 'critical']);
    }

    /**
     * Compact shape for the map API — enough to draw + colour the hexagon, open the detail panel
     * on click, and (via forecast_scores) let the map's forecast-hour slider re-render every cell
     * from this cached array without a refetch per drag.
     *
     * @return array<string, mixed>
     */
    public function toGrid(): array
    {
        return [
            'h3_index' => $this->h3_index,
            'lat' => $this->lat,
            'lng' => $this->lng,
            'district' => $this->district,
            'score' => $this->current_score,
            'band' => $this->current_band,
            'forecast_scores' => $this->forecast_scores,
            'open_blockage_reports' => $this->open_blockage_reports,
            'synced_at' => $this->synced_at?->toIso8601String(),
        ];
    }
}
