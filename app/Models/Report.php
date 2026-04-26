<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Report extends Model
{
    use HasFactory;
    use SoftDeletes;

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
        'deletion_reason',
        'deletion_notes',
    ];

    public const STATUS_PENDING = 'pending';

    /**
     * Admin-facing keys and labels when removing a customer report from the queue.
     *
     * @return array<string, string>
     */
    public static function deletionReasonOptions(): array
    {
        return [
            'already_handled' => 'Already handled / taken care of elsewhere',
            'duplicate' => 'Duplicate submission',
            'out_of_scope' => 'Outside JKR drainage / sewerage scope',
            'insufficient_detail' => 'Insufficient detail or unclear photo / location',
            'spam_or_invalid' => 'Spam, test, or invalid submission',
            'location_invalid' => 'Location not in Brunei or not verifiable',
        ];
    }

    /**
     * Customer report was soft-deleted by admin with a recorded reason.
     */
    public function wasRejectedByAdmin(): bool
    {
        return $this->trashed() && filled($this->deletion_reason);
    }

    /**
     * Human-readable removal reason for the customer app (same wording as admin options).
     */
    public function customerDeletionReasonLabel(): string
    {
        $opts = static::deletionReasonOptions();

        return $opts[$this->deletion_reason] ?? Str::headline(str_replace('_', ' ', (string) $this->deletion_reason));
    }

    /**
     * Short customer-facing rejection reason.
     */
    public function customerDeletionReasonShortLabel(): string
    {
        return match ((string) $this->deletion_reason) {
            'already_handled' => 'Already handled',
            'duplicate' => 'Duplicate',
            'out_of_scope' => 'Out of scope',
            'insufficient_detail' => 'Insufficient details',
            'spam_or_invalid' => 'Invalid report',
            'location_invalid' => 'Invalid location',
            default => 'Rejected',
        };
    }

    /**
     * Logged-in customer’s history: sent to operations, or rejected (removed) with a reason.
     */
    public static function queryForCustomerHistory(int $userId): Builder
    {
        return static::query()
            ->with(['operationsReport'])
            ->withTrashed()
            ->where('user_id', $userId)
            ->where(function (Builder $q) {
                $q->where(function (Builder $a) {
                    $a->whereNull('reports.deleted_at')
                        ->whereHas('operationsReport');
                })->orWhere(function (Builder $a) {
                    $a->whereNotNull('reports.deleted_at')
                        ->whereNotNull('reports.deletion_reason');
                });
            })
            ->latest();
    }

    /**
     * Customer dashboard: "under review" (own report, not yet sent to operations) first, then newest.
     */
    public function scopeOrderByOwnerUnderReviewFirst(Builder $query, User $user): Builder
    {
        $uid = (int) $user->id;

        return $query
            ->orderByRaw(
                'CASE WHEN reports.user_id = ? AND NOT EXISTS (SELECT 1 FROM operations_reports op WHERE op.customer_report_id = reports.id) THEN 0 ELSE 1 END',
                [$uid]
            )
            ->orderByDesc('reports.created_at');
    }

    /**
     * Admin and maps: not yet sent to operations (intake / under review) first, then newest.
     */
    public function scopeOrderByUnsentToOperationsFirst(Builder $query): Builder
    {
        return $query
            ->orderByRaw(
                'CASE WHEN NOT EXISTS (SELECT 1 FROM operations_reports op WHERE op.customer_report_id = reports.id) THEN 0 ELSE 1 END'
            )
            ->orderByDesc('reports.created_at');
    }

    /**
     * Dashboard strip: community reports (with ops) + my drafts.
     */
    public static function queryForCustomerDashboard(User $user): Builder
    {
        return static::query()
            ->with(['user', 'operationsReport'])
            ->whereNull('reports.deleted_at')
            ->where(function (Builder $q) use ($user) {
                $q->whereHas('operationsReport')
                    ->orWhere('reports.user_id', $user->id);
            })
            ->orderByOwnerUnderReviewFirst($user);
    }

    public static function generateReferenceCode(): string
    {
        $md = now()->format('md');
        $yy = now()->format('y');

        // Keep trying until unique to avoid collisions.
        do {
            $rand = str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT);
            $reference = "FR SAL/{$md}/{$yy}({$rand})";
        } while (static::withTrashed()->where('reference_code', $reference)->exists());

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
