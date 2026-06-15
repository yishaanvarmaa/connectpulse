<?php

namespace Database\Seeders;

use App\Models\User;
use App\Services\CreditService;
use App\Services\OrganizationService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Super Admin',
            'email' => 'admin@connectpulse.app',
            'password' => Hash::make('password'),
            'role' => User::ROLE_SUPER_ADMIN,
        ]);

        $organizationService = app(OrganizationService::class);
        $creditService = app(CreditService::class);

        $surabhi = $organizationService->create([
            'company_name' => 'Surabhi Diagnostics',
            'contact_person' => 'Surabhi Admin',
            'email' => 'admin@surabhidiagnostics.com',
            'mobile' => '919553095537',
        ], 'password');

        $creditService->addCredits($surabhi, 1000, 'Initial credits for Surabhi Diagnostics');

        $navocab = $organizationService->create([
            'company_name' => 'Navocab',
            'contact_person' => 'Navocab Admin',
            'email' => 'admin@navocab.com',
            'mobile' => '9876543211',
        ], 'password');

        $creditService->addCredits($navocab, 1000, 'Initial credits for Navocab');

        $sreekari = $organizationService->create([
            'company_name' => 'Sreekari Diagnostix',
            'contact_person' => 'Sreekari Admin',
            'email' => 'admin@sreekaridiagnostix.com',
            'mobile' => '919000000000',
        ], 'password');

        $creditService->addCredits($sreekari, 1000, 'Initial credits for Sreekari Diagnostix');

        $narayani = $organizationService->create([
            'company_name' => 'Sri Narayani Imaging',
            'contact_person' => 'Narayani Admin',
            'email' => 'imaging@sreekaridiagnostix.com',
            'mobile' => '919000000002',
        ], 'password');

        $creditService->addCredits($narayani, 1000, 'Initial credits for Sri Narayani Imaging');
    }
}
