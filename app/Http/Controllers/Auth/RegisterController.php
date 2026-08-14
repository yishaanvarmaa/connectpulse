<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\OrganizationService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class RegisterController extends Controller
{
    public function __construct(
        private OrganizationService $organizationService,
    ) {}

    public function show(): View
    {
        return view('auth.register');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'company_name' => ['required', 'string', 'max:255'],
            'contact_person' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:organizations,email', 'unique:users,email'],
            'mobile' => ['required', 'string', 'min:10', 'max:20'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $validated['mobile'] = preg_replace('/\D+/', '', $validated['mobile']);

        $organization = $this->organizationService->create($validated, $validated['password']);

        $user = User::where('organization_id', $organization->id)
            ->where('email', $validated['email'])
            ->firstOrFail();

        event(new Registered($user));

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()
            ->route('org.dashboard')
            ->with('success', 'Welcome to ConnectPulse! Your workspace is ready.');
    }
}
