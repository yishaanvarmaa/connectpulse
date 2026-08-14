<?php

namespace App\Http\Controllers\Org;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Models\LeadFollowUp;
use App\Models\MessageLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SearchController extends Controller
{
    public function __invoke(Request $request): View
    {
        $query = trim((string) $request->input('q', ''));
        $organization = $request->user()->organization;

        $leads = collect();
        $messages = collect();
        $followUps = collect();

        if (strlen($query) >= 2) {
            $leads = Lead::forOrganization($organization)
                ->where(function ($q) use ($query) {
                    $q->where('name', 'like', "%{$query}%")
                        ->orWhere('phone', 'like', "%{$query}%")
                        ->orWhere('company', 'like', "%{$query}%")
                        ->orWhere('interested_product', 'like', "%{$query}%")
                        ->orWhere('email', 'like', "%{$query}%");
                })
                ->latest()
                ->limit(10)
                ->get();

            $messages = MessageLog::query()
                ->where('organization_id', $organization->id)
                ->where(function ($q) use ($query) {
                    $q->where('mobile', 'like', "%{$query}%")
                        ->orWhere('message', 'like', "%{$query}%");
                })
                ->latest()
                ->limit(10)
                ->get();

            $followUps = LeadFollowUp::forOrganization($organization)
                ->pending()
                ->whereHas('lead', function ($q) use ($query) {
                    $q->where('name', 'like', "%{$query}%")
                        ->orWhere('phone', 'like', "%{$query}%");
                })
                ->with('lead')
                ->orderBy('scheduled_at')
                ->limit(10)
                ->get();
        }

        return view('org.search.index', [
            'query' => $query,
            'leads' => $leads,
            'messages' => $messages,
            'followUps' => $followUps,
        ]);
    }
}
