<?php

namespace App\Services;

use App\Models\Lead;
use App\Models\LeadActivity;
use App\Models\LeadFollowUp;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class FollowUpService
{
    public function __construct(
        private LeadActivityService $activityService,
    ) {}

    public function create(Lead $lead, User $user, array $data): LeadFollowUp
    {
        return DB::transaction(function () use ($lead, $user, $data) {
            $followUp = LeadFollowUp::create([
                'lead_id' => $lead->id,
                'organization_id' => $lead->organization_id,
                'created_by' => $user->id,
                'scheduled_at' => $data['scheduled_at'],
                'type' => $data['type'] ?? LeadFollowUp::TYPE_CALL,
                'notes' => $data['notes'] ?? null,
                'status' => LeadFollowUp::STATUS_PENDING,
            ]);

            $lead->update(['next_follow_up_at' => $followUp->scheduled_at]);

            $this->activityService->record(
                $lead,
                LeadActivity::TYPE_FOLLOW_UP_SCHEDULED,
                'Follow-up scheduled for '.$followUp->scheduled_at->format('d M, h:i A'),
                $user,
                ['follow_up_id' => $followUp->id, 'type' => $followUp->type]
            );

            return $followUp;
        });
    }

    public function reschedule(LeadFollowUp $followUp, User $user, $scheduledAt): LeadFollowUp
    {
        return DB::transaction(function () use ($followUp, $user, $scheduledAt) {
            $followUp->update([
                'scheduled_at' => $scheduledAt,
                'status' => LeadFollowUp::STATUS_PENDING,
                'completed_at' => null,
            ]);

            $followUp->lead->update(['next_follow_up_at' => $scheduledAt]);

            $this->activityService->record(
                $followUp->lead,
                LeadActivity::TYPE_FOLLOW_UP_SCHEDULED,
                'Follow-up rescheduled to '.$followUp->scheduled_at->format('d M, h:i A'),
                $user,
                ['follow_up_id' => $followUp->id]
            );

            return $followUp->fresh();
        });
    }

    public function complete(LeadFollowUp $followUp, User $user, ?string $notes = null): LeadFollowUp
    {
        return DB::transaction(function () use ($followUp, $user, $notes) {
            $followUp->update([
                'status' => LeadFollowUp::STATUS_COMPLETED,
                'completed_at' => now(),
                'notes' => $notes ?? $followUp->notes,
            ]);

            $lead = $followUp->lead;
            $lead->update(['last_contacted_at' => now()]);

            $nextPending = $lead->followUps()
                ->pending()
                ->where('id', '!=', $followUp->id)
                ->orderBy('scheduled_at')
                ->first();

            $lead->update(['next_follow_up_at' => $nextPending?->scheduled_at]);

            $activityType = $followUp->type === LeadFollowUp::TYPE_CALL
                ? LeadActivity::TYPE_CALL_COMPLETED
                : LeadActivity::TYPE_FOLLOW_UP_COMPLETED;

            $this->activityService->record(
                $lead,
                $activityType,
                ucfirst($followUp->type).' follow-up completed',
                $user,
                ['follow_up_id' => $followUp->id]
            );

            return $followUp->fresh();
        });
    }

    public function cancel(LeadFollowUp $followUp, User $user): LeadFollowUp
    {
        return DB::transaction(function () use ($followUp, $user) {
            $followUp->update(['status' => LeadFollowUp::STATUS_CANCELLED]);

            $lead = $followUp->lead;
            $nextPending = $lead->followUps()->pending()->orderBy('scheduled_at')->first();
            $lead->update(['next_follow_up_at' => $nextPending?->scheduled_at]);

            $this->activityService->record(
                $lead,
                LeadActivity::TYPE_FOLLOW_UP_CANCELLED,
                'Follow-up cancelled',
                $user,
                ['follow_up_id' => $followUp->id]
            );

            return $followUp->fresh();
        });
    }

    public function getDashboardGroups(Organization $organization): array
    {
        $baseQuery = LeadFollowUp::forOrganization($organization)
            ->with('lead')
            ->pending();

        return [
            'overdue' => (clone $baseQuery)->overdue()->orderBy('scheduled_at')->get(),
            'today' => (clone $baseQuery)->dueToday()->orderBy('scheduled_at')->get(),
            'upcoming' => (clone $baseQuery)->upcoming()->orderBy('scheduled_at')->limit(50)->get(),
            'no_follow_up' => Lead::forOrganization($organization)
                ->whereIn('status', Lead::openStatuses())
                ->whereNull('next_follow_up_at')
                ->orderByDesc('created_at')
                ->limit(50)
                ->get(),
        ];
    }

    public function findForOrganization(Organization $organization, int $followUpId): LeadFollowUp
    {
        return LeadFollowUp::forOrganization($organization)->findOrFail($followUpId);
    }
}
