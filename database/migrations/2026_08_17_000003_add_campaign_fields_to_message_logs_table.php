<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('message_logs', function (Blueprint $table) {
            $table->foreignId('campaign_id')->nullable()->after('batch_id')->constrained()->nullOnDelete();
            $table->foreignId('campaign_recipient_id')->nullable()->after('campaign_id')->constrained()->nullOnDelete();
            $table->string('media_path')->nullable()->after('message');
            $table->string('media_type')->nullable()->after('media_path');
        });
    }

    public function down(): void
    {
        Schema::table('message_logs', function (Blueprint $table) {
            $table->dropConstrainedForeignId('campaign_recipient_id');
            $table->dropConstrainedForeignId('campaign_id');
            $table->dropColumn(['media_path', 'media_type']);
        });
    }
};
