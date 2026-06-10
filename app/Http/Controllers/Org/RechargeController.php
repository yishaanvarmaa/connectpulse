<?php

namespace App\Http\Controllers\Org;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RechargeController extends Controller
{
    public function index(Request $request): View
    {
        $organization = $request->user()->organization;
        $organization->load('creditWallet');

        return view('org.recharge.index', [
            'organization' => $organization,
            'transactions' => $organization->creditTransactions()->latest()->paginate(20),
        ]);
    }
}
