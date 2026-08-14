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

    public function getCalendarGroups(Organization $organization, int $days = 14): array
    {
        $start = now()->startOfDay();
        $end = now()->addDays($days)->endOfDay();

        $followUps = LeadFollowUp::forOrganization($organization)
            ->with('lead')
            ->pending()
            ->whereBetween('scheduled_at', [$start, $end])
            ->orderBy('scheduled_at')
            ->get()
            ->groupBy(fn (LeadFollowUp $f) => $f->scheduled_at->format('Y-m-d'));

        $calendar = [];
        for ($i = 0; $i <= $days; $i++) {
            $date = now()->addDays($i)->startOfDay();
            $key = $date->format('Y-m-d');
            $calendar[$key] = [
                'date' => $date,
                'label' => $date->isToday() ? 'Today' : ($date->isTomorrow() ? 'Tomorrow' : $date->format('D, M d')),
                'items' => $followUps->get($key, collect()),
            ];
        }

        return $calendar;
    }

    public function findForOrganization(Organization $organization, int $followUpId): LeadFollowUp
    {
        return LeadFollowUp::forOrganization($organization)->findOrFail($followUpId);
    }

    public function logInteraction(Lead $lead, User $user, array $data, ?LeadFollowUp $followUp = null): LeadActivity
    {
        return DB::transaction(function () use ($lead, $user, $data, $followUp) {
            if (! empty($data['result'])) {
                $mapped = $this->mapInteractionResult($data['result']);
                $data['outcome'] = $mapped['outcome'];
                $data['lead_status'] = $mapped['status'];
            }

            $outcome = $data['outcome'];
            $notes = trim($data['notes'] ?? '');
            $outcomeLabel = LeadActivity::outcomes()[$outcome] ?? ucfirst(str_replace('_', ' ', $outcome));

            if (! $followUp) {
                $followUp = $lead->followUps()->pending()->overdue()->orderBy('scheduled_at')->first()
                    ?? $lead->followUps()->pending()->dueToday()->orderBy('scheduled_at')->first()
                    ?? $lead->followUps()->pending()->orderBy('scheduled_at')->first();
            }

            if ($followUp && $followUp->lead_id === $lead->id && $followUp->status === LeadFollowUp::STATUS_PENDING) {
                $completionNotes = $outcomeLabel;
                if ($notes !== '') {
                    $completionNotes .= "\n".$notes;
                }

                $followUp->update([
                    'status' => LeadFollowUp::STATUS_COMPLETED,
                    'completed_at' => now(),
                    'notes' => $completionNotes,
                ]);
            }

            $lead->update(['last_contacted_at' => now()]);

            if (! empty($data['lead_status']) && in_array($data['lead_status'], array_keys(Lead::statuses()), true)) {
                $lead->update(['status' => $data['lead_status']]);
                if ($data['lead_status'] === Lead::STATUS_WON) {
                    $lead->update(['converted_at' => now()]);
                }
            } elseif ($lead->status === Lead::STATUS_NEW) {
                $lead->update(['status' => Lead::STATUS_CONTACTED]);
            }

            $activity = $this->activityService->record(
                $lead,
                LeadActivity::TYPE_INTERACTION_LOGGED,
                $outcomeLabel,
                $user,
                ['outcome' => $outcome, 'follow_up_id' => $followUp?->id],
                $notes !== '' ? $notes : null,
            );

            if (! empty($data['next_scheduled_at'])) {
                $this->create($lead, $user, [
                    'scheduled_at' => $data['next_scheduled_at'],
                    'type' => $data['next_type'] ?? LeadFollowUp::TYPE_CALL,
                    'notes' => $data['next_notes'] ?? null,
                ]);
            } else {
                $nextPending = $lead->followUps()->pending()->orderBy('scheduled_at')->first();
                $lead->update(['next_follow_up_at' => $nextPending?->scheduled_at]);
            }

            return $activity;
        });
    }

    /** @return array{outcome: string, status: ?string} */
    private function mapInteractionResult(string $result): array
    {
        return match ($result) {
            'interested' => ['outcome' => LeadActivity::OUTCOME_SPOKE_INTERESTED, 'status' => Lead::STATUS_INTERESTED],
            'needs_time' => ['outcome' => LeadActivity::OUTCOME_CALLBACK_REQUESTED, 'status' => null],
            'demo' => ['outcome' => LeadActivity::OUTCOME_MEETING_DONE, 'status' => Lead::STATUS_DEMO_SCHEDULED],
            'negotiation' => ['outcome' => LeadActivity::OUTCOME_SPOKE_INTERESTED, 'status' => Lead::STATUS_NEGOTIATION],
            'won' => ['outcome' => LeadActivity::OUTCOME_SPOKE_INTERESTED, 'status' => Lead::STATUS_WON],
            'lost' => ['outcome' => LeadActivity::OUTCOME_SPOKE_NOT_INTERESTED, 'status' => Lead::STATUS_LOST],
            'no_response' => ['outcome' => LeadActivity::OUTCOME_NO_ANSWER, 'status' => null],
            default => ['outcome' => LeadActivity::OUTCOME_OTHER, 'status' => null],
        };
    }
}
