<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DmsArchiveWorkOrderPhoto extends Model
{
    protected $fillable = [
        'dms_archive_work_order_id',
        'path',
        'photo_type',
        'original_name',
    ];

    public function workOrder(): BelongsTo
    {
        return $this->belongsTo(DmsArchiveWorkOrder::class, 'dms_archive_work_order_id');
    }
}
