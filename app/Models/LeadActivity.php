<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeadActivity extends Model
{
    public const TYPE_CREATED = 'created';

    public const TYPE_STATUS_CHANGED = 'status_changed';

    public const TYPE_NOTE_ADDED = 'note_added';

    public const TYPE_FOLLOW_UP_SCHEDULED = 'follow_up_scheduled';

    public const TYPE_FOLLOW_UP_COMPLETED = 'follow_up_completed';

    public const TYPE_FOLLOW_UP_CANCELLED = 'follow_up_cancelled';

    public const TYPE_WHATSAPP_SENT = 'whatsapp_sent';

    public const TYPE_CALL_COMPLETED = 'call_completed';

    public const TYPE_MARKED_WON = 'marked_won';

    public const TYPE_MARKED_LOST = 'marked_lost';

    public const TYPE_UPDATED = 'updated';

    protected $fillable = [
        'lead_id',
        'organization_id',
        'user_id',
        'type',
        'title',
        'description',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
        ];
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeForOrganization(Builder $query, Organization $organization): Builder
    {
        return $query->where('organization_id', $organization->id);
    }
}
