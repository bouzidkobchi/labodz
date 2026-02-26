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
        Schema::table('analyses', function (Blueprint $blueprint) {
            $blueprint->decimal('min_critical', 10, 3)->nullable()->after('unit');
            $blueprint->decimal('max_critical', 10, 3)->nullable()->after('min_critical');
            $blueprint->text('critical_instructions')->nullable()->after('max_critical');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('analyses', function (Blueprint $blueprint) {
            $blueprint->dropColumn(['min_critical', 'max_critical', 'critical_instructions']);
        });
    }
};
