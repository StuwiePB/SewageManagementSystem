<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Report extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference_code',
        'user_id',
        'phone',
        'reporter_name',
        'problem_type',
        'severity',
        'description',
        'address',
        'latitude',
        'longitude',
        'photo_path',
        'drainage_ai_verdict',
        'status',
    ];

    public const STATUS_PENDING = 'pending';

    public static function generateReferenceCode(): string
    {
        $md = now()->format('md');
        $yy = now()->format('y');

        // Keep trying until unique to avoid collisions.
        do {
            $rand = str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT);
            $reference = "FR SAL/{$md}/{$yy}({$rand})";
        } while (static::where('reference_code', $reference)->exists());

        return $reference;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Linked ops row after an admin sends this report to operations.
     */
    public function operationsReport(): HasOne
    {
        return $this->hasOne(OperationsReport::class, 'customer_report_id');
    }
}
