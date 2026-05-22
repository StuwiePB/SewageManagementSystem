<?php

namespace App\Http\Controllers\Concerns;

use App\Support\DmsFormDateTime;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

trait FiltersDmsArchiveByDateTime
{
    protected function applyDateTimeRangeFilter(
        Builder $query,
        Request $request,
        string $column,
        string $fromKey = 'datetime_from',
        string $toKey = 'datetime_to',
    ): void {
        if ($request->filled($fromKey)) {
            $from = DmsFormDateTime::parse($request->input($fromKey));
            if ($from) {
                $query->where($column, '>=', $from);
            }
        }

        if ($request->filled($toKey)) {
            $to = DmsFormDateTime::parse($request->input($toKey));
            if ($to) {
                if (strlen($request->input($toKey)) <= 10) {
                    $to = $to->endOfDay();
                }
                $query->where($column, '<=', $to);
            }
        }
    }

    protected function applyWorkOrderDateFilter(Builder $query, Request $request): void
    {
        $column = match ($request->input('date_on', 'created')) {
            'started' => 'record_started_at',
            'completed' => 'record_completed_at',
            default => 'record_created_at',
        };

        $this->applyDateTimeRangeFilter($query, $request, $column);
    }
}
