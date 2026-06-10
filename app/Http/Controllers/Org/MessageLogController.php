<?php

namespace App\Http\Controllers\Org;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MessageLogController extends Controller
{
    public function index(Request $request): View
    {
        $organization = $request->user()->organization;

        $query = $organization->messageLogs()->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return view('org.logs.index', [
            'logs' => $query->paginate(25)->withQueryString(),
        ]);
    }
}
