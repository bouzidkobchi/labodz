<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDoctorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('doctors', function (Blueprint $col) {
            $col->id();
            $col->string('name');
            $col->string('email')->unique();
            $col->string('password');
            $col->string('specialty')->nullable();
            $col->string('phone')->nullable();
            $col->string('address')->nullable();
            $col->rememberToken();
            $col->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('doctors');
    }
}
