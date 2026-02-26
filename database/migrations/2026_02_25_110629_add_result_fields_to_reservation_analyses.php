<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('reservation_analyses', function (Blueprint $table) {
            $table->string('result_value')->nullable()->after('status');
            $table->string('unit')->nullable()->after('result_value');
            $table->text('reference_range')->nullable()->after('unit');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('reservation_analyses', function (Blueprint $table) {
            $table->dropColumn(['result_value', 'unit', 'reference_range']);
        });
    }
};
