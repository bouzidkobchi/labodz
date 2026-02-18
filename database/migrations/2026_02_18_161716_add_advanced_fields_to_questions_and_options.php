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
        Schema::table('questions', function (Blueprint $table) {
            $table->foreignId('parent_question_id')->nullable()->constrained('questions')->onDelete('cascade');
            $table->foreignId('show_when_option_id')->nullable()->constrained('options')->onDelete('cascade');
            $table->boolean('is_multiple')->default(false);
            $table->string('gender_condition')->nullable(); // 'male', 'female'
            $table->integer('order')->default(0);
        });

        Schema::table('options', function (Blueprint $table) {
            $table->string('value')->nullable(); // e.g., 'YES', 'NO', 'SUGARY_DRINK'
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->dropForeign(['parent_question_id']);
            $table->dropForeign(['show_when_option_id']);
            $table->dropColumn(['parent_question_id', 'show_when_option_id', 'is_multiple', 'gender_condition', 'order']);
        });

        Schema::table('options', function (Blueprint $table) {
            $table->dropColumn('value');
        });
    }
};
