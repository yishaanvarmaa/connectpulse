<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Services\CreditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CreditController extends Controller
{
    public function __construct(
        private CreditService $creditService
    ) {}

    public function index(): View
    {
        return view('admin.credits.index', [
            'organizations' => Organization::with('creditWallet')->orderBy('company_name')->get(),
        ]);
    }

    public function store(Request $request, Organization $organization): RedirectResponse
    {
        $validated = $request->validate([
            'amount' => ['required', 'integer', 'min:1'],
            'remarks' => ['nullable', 'string', 'max:255'],
        ]);

        $this->creditService->addCredits(
            $organization,
            $validated['amount'],
            $validated['remarks'] ?? 'Admin credit recharge',
            $request->user()->id
        );

        return back()->with('success', 'Credits added successfully.');
    }

    public function setBalance(Request $request, Organization $organization): RedirectResponse
    {
        $validated = $request->validate([
            'balance' => ['required', 'integer', 'min:0'],
            'remarks' => ['nullable', 'string', 'max:255'],
        ]);

        $this->creditService->setBalance(
            $organization,
            (int) $validated['balance'],
            $validated['remarks'] ?? 'Admin set balance',
            $request->user()->id
        );

        return back()->with('success', 'Credit balance updated to '.number_format($validated['balance']).'.');
    }
}
