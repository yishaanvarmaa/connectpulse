<?php

namespace App\Services;

use App\Models\Lead;
use App\Models\LeadActivity;
use App\Models\User;

class LeadActivityService
{
    public function record(
        Lead $lead,
        string $type,
        string $title,
        ?User $user = null,
        ?array $metadata = null,
        ?string $description = null,
    ): LeadActivity {
        return LeadActivity::create([
            'lead_id' => $lead->id,
            'organization_id' => $lead->organization_id,
            'user_id' => $user?->id,
            'type' => $type,
            'title' => $title,
            'description' => $description,
            'metadata' => $metadata,
        ]);
    }

    public function getTimeline(Lead $lead, int $limit = 50)
    {
        return $lead->activities()
            ->with('user')
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }
}
