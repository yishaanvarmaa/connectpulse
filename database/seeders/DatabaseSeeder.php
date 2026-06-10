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
            'mobile' => '9876543210',
        ], 'password');

        $creditService->addCredits($surabhi, 5000, 'Initial credits for Surabhi Diagnostics');

        $navocab = $organizationService->create([
            'company_name' => 'Navocab',
            'contact_person' => 'Navocab Admin',
            'email' => 'admin@navocab.com',
            'mobile' => '9876543211',
        ], 'password');

        $creditService->addCredits($navocab, 1000, 'Initial credits for Navocab');
    }
}
