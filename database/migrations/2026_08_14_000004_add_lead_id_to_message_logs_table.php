<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('message_logs', function (Blueprint $table) {
            $table->foreignId('lead_id')->nullable()->after('organization_id')->constrained()->nullOnDelete();
            $table->index(['organization_id', 'mobile']);
        });
    }

    public function down(): void
    {
        Schema::table('message_logs', function (Blueprint $table) {
            $table->dropForeign(['lead_id']);
            $table->dropColumn('lead_id');
            $table->dropIndex(['organization_id', 'mobile']);
        });
    }
};
