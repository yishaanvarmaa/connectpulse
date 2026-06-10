<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MessageLog;
use App\Models\Organization;
use App\Models\WhatsappConnection;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        return view('admin.dashboard', [
            'organizationsCount' => Organization::count(),
            'activeOrganizations' => Organization::where('status', Organization::STATUS_ACTIVE)->count(),
            'connectedWhatsApp' => WhatsappConnection::where('status', WhatsappConnection::STATUS_CONNECTED)->count(),
            'messagesToday' => MessageLog::whereDate('created_at', today())->count(),
            'recentLogs' => MessageLog::with('organization')->latest()->limit(10)->get(),
            'activeConnections' => WhatsappConnection::with('organization')
                ->where('status', WhatsappConnection::STATUS_CONNECTED)
                ->get(),
        ]);
    }
}
