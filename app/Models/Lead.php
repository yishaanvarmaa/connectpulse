<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Lead extends Model
{
    public const STATUS_NEW = 'new';

    public const STATUS_CONTACTED = 'contacted';

    public const STATUS_INTERESTED = 'interested';

    public const STATUS_FOLLOW_UP = 'follow_up';

    public const STATUS_DEMO_SCHEDULED = 'demo_scheduled';

    public const STATUS_NEGOTIATION = 'negotiation';

    public const STATUS_WON = 'won';

    public const STATUS_LOST = 'lost';

    public const PRIORITY_LOW = 'low';

    public const PRIORITY_MEDIUM = 'medium';

    public const PRIORITY_HIGH = 'high';

    public const SOURCE_FACEBOOK = 'facebook';

    public const SOURCE_INSTAGRAM = 'instagram';

    public const SOURCE_WHATSAPP = 'whatsapp';

    public const SOURCE_WEBSITE = 'website';

    public const SOURCE_REFERRAL = 'referral';

    public const SOURCE_GOOGLE = 'google';

    public const SOURCE_MANUAL = 'manual';

    public const SOURCE_OTHER = 'other';

    public const TEMP_HOT = 'hot';

    public const TEMP_WARM = 'warm';

    public const TEMP_COLD = 'cold';

    public const TEMP_NEW = 'new';

    protected $fillable = [
        'organization_id',
        'created_by',
        'name',
        'phone',
        'email',
        'company',
        'designation',
        'source',
        'interested_product',
        'estimated_value',
        'status',
        'priority',
        'temperature',
        'notes',
        'last_contacted_at',
        'next_follow_up_at',
        'converted_at',
        'lost_reason',
        'automation_enabled',
    ];

    protected function casts(): array
    {
        return [
            'estimated_value' => 'decimal:2',
            'last_contacted_at' => 'datetime',
            'next_follow_up_at' => 'datetime',
            'converted_at' => 'datetime',
            'automation_enabled' => 'boolean',
        ];
    }

    public static function statuses(): array
    {
        return [
            self::STATUS_NEW => 'New',
            self::STATUS_CONTACTED => 'Contacted',
            self::STATUS_INTERESTED => 'Interested',
            self::STATUS_FOLLOW_UP => 'Follow-up',
            self::STATUS_DEMO_SCHEDULED => 'Demo Scheduled',
            self::STATUS_NEGOTIATION => 'Negotiation',
            self::STATUS_WON => 'Won',
            self::STATUS_LOST => 'Lost',
        ];
    }

    public static function priorities(): array
    {
        return [
            self::PRIORITY_LOW => 'Low',
            self::PRIORITY_MEDIUM => 'Medium',
            self::PRIORITY_HIGH => 'High',
        ];
    }

    public static function sources(): array
    {
        return [
            self::SOURCE_FACEBOOK => 'Facebook',
            self::SOURCE_INSTAGRAM => 'Instagram',
            self::SOURCE_WHATSAPP => 'WhatsApp',
            self::SOURCE_WEBSITE => 'Website',
            self::SOURCE_REFERRAL => 'Referral',
            self::SOURCE_GOOGLE => 'Google',
            self::SOURCE_MANUAL => 'Manual',
            self::SOURCE_OTHER => 'Other',
        ];
    }

    public static function temperatures(): array
    {
        return [
            self::TEMP_HOT => 'Hot',
            self::TEMP_WARM => 'Warm',
            self::TEMP_COLD => 'Cold',
            self::TEMP_NEW => 'New',
        ];
    }

    public static function quickFilters(): array
    {
        return [
            'all' => 'All',
            'new' => 'New',
            'needs_follow_up' => 'Needs Follow-up',
            'overdue' => 'Overdue',
            'hot' => 'Hot',
            'won' => 'Won',
            'lost' => 'Lost',
        ];
    }

    public static function openStatuses(): array
    {
        return [
            self::STATUS_NEW,
            self::STATUS_CONTACTED,
            self::STATUS_INTERESTED,
            self::STATUS_FOLLOW_UP,
            self::STATUS_DEMO_SCHEDULED,
            self::STATUS_NEGOTIATION,
        ];
    }

    public function statusLabel(): string
    {
        return self::statuses()[$this->status] ?? ucfirst(str_replace('_', ' ', $this->status));
    }

    public function sourceLabel(): string
    {
        return self::sources()[$this->source] ?? ucfirst($this->source);
    }

    public function priorityLabel(): string
    {
        return self::priorities()[$this->priority] ?? ucfirst($this->priority);
    }

    public function temperatureLabel(): string
    {
        return self::temperatures()[$this->temperature ?? self::TEMP_NEW] ?? 'New';
    }

    public function temperatureIcon(): string
    {
        return match ($this->temperature ?? self::TEMP_NEW) {
            self::TEMP_HOT => '🔥',
            self::TEMP_WARM => '🟡',
            self::TEMP_COLD => '⚪',
            default => '🔵',
        };
    }

    public function isHot(): bool
    {
        return ($this->temperature ?? self::TEMP_NEW) === self::TEMP_HOT
            || $this->priority === self::PRIORITY_HIGH;
    }

    public function isClosed(): bool
    {
        return in_array($this->status, [self::STATUS_WON, self::STATUS_LOST], true);
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function followUps(): HasMany
    {
        return $this->hasMany(LeadFollowUp::class);
    }

    public function activities(): HasMany
    {
        return $this->hasMany(LeadActivity::class);
    }

    public function messageLogs(): HasMany
    {
        return $this->hasMany(MessageLog::class);
    }

    public function automationConfig(): HasOne
    {
        return $this->hasOne(LeadAutomationConfig::class);
    }

    public function scopeForOrganization(Builder $query, Organization $organization): Builder
    {
        return $query->where('organization_id', $organization->id);
    }

    public function normalizedPhone(): string
    {
        return preg_replace('/[^0-9]/', '', (string) ($this->phone ?? '')) ?: '';
    }
}
