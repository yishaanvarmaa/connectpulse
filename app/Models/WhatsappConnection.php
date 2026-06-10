<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WhatsappConnection extends Model
{
    public const STATUS_CONNECTED = 'connected';

    public const STATUS_DISCONNECTED = 'disconnected';

    public const STATUS_QR_REQUIRED = 'qr_required';

    public const STATUS_RECONNECTING = 'reconnecting';

    protected $fillable = [
        'organization_id',
        'status',
        'phone_number',
        'session_path',
        'connected_at',
        'disconnected_at',
    ];

    protected function casts(): array
    {
        return [
            'connected_at' => 'datetime',
            'disconnected_at' => 'datetime',
        ];
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function isConnected(): bool
    {
        return $this->status === self::STATUS_CONNECTED;
    }

    public function getClientStatus(): string
    {
        return match ($this->status) {
            self::STATUS_CONNECTED => 'Connected',
            self::STATUS_QR_REQUIRED => 'Reconnect Required',
            self::STATUS_RECONNECTING => 'Reconnect Required',
            default => 'Disconnected',
        };
    }
}
