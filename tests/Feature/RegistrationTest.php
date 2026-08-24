<?php

namespace Tests\Feature;

use App\Models\Organization;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();
    }

    /**
     * @return array<string, mixed>
     */
    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'company_name' => 'Acme Diagnostics',
            'contact_person' => 'Ravi Kumar',
            'email' => 'ravi@acme-diagnostics.com',
            'mobile' => '9876543210',
            'password' => 'password12',
            'password_confirmation' => 'password12',
        ], $overrides);
    }

    public function test_register_page_is_shown(): void
    {
        $this->get(route('register'))
            ->assertOk()
            ->assertSee('15 free credits');
    }

    public function test_new_account_receives_welcome_credits(): void
    {
        $this->post(route('register'), $this->validPayload())
            ->assertRedirect(route('org.dashboard'));

        $this->assertAuthenticated();

        $organization = Organization::query()->where('email', 'ravi@acme-diagnostics.com')->first();
        $this->assertNotNull($organization);
        $this->assertSame('9876543210', $organization->mobile);
        $this->assertSame(15, $organization->creditWallet?->balance);
    }

    public function test_disposable_email_is_rejected(): void
    {
        $this->from(route('register'))
            ->post(route('register'), $this->validPayload([
                'email' => 'temp@mailinator.com',
            ]))
            ->assertRedirect(route('register'))
            ->assertSessionHasErrors('email');

        $this->assertGuest();
        $this->assertSame(0, Organization::query()->count());
    }

    public function test_gmail_alias_cannot_register_twice(): void
    {
        $this->post(route('register'), $this->validPayload([
            'email' => 'john.smith@gmail.com',
            'mobile' => '9876543210',
        ]))->assertRedirect(route('org.dashboard'));

        auth()->logout();

        $this->from(route('register'))
            ->post(route('register'), $this->validPayload([
                'company_name' => 'Second Org',
                'email' => 'johnsmith+promo@gmail.com',
                'mobile' => '9876543211',
            ]))
            ->assertRedirect(route('register'))
            ->assertSessionHasErrors('email');
    }

    public function test_same_mobile_cannot_register_twice(): void
    {
        $this->post(route('register'), $this->validPayload())->assertRedirect(route('org.dashboard'));
        auth()->logout();

        $this->from(route('register'))
            ->post(route('register'), $this->validPayload([
                'company_name' => 'Second Org',
                'email' => 'other@acme-diagnostics.com',
                'mobile' => '919876543210',
            ]))
            ->assertRedirect(route('register'))
            ->assertSessionHasErrors('mobile');
    }

    public function test_honeypot_rejects_bots(): void
    {
        $this->from(route('register'))
            ->post(route('register'), $this->validPayload([
                'website' => 'https://spam.example',
            ]))
            ->assertRedirect(route('register'))
            ->assertSessionHasErrors('website');

        $this->assertSame(0, Organization::query()->count());
    }

    public function test_registration_is_rate_limited_per_ip(): void
    {
        for ($i = 1; $i <= 3; $i++) {
            $this->post(route('register'), $this->validPayload([
                'company_name' => "Org {$i}",
                'email' => "user{$i}@acme-diagnostics.com",
                'mobile' => (string) (9876543210 + $i),
            ]))->assertRedirect(route('org.dashboard'));

            auth()->logout();
        }

        $this->post(route('register'), $this->validPayload([
            'company_name' => 'Org 4',
            'email' => 'user4@acme-diagnostics.com',
            'mobile' => '9876543215',
        ]))->assertStatus(429);
    }
}
