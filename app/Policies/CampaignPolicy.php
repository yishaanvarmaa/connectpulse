<?php

namespace App\Policies;

use App\Models\Campaign;
use App\Models\Contact;
use App\Models\ContactList;
use App\Models\ContactTag;
use App\Models\User;

class CampaignPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isOrganizationAdmin();
    }

    public function view(User $user, Campaign $campaign): bool
    {
        return $user->isOrganizationAdmin()
            && $user->organization_id === $campaign->organization_id;
    }

    public function create(User $user): bool
    {
        return $user->isOrganizationAdmin();
    }

    public function update(User $user, Campaign $campaign): bool
    {
        return $user->isOrganizationAdmin()
            && $user->organization_id === $campaign->organization_id;
    }

    public function delete(User $user, Campaign $campaign): bool
    {
        return $this->update($user, $campaign) && $campaign->canBeDeleted();
    }

    public function manage(User $user, Campaign $campaign): bool
    {
        return $this->update($user, $campaign);
    }
}
