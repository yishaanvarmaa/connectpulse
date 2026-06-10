<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MessageLog;
use App\Models\Organization;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MessageLogController extends Controller
{
    public function index(Request $request): View
    {
        $query = MessageLog::with('organization')->latest();

        if ($request->filled('organization_id')) {
            $query->where('organization_id', $request->organization_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return view('admin.logs.index', [
            'logs' => $query->paginate(25)->withQueryString(),
            'organizations' => Organization::orderBy('company_name')->get(),
        ]);
    }
}
