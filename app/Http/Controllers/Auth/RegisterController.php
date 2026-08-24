<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use App\Services\OrganizationService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
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

    public function store(RegisterRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $organization = $this->organizationService->create(
            $validated,
            $validated['password'],
            grantSignupBonus: true,
        );

        $user = User::where('organization_id', $organization->id)
            ->where('email', $validated['email'])
            ->firstOrFail();

        event(new Registered($user));

        Auth::login($user);
        $request->session()->regenerate();

        $bonus = (int) config('connectpulse.signup_bonus_credits', 15);
        $message = $bonus > 0
            ? "Welcome to ConnectPulse! You've got {$bonus} free credits to try sending messages."
            : 'Welcome to ConnectPulse! Your workspace is ready.';

        return redirect()
            ->route('org.dashboard')
            ->with('success', $message);
    }
}
