<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->string('name')->nullable();
            $table->string('phone');
            $table->string('email')->nullable();
            $table->string('source')->default('manual');
            $table->string('opt_in_status')->default('unknown');
            $table->timestamp('last_contacted_at')->nullable();
            $table->timestamps();

            $table->index(['organization_id', 'phone']);
            $table->unique(['organization_id', 'phone']);
        });

        Schema::create('contact_tags', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->timestamps();

            $table->unique(['organization_id', 'name']);
        });

        Schema::create('contact_contact_tag', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contact_id')->constrained()->cascadeOnDelete();
            $table->foreignId('contact_tag_id')->constrained()->cascadeOnDelete();

            $table->unique(['contact_id', 'contact_tag_id']);
        });

        Schema::create('contact_lists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index(['organization_id', 'name']);
        });

        Schema::create('contact_list_contact', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contact_list_id')->constrained()->cascadeOnDelete();
            $table->foreignId('contact_id')->constrained()->cascadeOnDelete();

            $table->unique(['contact_list_id', 'contact_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_list_contact');
        Schema::dropIfExists('contact_lists');
        Schema::dropIfExists('contact_contact_tag');
        Schema::dropIfExists('contact_tags');
        Schema::dropIfExists('contacts');
    }
};
