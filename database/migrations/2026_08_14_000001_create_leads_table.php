<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('name');
            $table->string('phone', 20);
            $table->string('email')->nullable();
            $table->string('company')->nullable();
            $table->string('designation')->nullable();
            $table->string('source')->default('manual');
            $table->string('interested_product')->nullable();
            $table->decimal('estimated_value', 12, 2)->nullable();
            $table->string('status')->default('new');
            $table->string('priority')->default('medium');
            $table->text('notes')->nullable();
            $table->timestamp('last_contacted_at')->nullable();
            $table->timestamp('next_follow_up_at')->nullable();
            $table->timestamp('converted_at')->nullable();
            $table->string('lost_reason')->nullable();
            $table->boolean('automation_enabled')->default(false);
            $table->timestamps();

            $table->index(['organization_id', 'status']);
            $table->index(['organization_id', 'source']);
            $table->index(['organization_id', 'phone']);
            $table->index(['organization_id', 'next_follow_up_at']);
            $table->index(['organization_id', 'created_at']);
            $table->index(['organization_id', 'priority']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
