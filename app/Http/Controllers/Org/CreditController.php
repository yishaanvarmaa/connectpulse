<?php

namespace App\Http\Controllers\Org;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CreditController extends Controller
{
    public function index(Request $request): View
    {
        $organization = $request->user()->organization;
        $organization->load('creditWallet');

        return view('org.credits.index', [
            'organization' => $organization,
            'transactions' => $organization->creditTransactions()->latest()->paginate(20),
        ]);
    }
}
