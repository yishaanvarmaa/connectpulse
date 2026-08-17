<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Campaign extends Model
{
    public const STATUS_DRAFT = 'draft';

    public const STATUS_SCHEDULED = 'scheduled';

    public const STATUS_RUNNING = 'running';

    public const STATUS_PAUSED = 'paused';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_CANCELLED = 'cancelled';

    public const AUDIENCE_ALL_CONTACTS = 'all_contacts';

    public const AUDIENCE_CONTACT_LIST = 'contact_list';

    public const AUDIENCE_TAGS = 'tags';

    public const AUDIENCE_LEADS = 'leads';

    public const AUDIENCE_MANUAL = 'manual';

    public const AUDIENCE_CSV = 'csv';

    protected $fillable = [
        'organization_id',
        'created_by',
        'name',
        'message_body',
        'media_path',
        'media_type',
        'audience_type',
        'audience_config',
        'status',
        'pause_reason',
        'scheduled_at',
        'started_at',
        'completed_at',
        'total_recipients',
        'sent_count',
        'delivered_count',
        'failed_count',
        'replied_count',
        'delay_min_seconds',
        'delay_max_seconds',
        'test_confirmed',
        'test_phone',
        'credits_used',
    ];

    protected function casts(): array
    {
        return [
            'audience_config' => 'array',
            'scheduled_at' => 'datetime',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'test_confirmed' => 'boolean',
            'total_recipients' => 'integer',
            'sent_count' => 'integer',
            'delivered_count' => 'integer',
            'failed_count' => 'integer',
            'replied_count' => 'integer',
            'delay_min_seconds' => 'integer',
            'delay_max_seconds' => 'integer',
            'credits_used' => 'integer',
        ];
    }

    public static function statuses(): array
    {
        return [
            self::STATUS_DRAFT => 'Draft',
            self::STATUS_SCHEDULED => 'Scheduled',
            self::STATUS_RUNNING => 'Running',
            self::STATUS_PAUSED => 'Paused',
            self::STATUS_COMPLETED => 'Completed',
            self::STATUS_CANCELLED => 'Cancelled',
        ];
    }

    public static function audienceTypes(): array
    {
        return [
            self::AUDIENCE_ALL_CONTACTS => 'All contacts',
            self::AUDIENCE_CONTACT_LIST => 'Contact list',
            self::AUDIENCE_TAGS => 'Tags',
            self::AUDIENCE_LEADS => 'Leads',
            self::AUDIENCE_MANUAL => 'Manual selection',
            self::AUDIENCE_CSV => 'CSV import',
        ];
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function recipients(): HasMany
    {
        return $this->hasMany(CampaignRecipient::class);
    }

    public function messageLogs(): HasMany
    {
        return $this->hasMany(MessageLog::class);
    }

    public function scopeForOrganization(Builder $query, Organization $organization): Builder
    {
        return $query->where('organization_id', $organization->id);
    }

    public function isActive(): bool
    {
        return in_array($this->status, [self::STATUS_RUNNING, self::STATUS_SCHEDULED], true);
    }

    public function canLaunch(): bool
    {
        return in_array($this->status, [self::STATUS_DRAFT, self::STATUS_SCHEDULED, self::STATUS_PAUSED], true)
            && $this->test_confirmed;
    }

    public function progressPercent(): int
    {
        if ($this->total_recipients <= 0) {
            return 0;
        }

        $processed = $this->sent_count + $this->failed_count;

        return (int) min(100, round(($processed / $this->total_recipients) * 100));
    }

    public function pendingCount(): int
    {
        return max(0, $this->total_recipients - $this->sent_count - $this->failed_count);
    }

    public function statusLabel(): string
    {
        return self::statuses()[$this->status] ?? ucfirst($this->status);
    }
}
