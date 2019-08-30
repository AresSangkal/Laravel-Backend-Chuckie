<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDeathnoticesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('deathlists', function (Blueprint $table) {
            $table->increments('id');
            $table->string('firstname');
            $table->string('town');
            $table->string('county');
            $table->string('published');
            $table->string('finaldatelater');
            $table->string('index');
            $table->string('chdeathtime');
            $table->string('lastname');
            $table->string('neename');
            $table->string('aheadtowncounty');
            $table->string('image');
            $table->string('resta');
            $table->string('restb');
            $table->string('restc');
            $table->string('chtype')->nullable();
            $table->string('chlocname')->nullable();
            $table->string('chlocaltname')->nullable();
            $table->string('chzoom')->nullable();
            $table->string('chlocaddr')->nullable();
            $table->string('chremark')->nullable();
            $table->string('chlat')->nullable();
            $table->string('chlon')->nullable();
            $table->string('chloccounty')->nullable();
            $table->string('chloctown')->nullable();
            $table->string('chloccatname')->nullable();
            $table->string('chloccountyid')->nullable();
            $table->string('chloctownid')->nullable();
            $table->string('cetype')->nullable();
            $table->string('celocname')->nullable();
            $table->string('celocaltname')->nullable();
            $table->string('cezoom')->nullable();
            $table->string('celocaddr')->nullable();
            $table->string('ceremark')->nullable();
            $table->string('celat')->nullable();
            $table->string('celon')->nullable();
            $table->string('celoccounty')->nullable();
            $table->string('celoctown')->nullable();
            $table->string('celoccatname')->nullable();
            $table->string('celoccountyid')->nullable();
            $table->string('celoctownid')->nullable();
            $table->string('deathdate')->nullable();
            $table->string('description')->nullable();
            $table->string('towncounty')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('deathlists');
    }
}
