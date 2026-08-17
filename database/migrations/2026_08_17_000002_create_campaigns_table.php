<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('campaigns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('name');
            $table->text('message_body');
            $table->string('media_path')->nullable();
            $table->string('media_type')->nullable();
            $table->string('audience_type');
            $table->json('audience_config')->nullable();
            $table->string('status')->default('draft');
            $table->string('pause_reason')->nullable();
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->unsignedInteger('total_recipients')->default(0);
            $table->unsignedInteger('sent_count')->default(0);
            $table->unsignedInteger('delivered_count')->default(0);
            $table->unsignedInteger('failed_count')->default(0);
            $table->unsignedInteger('replied_count')->default(0);
            $table->unsignedInteger('delay_min_seconds')->default(10);
            $table->unsignedInteger('delay_max_seconds')->default(20);
            $table->boolean('test_confirmed')->default(false);
            $table->string('test_phone')->nullable();
            $table->unsignedInteger('credits_used')->default(0);
            $table->timestamps();

            $table->index(['organization_id', 'status']);
            $table->index(['organization_id', 'scheduled_at']);
        });

        Schema::create('campaign_recipients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained()->cascadeOnDelete();
            $table->foreignId('contact_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('lead_id')->nullable()->constrained()->nullOnDelete();
            $table->string('phone');
            $table->string('name')->nullable();
            $table->text('rendered_message')->nullable();
            $table->string('status')->default('pending');
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->foreignId('message_log_id')->nullable()->constrained('message_logs')->nullOnDelete();
            $table->string('provider_message_id')->nullable();
            $table->timestamp('queued_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->text('failure_reason')->nullable();
            $table->timestamp('next_attempt_at')->nullable();
            $table->timestamps();

            $table->index(['campaign_id', 'status']);
            $table->unique(['campaign_id', 'phone']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('campaign_recipients');
        Schema::dropIfExists('campaigns');
    }
};
