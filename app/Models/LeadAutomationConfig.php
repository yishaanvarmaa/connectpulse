<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeadAutomationConfig extends Model
{
    protected $fillable = [
        'lead_id',
        'organization_id',
        'is_active',
        'steps',
        'current_step',
        'paused_at',
        'pause_reason',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'steps' => 'array',
            'paused_at' => 'datetime',
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
}
