<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Schema;
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
        'description',
        'address',
        'latitude',
        'longitude',
        'h3_index',
        'photo_path',
        'drainage_ai_verdict',
        'status',
        'deletion_reason',
        'deletion_notes',
    ];

    public const STATUS_PENDING = 'pending';

    /**
     * Problem types that warrant immediate SNS / high-priority handling (severity column removed).
     */
    public function isHighPriorityProblemType(): bool
    {
        $type = strtolower(str_replace([' ', '-'], '_', trim((string) $this->problem_type)));

        return in_array($type, ['overflow', 'blockage'], true);
    }

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
     * Statuses counted on the community statistics page (not under_review — that is owner-only on the dashboard).
     *
     * @return list<string>
     */
    public static function customerStatisticsStatuses(): array
    {
        return ['pending', 'in_progress', 'resolved'];
    }

    /**
     * Logged-in customer’s history: accepted into operations only (no rejections or cancellations).
     */
    public static function queryForCustomerHistory(int $userId): Builder
    {
        return static::query()
            ->with(['operationsReport'])
            ->where('user_id', $userId)
            ->whereNull('reports.deleted_at')
            ->where('reports.status', '!=', 'cancelled')
            ->whereHas('operationsReport')
            ->latest();
    }

    /**
     * Customer statistics page: all reporters’ ops-linked reports; excludes cancelled and admin-rejected.
     */
    public static function queryForCustomerStatistics(): Builder
    {
        return static::query()
            ->whereNull('reports.deleted_at')
            ->whereIn('reports.status', static::customerStatisticsStatuses())
            ->whereHas('operationsReport');
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
            ->where('reports.status', '!=', 'cancelled')
            ->where(function (Builder $q) use ($user) {
                $q->whereHas('operationsReport')
                    ->orWhere('reports.user_id', $user->id);
            })
            ->orderByOwnerUnderReviewFirst($user);
    }

    public static function isFrSalReference(?string $code): bool
    {
        return is_string($code) && str_starts_with($code, 'FR SAL/');
    }

    public static function isLegacyReference(?string $code): bool
    {
        if (! is_string($code) || $code === '') {
            return true;
        }

        if (static::isFrSalReference($code)) {
            return false;
        }

        return str_starts_with($code, 'RPT-')
            || str_starts_with($code, 'WO-')
            || str_starts_with($code, 'WO-ARC-');
    }

    public static function referenceCodeExists(string $code): bool
    {
        if (static::withTrashed()->where('reference_code', $code)->exists()) {
            return true;
        }

        if (OperationsReport::query()->where('report_number', $code)->exists()) {
            return true;
        }

        if (WorkOrder::query()->withoutGlobalScopes()->where('work_order_number', $code)->exists()) {
            return true;
        }

        if (class_exists(DmsArchiveWorkOrder::class)
            && Schema::hasTable('dms_archive_work_orders')
            && DmsArchiveWorkOrder::query()->where('work_order_number', $code)->exists()) {
            return true;
        }

        return false;
    }

    public static function generateReferenceCode(): string
    {
        $md = now()->format('md');
        $yy = now()->format('y');

        do {
            $rand = str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT);
            $reference = "FR SAL/{$md}/{$yy}({$rand})";
        } while (static::referenceCodeExists($reference));

        return $reference;
    }

    /**
     * Customer-facing reference used across ops reports and work orders for this report.
     */
    public function ensureReferenceCode(): string
    {
        if (filled($this->reference_code)) {
            return (string) $this->reference_code;
        }

        $code = static::generateReferenceCode();
        $this->forceFill(['reference_code' => $code])->save();

        return $code;
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
