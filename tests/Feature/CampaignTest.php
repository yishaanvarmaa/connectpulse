<?php

namespace Tests\Feature;

use App\Jobs\ProcessCampaignRecipientJob;
use App\Models\Campaign;
use App\Models\CampaignRecipient;
use App\Models\Contact;
use App\Models\CreditWallet;
use App\Models\Organization;
use App\Models\User;
use App\Models\WhatsappConnection;
use App\Services\CampaignService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class CampaignTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Organization $organization;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'connectpulse.whatsapp_bridge_url' => 'http://bridge.test',
            'connectpulse.whatsapp_bridge_secret' => 'bridge-secret',
            'connectpulse.campaign_delay_min_seconds' => 1,
            'connectpulse.campaign_delay_max_seconds' => 2,
        ]);

        $this->organization = Organization::create([
            'company_name' => 'Campaign Test Org',
            'contact_person' => 'Admin',
            'email' => 'campaign@test.com',
            'mobile' => '9876543210',
            'status' => Organization::STATUS_ACTIVE,
        ]);

        CreditWallet::create([
            'organization_id' => $this->organization->id,
            'balance' => 100,
        ]);

        WhatsappConnection::create([
            'organization_id' => $this->organization->id,
            'status' => WhatsappConnection::STATUS_CONNECTED,
            'phone_number' => '919876543210',
            'session_path' => storage_path("app/whatsapp/{$this->organization->id}"),
        ]);

        $this->user = User::factory()->create([
            'role' => User::ROLE_ORGANIZATION_ADMIN,
            'organization_id' => $this->organization->id,
        ]);

        Http::fake([
            'bridge.test/status*' => Http::response([
                'connected' => true,
                'phone' => '919876543210',
                'status' => 'connected',
            ]),
            'bridge.test/send' => Http::response([
                'success' => true,
                'message_id' => 'msg-test-123',
            ]),
        ]);
    }

    public function test_campaigns_index_requires_auth(): void
    {
        $this->get(route('org.campaigns.index'))->assertRedirect(route('login'));
    }

    public function test_org_admin_can_view_campaigns_index(): void
    {
        $this->actingAs($this->user)
            ->get(route('org.campaigns.index'))
            ->assertOk()
            ->assertSee('Campaigns');
    }

    public function test_can_create_campaign_with_contacts(): void
    {
        Contact::create([
            'organization_id' => $this->organization->id,
            'name' => 'Ravi',
            'phone' => '919111111111',
            'source' => Contact::SOURCE_MANUAL,
        ]);

        Contact::create([
            'organization_id' => $this->organization->id,
            'name' => 'Priya',
            'phone' => '919222222222',
            'source' => Contact::SOURCE_MANUAL,
        ]);

        Contact::create([
            'organization_id' => $this->organization->id,
            'name' => 'Suresh',
            'phone' => '919333333333',
            'source' => Contact::SOURCE_MANUAL,
        ]);

        $this->actingAs($this->user)->post(route('org.campaigns.store'), [
            'name' => 'Test Campaign',
            'message_body' => 'Hello {{name}}',
            'audience_type' => Campaign::AUDIENCE_ALL_CONTACTS,
            'delay_min_seconds' => 10,
            'delay_max_seconds' => 20,
            'send_mode' => 'now',
            'test_phone' => '919999999999',
            'launch' => 0,
        ])->assertRedirect();

        $campaign = Campaign::first();
        $this->assertNotNull($campaign);
        $this->assertEquals(3, $campaign->total_recipients);
        $this->assertTrue($campaign->test_confirmed);
    }

    public function test_campaign_processes_recipients_sequentially(): void
    {
        Queue::fake([ProcessCampaignRecipientJob::class]);

        $campaign = Campaign::create([
            'organization_id' => $this->organization->id,
            'created_by' => $this->user->id,
            'name' => 'Sequential Test',
            'message_body' => 'Hello {{name}}',
            'audience_type' => Campaign::AUDIENCE_MANUAL,
            'audience_config' => [],
            'status' => Campaign::STATUS_DRAFT,
            'delay_min_seconds' => 1,
            'delay_max_seconds' => 2,
            'test_confirmed' => true,
            'total_recipients' => 3,
        ]);

        foreach (['919111111111', '919222222222', '919333333333'] as $i => $phone) {
            CampaignRecipient::create([
                'campaign_id' => $campaign->id,
                'phone' => $phone,
                'name' => "User {$i}",
                'rendered_message' => "Hello User {$i}",
                'status' => CampaignRecipient::STATUS_PENDING,
            ]);
        }

        app(CampaignService::class)->launch($campaign);

        Queue::assertPushed(ProcessCampaignRecipientJob::class);
    }

    public function test_campaign_pauses_on_insufficient_credits(): void
    {
        $this->organization->creditWallet->update(['balance' => 0]);

        $campaign = Campaign::create([
            'organization_id' => $this->organization->id,
            'created_by' => $this->user->id,
            'name' => 'No Credits',
            'message_body' => 'Hello',
            'audience_type' => Campaign::AUDIENCE_MANUAL,
            'status' => Campaign::STATUS_RUNNING,
            'delay_min_seconds' => 1,
            'delay_max_seconds' => 2,
            'test_confirmed' => true,
            'total_recipients' => 1,
        ]);

        CampaignRecipient::create([
            'campaign_id' => $campaign->id,
            'phone' => '919111111111',
            'name' => 'User',
            'rendered_message' => 'Hello',
            'status' => CampaignRecipient::STATUS_PENDING,
        ]);

        app(CampaignService::class)->processNextRecipient($campaign);

        $campaign->refresh();
        $this->assertEquals(Campaign::STATUS_PAUSED, $campaign->status);
        $this->assertStringContainsString('insufficient credits', strtolower($campaign->pause_reason));
    }

    public function test_successful_recipient_is_not_sent_twice_on_resume(): void
    {
        $campaign = Campaign::create([
            'organization_id' => $this->organization->id,
            'created_by' => $this->user->id,
            'name' => 'No Duplicate',
            'message_body' => 'Hello',
            'audience_type' => Campaign::AUDIENCE_MANUAL,
            'status' => Campaign::STATUS_RUNNING,
            'delay_min_seconds' => 1,
            'delay_max_seconds' => 2,
            'test_confirmed' => true,
            'total_recipients' => 2,
            'sent_count' => 1,
        ]);

        CampaignRecipient::create([
            'campaign_id' => $campaign->id,
            'phone' => '919111111111',
            'rendered_message' => 'Hello',
            'status' => CampaignRecipient::STATUS_SENT,
            'sent_at' => now(),
        ]);

        CampaignRecipient::create([
            'campaign_id' => $campaign->id,
            'phone' => '919222222222',
            'rendered_message' => 'Hello',
            'status' => CampaignRecipient::STATUS_PENDING,
        ]);

        $service = app(CampaignService::class);
        $service->processNextRecipient($campaign);

        $sentCount = CampaignRecipient::where('campaign_id', $campaign->id)
            ->where('status', CampaignRecipient::STATUS_SENT)
            ->count();

        $this->assertEquals(2, $sentCount);

        $service->processNextRecipient($campaign->fresh());

        $this->assertEquals(2, CampaignRecipient::where('campaign_id', $campaign->id)
            ->where('status', CampaignRecipient::STATUS_SENT)
            ->count());
    }

    public function test_pause_and_cancel_campaign(): void
    {
        $campaign = Campaign::create([
            'organization_id' => $this->organization->id,
            'created_by' => $this->user->id,
            'name' => 'Control Test',
            'message_body' => 'Hello',
            'audience_type' => Campaign::AUDIENCE_MANUAL,
            'status' => Campaign::STATUS_RUNNING,
            'test_confirmed' => true,
            'total_recipients' => 5,
        ]);

        $this->actingAs($this->user)
            ->post(route('org.campaigns.pause', $campaign))
            ->assertRedirect();

        $campaign->refresh();
        $this->assertEquals(Campaign::STATUS_PAUSED, $campaign->status);

        $this->actingAs($this->user)
            ->post(route('org.campaigns.cancel', $campaign))
            ->assertRedirect();

        $campaign->refresh();
        $this->assertEquals(Campaign::STATUS_CANCELLED, $campaign->status);
    }

    public function test_campaign_isolated_by_organization(): void
    {
        $otherOrg = Organization::create([
            'company_name' => 'Other Org',
            'contact_person' => 'Other',
            'email' => 'other@test.com',
            'mobile' => '9111111111',
            'status' => Organization::STATUS_ACTIVE,
        ]);

        $campaign = Campaign::create([
            'organization_id' => $otherOrg->id,
            'name' => 'Secret Campaign',
            'message_body' => 'Hello',
            'audience_type' => Campaign::AUDIENCE_MANUAL,
            'status' => Campaign::STATUS_DRAFT,
        ]);

        $this->actingAs($this->user)
            ->get(route('org.campaigns.show', $campaign))
            ->assertForbidden();
    }

    public function test_invalid_phone_is_skipped_during_build(): void
    {
        $service = app(\App\Services\ContactService::class);

        $audience = $service->resolveAudience(
            $this->organization,
            Campaign::AUDIENCE_CSV,
            ['phones' => [
                ['phone' => '123', 'name' => 'Bad'],
                ['phone' => '919876543210', 'name' => 'Good'],
            ]]
        );

        $this->assertCount(1, $audience);
        $this->assertEquals('919876543210', $audience->first()['phone']);
    }
}
