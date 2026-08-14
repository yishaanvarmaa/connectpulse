<?php

namespace App\Services;

use App\Models\Lead;
use App\Models\LeadActivity;
use App\Models\LeadAutomationConfig;
use App\Models\LeadFollowUp;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class LeadService
{
    public function __construct(
        private LeadActivityService $activityService,
    ) {}

    public function create(Organization $organization, User $user, array $data): Lead
    {
        return DB::transaction(function () use ($organization, $user, $data) {
            $lead = Lead::create([
                'organization_id' => $organization->id,
                'created_by' => $user->id,
                'name' => $data['name'],
                'phone' => $data['phone'],
                'email' => $data['email'] ?? null,
                'company' => $data['company'] ?? null,
                'designation' => $data['designation'] ?? null,
                'source' => $data['source'] ?? Lead::SOURCE_MANUAL,
                'interested_product' => $data['interested_product'] ?? null,
                'estimated_value' => $data['estimated_value'] ?? null,
                'status' => $data['status'] ?? Lead::STATUS_NEW,
                'priority' => $data['priority'] ?? Lead::PRIORITY_MEDIUM,
                'temperature' => $data['temperature'] ?? Lead::TEMP_NEW,
                'notes' => $data['notes'] ?? null,
                'next_follow_up_at' => $data['next_follow_up_at'] ?? null,
            ]);

            LeadAutomationConfig::create([
                'lead_id' => $lead->id,
                'organization_id' => $organization->id,
                'is_active' => false,
                'steps' => [],
            ]);

            $this->activityService->record(
                $lead,
                LeadActivity::TYPE_CREATED,
                'Lead created from '.$lead->sourceLabel(),
                $user,
                ['source' => $lead->source]
            );

            if (! empty($data['next_follow_up_at'])) {
                $this->scheduleInitialFollowUp($lead, $user, $data);
            }

            return $lead->fresh(['followUps', 'activities']);
        });
    }

    private function scheduleInitialFollowUp(Lead $lead, User $user, array $data): void
    {
        $followUp = LeadFollowUp::create([
            'lead_id' => $lead->id,
            'organization_id' => $lead->organization_id,
            'created_by' => $user->id,
            'scheduled_at' => $data['next_follow_up_at'],
            'type' => $data['follow_up_type'] ?? LeadFollowUp::TYPE_CALL,
            'notes' => $data['follow_up_notes'] ?? null,
            'status' => LeadFollowUp::STATUS_PENDING,
        ]);

        $this->activityService->record(
            $lead,
            LeadActivity::TYPE_FOLLOW_UP_SCHEDULED,
            'Follow-up scheduled for '.$followUp->scheduled_at->format('d M, h:i A'),
            $user,
            ['follow_up_id' => $followUp->id]
        );
    }

    public function update(Lead $lead, User $user, array $data): Lead
    {
        return DB::transaction(function () use ($lead, $user, $data) {
            $previousStatus = $lead->status;
            $lead->update($data);

            if (isset($data['status']) && $data['status'] !== $previousStatus) {
                $this->activityService->record(
                    $lead,
                    LeadActivity::TYPE_STATUS_CHANGED,
                    'Status changed from '.(Lead::statuses()[$previousStatus] ?? $previousStatus)
                        .' → '.$lead->statusLabel(),
                    $user,
                    ['from' => $previousStatus, 'to' => $lead->status]
                );

                if (in_array($lead->status, [Lead::STATUS_WON, Lead::STATUS_LOST], true)) {
                    $this->pauseAutomation($lead, 'Lead marked as '.$lead->statusLabel());
                }
            } else {
                $this->activityService->record(
                    $lead,
                    LeadActivity::TYPE_UPDATED,
                    'Lead details updated',
                    $user
                );
            }

            return $lead->fresh();
        });
    }

    public function updateStatus(Lead $lead, User $user, string $status, ?string $lostReason = null): Lead
    {
        $data = ['status' => $status];

        if ($status === Lead::STATUS_WON) {
            $data['converted_at'] = now();
            $data['lost_reason'] = null;
        } elseif ($status === Lead::STATUS_LOST) {
            $data['lost_reason'] = $lostReason;
            $data['converted_at'] = null;
        }

        $lead = $this->update($lead, $user, $data);

        if ($status === Lead::STATUS_WON) {
            $this->activityService->record(
                $lead,
                LeadActivity::TYPE_MARKED_WON,
                'Lead marked as Won'.($lead->estimated_value ? ' — ₹'.number_format($lead->estimated_value, 0) : ''),
                $user
            );
        } elseif ($status === Lead::STATUS_LOST) {
            $this->activityService->record(
                $lead,
                LeadActivity::TYPE_MARKED_LOST,
                'Lead marked as Lost'.($lostReason ? " — {$lostReason}" : ''),
                $user
            );
        }

        return $lead;
    }

    public function addNote(Lead $lead, User $user, string $note): Lead
    {
        $existingNotes = trim($lead->notes ?? '');
        $timestamp = now()->format('d M Y, h:i A');
        $newNote = "[{$timestamp}] {$note}";
        $lead->notes = $existingNotes ? "{$existingNotes}\n\n{$newNote}" : $newNote;
        $lead->save();

        $this->activityService->record(
            $lead,
            LeadActivity::TYPE_NOTE_ADDED,
            'Note added',
            $user,
            ['note' => $note]
        );

        return $lead;
    }

    public function touchContacted(Lead $lead): void
    {
        $lead->update(['last_contacted_at' => now()]);
    }

    public function search(Organization $organization, array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $query = Lead::forOrganization($organization);

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('company', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['source'])) {
            $query->where('source', $filters['source']);
        }

        if (! empty($filters['priority'])) {
            $query->where('priority', $filters['priority']);
        }

        if (! empty($filters['product'])) {
            $query->where('interested_product', 'like', '%'.$filters['product'].'%');
        }

        if (! empty($filters['follow_up_from'])) {
            $query->whereDate('next_follow_up_at', '>=', $filters['follow_up_from']);
        }

        if (! empty($filters['follow_up_to'])) {
            $query->whereDate('next_follow_up_at', '<=', $filters['follow_up_to']);
        }

        if (! empty($filters['created_from'])) {
            $query->whereDate('created_at', '>=', $filters['created_from']);
        }

        if (! empty($filters['created_to'])) {
            $query->whereDate('created_at', '<=', $filters['created_to']);
        }

        if (! empty($filters['view'])) {
            match ($filters['view']) {
                'new' => $query->where('status', Lead::STATUS_NEW),
                'won' => $query->where('status', Lead::STATUS_WON),
                'lost' => $query->where('status', Lead::STATUS_LOST),
                'hot' => $query->where(function ($q) {
                    $q->where('temperature', Lead::TEMP_HOT)
                        ->orWhere('priority', Lead::PRIORITY_HIGH);
                }),
                'needs_follow_up' => $query->whereIn('status', Lead::openStatuses())
                    ->where(function ($q) {
                        $q->whereNotNull('next_follow_up_at')
                            ->orWhereHas('followUps', fn ($fq) => $fq->pending());
                    }),
                'overdue' => $query->whereIn('status', Lead::openStatuses())
                    ->where(function ($q) {
                        $q->where('next_follow_up_at', '<', now())
                            ->orWhereHas('followUps', fn ($fq) => $fq->overdue());
                    }),
                default => null,
            };
        }

        $sort = $filters['sort'] ?? 'latest';
        match ($sort) {
            'oldest' => $query->orderBy('created_at'),
            'follow_up' => $query->orderByRaw('next_follow_up_at IS NULL')->orderBy('next_follow_up_at'),
            'value' => $query->orderByDesc('estimated_value'),
            default => $query->orderByDesc('created_at'),
        };

        return $query->paginate($perPage)->withQueryString();
    }

    public function getPipelineLeads(Organization $organization): Collection
    {
        return Lead::forOrganization($organization)
            ->orderByDesc('updated_at')
            ->get();
    }

    public function findForOrganization(Organization $organization, int $leadId): Lead
    {
        return Lead::forOrganization($organization)->findOrFail($leadId);
    }

    private function pauseAutomation(Lead $lead, string $reason): void
    {
        $config = $lead->automationConfig;
        if ($config) {
            $config->update([
                'is_active' => false,
                'paused_at' => now(),
                'pause_reason' => $reason,
            ]);
        }

        $lead->update(['automation_enabled' => false]);
    }
}
