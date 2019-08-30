<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNoticesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('noticeslist', function (Blueprint $table) {
            $table->increments('id');
            $table->string('firstname');
            $table->string('lastname');
            $table->string('published');
            $table->string('city');
            $table->string('town');
            $table->string('country');
            $table->string('finaldate');
            $table->string('index');
            $table->string('resta');
            $table->string('restb');
            $table->string('restc');
            $table->string('restd');
            $table->string('reste');
            $table->string('restf');
            $table->string('restg');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('noticeslist');
    }
}
