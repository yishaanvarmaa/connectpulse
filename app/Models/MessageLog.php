<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MessageLog extends Model
{
    public const STATUS_QUEUED = 'queued';

    public const STATUS_SENT = 'sent';

    public const STATUS_FAILED = 'failed';

    protected $fillable = [
        'organization_id',
        'mobile',
        'message',
        'status',
        'credits_used',
        'message_id',
        'error_message',
        'batch_id',
        'sent_at',
    ];

    protected function casts(): array
    {
        return [
            'credits_used' => 'integer',
            'sent_at' => 'datetime',
        ];
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }
}
