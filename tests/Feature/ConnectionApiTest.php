<?php

namespace Tests\Feature;

use App\Models\ApiKey;
use App\Models\CreditWallet;
use App\Models\Organization;
use App\Models\WhatsappConnection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ConnectionApiTest extends TestCase
{
    use RefreshDatabase;

    private Organization $organization;

    private string $apiKey;

    private string $apiSecret;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'connectpulse.whatsapp_bridge_url' => 'http://bridge.test',
            'connectpulse.whatsapp_bridge_secret' => 'bridge-secret',
        ]);

        $this->organization = Organization::create([
            'company_name' => 'Surabhi Diagnostics',
            'contact_person' => 'Admin',
            'email' => 'admin@surabhidiagnostics.com',
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

        $key = ApiKey::create([
            'organization_id' => $this->organization->id,
            'api_key' => 'cp_live_testkey123',
            'api_secret' => 'test-secret-value',
            'is_active' => true,
        ]);

        $this->apiKey = $key->api_key;
        $this->apiSecret = 'test-secret-value';
    }

    public function test_connection_api_returns_connected_when_bridge_is_connected(): void
    {
        Http::fake([
            'bridge.test/status*' => Http::response([
                'connected' => true,
                'phone' => '919390315816',
                'status' => 'connected',
            ]),
        ]);

        $response = $this->getJson('/api/v1/connection', [
            'X-API-Key' => $this->apiKey,
            'X-API-Secret' => $this->apiSecret,
        ]);

        $response->assertOk()
            ->assertJson([
                'connected' => true,
                'phone' => '919390315816',
                'status' => 'connected',
            ]);

        Http::assertSent(function ($request) {
            return $request->url() === 'http://bridge.test/status?organization_id='.$this->organization->id
                && $request->hasHeader('X-Bridge-Secret', 'bridge-secret');
        });
    }

    public function test_connection_api_returns_disconnected_when_bridge_is_disconnected(): void
    {
        Http::fake([
            'bridge.test/status*' => Http::response([
                'connected' => false,
                'phone' => null,
                'status' => 'disconnected',
            ]),
        ]);

        $response = $this->getJson('/api/v1/connection', [
            'X-API-Key' => $this->apiKey,
            'X-API-Secret' => $this->apiSecret,
        ]);

        $response->assertOk()
            ->assertJson([
                'connected' => false,
                'phone' => null,
                'status' => 'disconnected',
            ]);
    }

    public function test_connection_api_returns_disconnected_when_bridge_is_unreachable(): void
    {
        Http::fake([
            'bridge.test/status*' => Http::response(['success' => false, 'error' => 'Unauthorized'], 401),
        ]);

        $response = $this->getJson('/api/v1/connection', [
            'X-API-Key' => $this->apiKey,
            'X-API-Secret' => $this->apiSecret,
        ]);

        $response->assertOk()
            ->assertJson([
                'connected' => false,
                'phone' => null,
                'status' => 'disconnected',
            ]);
    }
}
