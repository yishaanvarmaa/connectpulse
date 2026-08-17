<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CampaignRecipient extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_QUEUED = 'queued';

    public const STATUS_SENDING = 'sending';

    public const STATUS_SENT = 'sent';

    public const STATUS_DELIVERED = 'delivered';

    public const STATUS_FAILED = 'failed';

    public const STATUS_SKIPPED = 'skipped';

    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'campaign_id',
        'contact_id',
        'lead_id',
        'phone',
        'name',
        'rendered_message',
        'status',
        'attempts',
        'message_log_id',
        'provider_message_id',
        'queued_at',
        'sent_at',
        'failed_at',
        'failure_reason',
        'next_attempt_at',
    ];

    protected function casts(): array
    {
        return [
            'attempts' => 'integer',
            'queued_at' => 'datetime',
            'sent_at' => 'datetime',
            'failed_at' => 'datetime',
            'next_attempt_at' => 'datetime',
        ];
    }

    public static function statuses(): array
    {
        return [
            self::STATUS_PENDING => 'Pending',
            self::STATUS_QUEUED => 'Queued',
            self::STATUS_SENDING => 'Sending',
            self::STATUS_SENT => 'Sent',
            self::STATUS_DELIVERED => 'Delivered',
            self::STATUS_FAILED => 'Failed',
            self::STATUS_SKIPPED => 'Skipped',
            self::STATUS_CANCELLED => 'Cancelled',
        ];
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function messageLog(): BelongsTo
    {
        return $this->belongsTo(MessageLog::class);
    }

    public function isTerminal(): bool
    {
        return in_array($this->status, [
            self::STATUS_SENT,
            self::STATUS_DELIVERED,
            self::STATUS_FAILED,
            self::STATUS_SKIPPED,
            self::STATUS_CANCELLED,
        ], true);
    }

    public function canRetry(): bool
    {
        return $this->status === self::STATUS_FAILED
            && $this->attempts < config('connectpulse.campaign_max_retries', 3)
            && ! $this->isPermanentFailure();
    }

    public function isPermanentFailure(): bool
    {
        if (! $this->failure_reason) {
            return false;
        }

        $reason = strtolower($this->failure_reason);

        return str_contains($reason, 'invalid')
            || str_contains($reason, 'malformed')
            || str_contains($reason, 'not a valid');
    }

    public function statusLabel(): string
    {
        return self::statuses()[$this->status] ?? ucfirst($this->status);
    }
}
