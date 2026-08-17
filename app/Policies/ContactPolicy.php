<?php

namespace App\Policies;

use App\Models\Contact;
use App\Models\User;

class ContactPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isOrganizationAdmin();
    }

    public function manage(User $user, Contact $contact): bool
    {
        return $user->isOrganizationAdmin()
            && $user->organization_id === $contact->organization_id;
    }
}
