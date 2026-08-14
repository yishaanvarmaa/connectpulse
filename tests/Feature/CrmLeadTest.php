<?php

namespace Tests\Feature;

use App\Models\CreditWallet;
use App\Models\Lead;
use App\Models\Organization;
use App\Models\User;
use App\Models\WhatsappConnection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CrmLeadTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Organization $organization;

    protected function setUp(): void
    {
        parent::setUp();

        $this->organization = Organization::create([
            'company_name' => 'Test Org',
            'contact_person' => 'Admin',
            'email' => 'crm@test.com',
            'mobile' => '9876543210',
            'status' => Organization::STATUS_ACTIVE,
        ]);

        CreditWallet::create([
            'organization_id' => $this->organization->id,
            'balance' => 100,
        ]);

        WhatsappConnection::create([
            'organization_id' => $this->organization->id,
            'status' => WhatsappConnection::STATUS_DISCONNECTED,
            'session_path' => storage_path("app/whatsapp/{$this->organization->id}"),
        ]);

        $this->user = User::factory()->create([
            'role' => User::ROLE_ORGANIZATION_ADMIN,
            'organization_id' => $this->organization->id,
        ]);
    }

    public function test_org_admin_can_log_interaction_and_schedule_next_follow_up(): void
    {
        $lead = Lead::create([
            'organization_id' => $this->organization->id,
            'name' => 'Follow-up Lead',
            'phone' => '9876543210',
            'source' => Lead::SOURCE_MANUAL,
            'status' => Lead::STATUS_NEW,
            'priority' => Lead::PRIORITY_MEDIUM,
            'next_follow_up_at' => now()->subHour(),
        ]);

        $followUp = \App\Models\LeadFollowUp::create([
            'lead_id' => $lead->id,
            'organization_id' => $this->organization->id,
            'created_by' => $this->user->id,
            'scheduled_at' => now()->subHour(),
            'type' => \App\Models\LeadFollowUp::TYPE_CALL,
            'status' => \App\Models\LeadFollowUp::STATUS_PENDING,
        ]);

        $nextAt = now()->addDay()->format('Y-m-d\TH:i');

        $this->actingAs($this->user)->post(route('org.crm.leads.log-interaction', $lead), [
            'outcome' => \App\Models\LeadActivity::OUTCOME_NO_ANSWER,
            'notes' => 'Rang twice, no answer.',
            'follow_up_id' => $followUp->id,
            'next_scheduled_at' => $nextAt,
            'next_type' => \App\Models\LeadFollowUp::TYPE_CALL,
        ])->assertRedirect();

        $followUp->refresh();
        $this->assertSame(\App\Models\LeadFollowUp::STATUS_COMPLETED, $followUp->status);
        $this->assertStringContainsString('no answer', strtolower($followUp->notes));

        $this->assertDatabaseHas('lead_activities', [
            'lead_id' => $lead->id,
            'type' => 'interaction_logged',
            'description' => 'Rang twice, no answer.',
        ]);

        $this->assertDatabaseHas('lead_follow_ups', [
            'lead_id' => $lead->id,
            'status' => \App\Models\LeadFollowUp::STATUS_PENDING,
            'type' => \App\Models\LeadFollowUp::TYPE_CALL,
        ]);

        $this->actingAs($this->user)
            ->get(route('org.crm.leads.show', $lead))
            ->assertOk()
            ->assertSee('Rang twice, no answer.')
            ->assertSee('Called — no answer');
    }

    public function test_org_admin_can_create_lead_with_minimal_slideover_fields(): void
    {
        $response = $this->actingAs($this->user)->post(route('org.crm.leads.store'), [
            'name' => 'Minimal Lead',
            'phone' => '+91 98765 43210',
            'source' => 'manual',
            'estimated_value' => '',
        ]);

        $response->assertRedirect(route('org.crm.leads.index'));
        $this->assertDatabaseHas('leads', [
            'organization_id' => $this->organization->id,
            'name' => 'Minimal Lead',
            'phone' => '919876543210',
        ]);

        $this->actingAs($this->user)
            ->get(route('org.crm.leads.index'))
            ->assertOk()
            ->assertSee('Minimal Lead');
    }

    public function test_org_admin_can_create_lead(): void
    {
        $response = $this->actingAs($this->user)->post(route('org.crm.leads.store'), [
            'name' => 'Ravi Kumar',
            'phone' => '9876543210',
            'interested_product' => 'Diagnostic Software',
            'estimated_value' => 24999,
            'source' => 'facebook',
            'next_follow_up_at' => now()->addDay()->format('Y-m-d\TH:i'),
        ]);

        $response->assertRedirect(route('org.crm.leads.index'));
        $this->assertDatabaseHas('leads', [
            'organization_id' => $this->organization->id,
            'name' => 'Ravi Kumar',
            'phone' => '9876543210',
            'source' => 'facebook',
            'status' => Lead::STATUS_NEW,
        ]);
        $this->assertDatabaseHas('lead_activities', [
            'organization_id' => $this->organization->id,
            'type' => 'created',
        ]);
    }

    public function test_org_admin_cannot_view_other_org_lead(): void
    {
        $otherOrg = Organization::create([
            'company_name' => 'Other Org',
            'contact_person' => 'Other',
            'email' => 'other@test.com',
            'mobile' => '9999999999',
            'status' => Organization::STATUS_ACTIVE,
        ]);

        $lead = Lead::create([
            'organization_id' => $otherOrg->id,
            'name' => 'Secret Lead',
            'phone' => '1111111111',
            'source' => Lead::SOURCE_MANUAL,
            'status' => Lead::STATUS_NEW,
            'priority' => Lead::PRIORITY_MEDIUM,
        ]);

        $response = $this->actingAs($this->user)->get(route('org.crm.leads.show', $lead));

        $response->assertForbidden();
    }

    public function test_mark_lead_won_updates_dashboard_stats(): void
    {
        Lead::create([
            'organization_id' => $this->organization->id,
            'name' => 'Won Lead',
            'phone' => '9876543210',
            'source' => Lead::SOURCE_FACEBOOK,
            'status' => Lead::STATUS_NEGOTIATION,
            'priority' => Lead::PRIORITY_HIGH,
            'estimated_value' => 24999,
        ]);

        Lead::create([
            'organization_id' => $this->organization->id,
            'name' => 'Lost Lead',
            'phone' => '9876543211',
            'source' => Lead::SOURCE_FACEBOOK,
            'status' => Lead::STATUS_LOST,
            'priority' => Lead::PRIORITY_MEDIUM,
            'estimated_value' => 10000,
        ]);

        $lead = Lead::where('name', 'Won Lead')->first();

        $this->actingAs($this->user)->post(route('org.crm.leads.status', $lead), [
            'status' => Lead::STATUS_WON,
        ])->assertRedirect();

        $response = $this->actingAs($this->user)->get(route('org.crm.dashboard'));

        $response->assertOk();
        $response->assertSee('Won Revenue');
        $response->assertSee('24,999');
    }

    public function test_crm_dashboard_requires_auth(): void
    {
        $this->get(route('org.crm.dashboard'))->assertRedirect(route('login'));
    }

    public function test_org_admin_can_view_pipeline(): void
    {
        Lead::create([
            'organization_id' => $this->organization->id,
            'name' => 'Pipeline Lead',
            'phone' => '9876543210',
            'source' => Lead::SOURCE_FACEBOOK,
            'status' => Lead::STATUS_NEW,
            'priority' => Lead::PRIORITY_MEDIUM,
            'estimated_value' => 24999,
        ]);

        $this->actingAs($this->user)
            ->get(route('org.crm.pipeline.index'))
            ->assertOk()
            ->assertSee('Pipeline Lead');
    }
}
