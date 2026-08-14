<?php

namespace App\Contracts;

use App\Models\LeadFollowUp;
use App\Models\User;

interface ReminderNotifierInterface
{
    public function notify(User $user, LeadFollowUp $followUp, string $context): void;
}
