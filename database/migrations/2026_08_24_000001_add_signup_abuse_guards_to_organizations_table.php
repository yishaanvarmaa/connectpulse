<?php

use App\Models\Organization;
use App\Support\EmailAddress;
use App\Support\PhoneNumber;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            $table->string('normalized_email')->nullable()->after('email');
        });

        Organization::query()->each(function (Organization $organization): void {
            $organization->forceFill([
                'email' => strtolower(trim((string) $organization->email)),
                'normalized_email' => EmailAddress::normalize((string) $organization->email),
                'mobile' => PhoneNumber::national((string) $organization->mobile),
            ])->save();
        });

        Schema::table('organizations', function (Blueprint $table) {
            $table->unique('normalized_email');
            $table->unique('mobile');
        });
    }

    public function down(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            $table->dropUnique(['normalized_email']);
            $table->dropUnique(['mobile']);
            $table->dropColumn('normalized_email');
        });
    }
};
