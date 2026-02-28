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
        Schema::table('reservation_analyses', function (Blueprint $blueprint) {
            $blueprint->string('clinical_status')->nullable()->after('status'); // Normal, Low, High, Critical
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reservation_analyses', function (Blueprint $blueprint) {
            $blueprint->dropColumn('clinical_status');
        });
    }
};
