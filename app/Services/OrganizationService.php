<?php

namespace App\Services;

use App\Models\Organization;
use App\Models\User;
use App\Models\WhatsappConnection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class OrganizationService
{
    public function __construct(
        private ApiKeyService $apiKeyService,
        private CreditService $creditService,
        private WhatsAppBridgeService $bridgeService,
    ) {}

    public function create(array $data, ?string $adminPassword = null): Organization
    {
        return DB::transaction(function () use ($data, $adminPassword) {
            $organization = Organization::create([
                'company_name' => $data['company_name'],
                'contact_person' => $data['contact_person'],
                'email' => $data['email'],
                'mobile' => $data['mobile'],
                'status' => Organization::STATUS_ACTIVE,
            ]);

            User::create([
                'name' => $data['contact_person'],
                'email' => $data['email'],
                'password' => Hash::make($adminPassword ?? Str::random(12)),
                'role' => User::ROLE_ORGANIZATION_ADMIN,
                'organization_id' => $organization->id,
            ]);

            $this->apiKeyService->generateForOrganization($organization);
            $this->creditService->ensureWallet($organization);

            WhatsappConnection::create([
                'organization_id' => $organization->id,
                'status' => WhatsappConnection::STATUS_DISCONNECTED,
                'session_path' => storage_path("app/whatsapp/{$organization->id}"),
            ]);

            return $organization->load(['apiKey', 'creditWallet', 'whatsappConnection']);
        });
    }

    public function suspend(Organization $organization): void
    {
        $organization->update(['status' => Organization::STATUS_SUSPENDED]);
    }

    public function activate(Organization $organization): void
    {
        $organization->update(['status' => Organization::STATUS_ACTIVE]);
    }

    public function delete(Organization $organization): void
    {
        DB::transaction(function () use ($organization) {
            $this->bridgeService->disconnect($organization->id);
            $organization->delete();
        });
    }
}
