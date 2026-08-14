<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeadFollowUp extends Model
{
    public const TYPE_CALL = 'call';

    public const TYPE_WHATSAPP = 'whatsapp';

    public const TYPE_MEETING = 'meeting';

    public const TYPE_DEMO = 'demo';

    public const TYPE_OTHER = 'other';

    public const STATUS_PENDING = 'pending';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'lead_id',
        'organization_id',
        'created_by',
        'scheduled_at',
        'type',
        'notes',
        'status',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public static function types(): array
    {
        return [
            self::TYPE_CALL => 'Call',
            self::TYPE_WHATSAPP => 'WhatsApp',
            self::TYPE_MEETING => 'Meeting',
            self::TYPE_DEMO => 'Demo',
            self::TYPE_OTHER => 'Other',
        ];
    }

    public static function statuses(): array
    {
        return [
            self::STATUS_PENDING => 'Pending',
            self::STATUS_COMPLETED => 'Completed',
            self::STATUS_CANCELLED => 'Cancelled',
        ];
    }

    public function typeLabel(): string
    {
        return self::types()[$this->type] ?? ucfirst($this->type);
    }

    public function isOverdue(): bool
    {
        return $this->status === self::STATUS_PENDING && $this->scheduled_at->isPast();
    }

    public function displayStatus(): string
    {
        if ($this->status === self::STATUS_PENDING && $this->scheduled_at->isPast()) {
            return 'Overdue';
        }

        return self::statuses()[$this->status] ?? ucfirst($this->status);
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeForOrganization(Builder $query, Organization $organization): Builder
    {
        return $query->where('organization_id', $organization->id);
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeOverdue(Builder $query): Builder
    {
        return $query->pending()->where('scheduled_at', '<', now());
    }

    public function scopeDueToday(Builder $query): Builder
    {
        return $query->pending()
            ->whereBetween('scheduled_at', [now()->startOfDay(), now()->endOfDay()]);
    }

    public function scopeUpcoming(Builder $query): Builder
    {
        return $query->pending()->where('scheduled_at', '>', now()->endOfDay());
    }
}
