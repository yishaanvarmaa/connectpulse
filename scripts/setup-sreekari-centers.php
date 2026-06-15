<?php

/**
 * Create or update both Sreekari Diagnostix centers (same website, two WhatsApp numbers).
 * Run: php scripts/setup-sreekari-centers.php
 */

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$centers = [
    [
        'slug' => 'sreekari',
        'company_name' => 'Sreekari Diagnostix',
        'contact_person' => 'Sreekari Admin',
        'email' => 'admin@sreekaridiagnostix.com',
        'mobile' => '919000000001',
        'aliases' => ['Srikari Diagnostix', 'admin@srikaridiagnostix.com'],
    ],
    [
        'slug' => 'narayani',
        'company_name' => 'Sri Narayani Imaging',
        'contact_person' => 'Narayani Admin',
        'email' => 'imaging@sreekaridiagnostix.com',
        'mobile' => '919000000002',
        'aliases' => [],
    ],
];

function upsertCenter(array $center): App\Models\Organization
{
    $query = App\Models\Organization::query()
        ->where('company_name', $center['company_name'])
        ->orWhere('email', $center['email']);

    foreach ($center['aliases'] as $alias) {
        if (str_contains($alias, '@')) {
            $query->orWhere('email', $alias);
        } else {
            $query->orWhere('company_name', $alias);
        }
    }

    $org = $query->first();

    if ($org) {
        $org->update([
            'company_name' => $center['company_name'],
            'contact_person' => $center['contact_person'],
            'email' => $center['email'],
            'mobile' => $center['mobile'],
        ]);

        $org->users()->update([
            'name' => $center['contact_person'],
            'email' => $center['email'],
        ]);

        echo "Updated {$center['company_name']} (#{$org->id})\n";
    } else {
        $org = app(App\Services\OrganizationService::class)->create([
            'company_name' => $center['company_name'],
            'contact_person' => $center['contact_person'],
            'email' => $center['email'],
            'mobile' => $center['mobile'],
        ], 'password');

        app(App\Services\CreditService::class)->addCredits(
            $org,
            1000,
            "Initial credits for {$center['company_name']}"
        );

        echo "Created {$center['company_name']} (#{$org->id})\n";
    }

    app(App\Services\CreditService::class)->setBalance($org, 1000, 'Set to 1000 credits');

    return $org->fresh()->load(['apiKey', 'creditWallet']);
}

echo "Setting up Sreekari Diagnostix multi-center accounts...\n\n";

$results = [];

foreach ($centers as $center) {
    $org = upsertCenter($center);
    $results[] = [
        'slug' => $center['slug'],
        'org' => $org,
    ];
}

echo "\n========================================\n";
echo "  TWO CENTERS — ONE WEBSITE\n";
echo "  https://sreekaridiagnostix.com\n";
echo "========================================\n\n";

foreach ($results as $result) {
    $org = $result['org'];

    echo "--- {$org->company_name} (center: {$result['slug']}) ---\n";
    echo "Org ID:     {$org->id}\n";
    echo "Login:      {$org->email} / password\n";
    echo "Credits:    {$org->creditWallet->balance}\n";
    echo "API Key:    {$org->apiKey->api_key}\n";
    echo "API Secret: {$org->apiKey->api_secret}\n";
    echo "WhatsApp:   https://connectpulse.cloud/admin/organizations/{$org->id}/whatsapp\n";
    echo "\n";
}

echo "Connect each center's WhatsApp separately (scan QR with that center's phone number).\n";
