<?php

namespace App\Services;

use App\Contracts\ReminderNotifierInterface;
use App\Models\LeadFollowUp;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class ReminderService
{
    /** @var ReminderNotifierInterface[] */
    private array $notifiers = [];

    public function registerNotifier(ReminderNotifierInterface $notifier): void
    {
        $this->notifiers[] = $notifier;
    }

    public function getDueReminders(Organization $organization): array
    {
        return [
            'overdue' => LeadFollowUp::forOrganization($organization)->overdue()->with('lead')->get(),
            'today' => LeadFollowUp::forOrganization($organization)->dueToday()->with('lead')->get(),
            'upcoming' => LeadFollowUp::forOrganization($organization)->upcoming()->with('lead')->limit(20)->get(),
        ];
    }

    public function dispatchReminders(User $user, Organization $organization): int
    {
        $groups = $this->getDueReminders($organization);
        $sent = 0;

        foreach (['overdue', 'today'] as $context) {
            /** @var Collection<int, LeadFollowUp> $items */
            $items = $groups[$context];
            foreach ($items as $followUp) {
                foreach ($this->notifiers as $notifier) {
                    $notifier->notify($user, $followUp, $context);
                }
                $sent++;
            }
        }

        if ($sent > 0) {
            Log::info('CRM reminders dispatched', [
                'organization_id' => $organization->id,
                'count' => $sent,
            ]);
        }

        return $sent;
    }
}
