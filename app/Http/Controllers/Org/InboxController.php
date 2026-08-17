<?php

namespace App\Http\Controllers\Org;

use App\Http\Controllers\Controller;
use App\Models\CampaignRecipient;
use App\Models\Lead;
use App\Models\MessageLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class InboxController extends Controller
{
    public function index(Request $request): View
    {
        $organization = $request->user()->organization;
        $selectedMobile = $request->input('with');

        $conversations = MessageLog::query()
            ->where('organization_id', $organization->id)
            ->select([
                'mobile',
                DB::raw('MAX(id) as latest_id'),
                DB::raw('MAX(created_at) as latest_at'),
                DB::raw('COUNT(*) as message_count'),
            ])
            ->groupBy('mobile')
            ->orderByDesc('latest_at')
            ->limit(50)
            ->get();

        $latestIds = $conversations->pluck('latest_id');
        $latestMessages = MessageLog::whereIn('id', $latestIds)->get()->keyBy('mobile');

        $conversationLeads = Lead::forOrganization($organization)
            ->get()
            ->keyBy(fn (Lead $l) => $l->normalizedPhone());

        $thread = collect();
        $activeLead = null;
        $activeCampaign = null;

        if ($selectedMobile) {
            $thread = MessageLog::query()
                ->where('organization_id', $organization->id)
                ->where('mobile', $selectedMobile)
                ->orderByDesc('created_at')
                ->limit(50)
                ->get()
                ->reverse()
                ->values();

            $activeLead = Lead::forOrganization($organization)
                ->where(function ($q) use ($selectedMobile) {
                    $q->where('phone', $selectedMobile)
                        ->orWhere('phone', 'like', '%'.substr($selectedMobile, -10));
                })
                ->first();

            $activeCampaign = CampaignRecipient::query()
                ->where('phone', $selectedMobile)
                ->whereHas('campaign', fn ($q) => $q->where('organization_id', $organization->id))
                ->with('campaign')
                ->latest('sent_at')
                ->first();
        }

        return view('org.inbox.index', [
            'conversations' => $conversations,
            'latestMessages' => $latestMessages,
            'conversationLeads' => $conversationLeads,
            'selectedMobile' => $selectedMobile,
            'thread' => $thread,
            'activeLead' => $activeLead,
            'activeCampaign' => $activeCampaign,
        ]);
    }
}
