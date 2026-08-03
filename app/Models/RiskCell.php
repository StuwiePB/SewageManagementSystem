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
        'slope_pct',
        'historical_flood_events',
        'open_blockage_reports',
        'current_score',
        'current_band',
        'landslide_score',
        'landslide_band',
        'forecast_scores',
        'forecast_times',
        'forecast_rainfall_mm',
        'now_index',
        'synced_at',
    ];

    protected function casts(): array
    {
        return [
            'lat' => 'float',
            'lng' => 'float',
            'elevation_m' => 'float',
            'drainage_capacity' => 'float',
            'slope_pct' => 'float',
            'historical_flood_events' => 'integer',
            'open_blockage_reports' => 'integer',
            'current_score' => 'float',
            'landslide_score' => 'float',
            'forecast_scores' => 'array',
            'forecast_times' => 'array',
            'forecast_rainfall_mm' => 'array',
            'now_index' => 'integer',
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
            'forecast_rainfall_mm' => $this->forecast_rainfall_mm,
            'now_index' => $this->now_index,
            'open_blockage_reports' => $this->open_blockage_reports,
            'landslide_score' => $this->landslide_score,
            'landslide_band' => $this->landslide_band,
            'slope_pct' => $this->slope_pct,
            'synced_at' => $this->synced_at?->toIso8601String(),
        ];
    }
}
