<?php

namespace App\Policies;

use App\Models\Lead;
use App\Models\LeadFollowUp;
use App\Models\User;

class LeadPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isOrganizationAdmin() && $user->organization_id !== null;
    }

    public function view(User $user, Lead $lead): bool
    {
        return $this->belongsToUserOrganization($user, $lead->organization_id);
    }

    public function create(User $user): bool
    {
        return $user->isOrganizationAdmin() && $user->organization_id !== null;
    }

    public function update(User $user, Lead $lead): bool
    {
        return $this->belongsToUserOrganization($user, $lead->organization_id);
    }

    public function delete(User $user, Lead $lead): bool
    {
        return $this->belongsToUserOrganization($user, $lead->organization_id);
    }

    public function manageFollowUp(User $user, LeadFollowUp $followUp): bool
    {
        return $this->belongsToUserOrganization($user, $followUp->organization_id);
    }

    private function belongsToUserOrganization(User $user, int $organizationId): bool
    {
        return $user->isOrganizationAdmin() && $user->organization_id === $organizationId;
    }
}
