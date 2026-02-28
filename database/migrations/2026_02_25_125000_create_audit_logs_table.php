<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->nullableMorphs('user');
            $blueprint->string('action'); // created, updated, deleted, validated, etc.
            $blueprint->string('model_type');
            $blueprint->unsignedBigInteger('model_id');
            $blueprint->json('old_values')->nullable();
            $blueprint->json('new_values')->nullable();
            $blueprint->string('ip_address', 45)->nullable();
            $blueprint->text('user_agent')->nullable();
            $blueprint->timestamps();

            $blueprint->index(['model_type', 'model_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
