<?php

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$org = App\Models\Organization::where('company_name', 'Surabhi Diagnostics')->first();

if (! $org) {
    echo "Surabhi Diagnostics not found.\n";
    exit(1);
}

$org->update(['mobile' => '919553095537']);
app(App\Services\CreditService::class)->setBalance($org, 1000, 'Set to 1000 credits');

$org->refresh();
$org->load('creditWallet');

echo "Updated org #{$org->id}: mobile={$org->mobile}, balance={$org->creditWallet->balance}\n";
