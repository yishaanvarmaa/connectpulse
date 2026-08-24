<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Services\CreditService;
use App\Services\OrganizationService;
use App\Support\EmailAddress;
use App\Support\PhoneNumber;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrganizationController extends Controller
{
    public function __construct(
        private OrganizationService $organizationService,
        private CreditService $creditService,
    ) {}

    public function index(): View
    {
        return view('admin.organizations.index', [
            'organizations' => Organization::with(['creditWallet', 'whatsappConnection', 'apiKey'])
                ->latest()
                ->paginate(15),
        ]);
    }

    public function create(): View
    {
        return view('admin.organizations.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->merge([
            'email' => strtolower(trim((string) $request->input('email'))),
            'mobile' => PhoneNumber::national((string) $request->input('mobile')),
        ]);

        $validated = $request->validate([
            'company_name' => ['required', 'string', 'max:255'],
            'contact_person' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:organizations,email', 'unique:users,email'],
            'mobile' => ['required', 'string', 'max:20', 'unique:organizations,mobile'],
            'password' => ['required', 'string', 'min:8'],
            'initial_credits' => ['nullable', 'integer', 'min:0'],
        ]);

        $organization = $this->organizationService->create($validated, $validated['password']);

        if (! empty($validated['initial_credits'])) {
            $this->creditService->addCredits(
                $organization,
                (int) $validated['initial_credits'],
                'Initial credits',
                $request->user()->id
            );
        }

        return redirect()->route('admin.organizations.index')
            ->with('success', 'Organization created successfully.');
    }

    public function show(Organization $organization): View
    {
        $organization->load(['creditWallet', 'whatsappConnection', 'apiKey', 'users']);

        return view('admin.organizations.show', [
            'organization' => $organization,
            'messageLogs' => $organization->messageLogs()->latest()->paginate(20),
        ]);
    }

    public function update(Request $request, Organization $organization): RedirectResponse
    {
        $request->merge([
            'email' => strtolower(trim((string) $request->input('email'))),
            'mobile' => PhoneNumber::national((string) $request->input('mobile')),
        ]);

        $validated = $request->validate([
            'contact_person' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:organizations,email,'.$organization->id],
            'mobile' => ['required', 'string', 'max:20', 'unique:organizations,mobile,'.$organization->id],
        ]);

        $organization->update([
            'contact_person' => $validated['contact_person'],
            'email' => $validated['email'],
            'normalized_email' => EmailAddress::normalize($validated['email']),
            'mobile' => $validated['mobile'],
        ]);

        return back()->with('success', 'Organization details updated.');
    }

    public function suspend(Organization $organization): RedirectResponse
    {
        $this->organizationService->suspend($organization);

        return back()->with('success', 'Organization suspended.');
    }

    public function activate(Organization $organization): RedirectResponse
    {
        $this->organizationService->activate($organization);

        return back()->with('success', 'Organization activated.');
    }

    public function destroy(Organization $organization): RedirectResponse
    {
        $this->organizationService->delete($organization);

        return redirect()->route('admin.organizations.index')
            ->with('success', 'Organization deleted.');
    }
}
