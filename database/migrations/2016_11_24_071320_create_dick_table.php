<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDickTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dick', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->unique();
            $table->integer('body_id')->index();
            $table->integer('lengh');
            $table->integer('search_cnt');
            $table->integer('appear_cnt');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('dick');
    }
}
