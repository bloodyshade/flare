<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMaps extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('maps', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('character_id')->unsigned();
            $table->foreign('character_id')
                ->references('id')->on('characters');
            $table->integer('position_x')->nullable()->default(0);
            $table->integer('position_y')->nullable()->default(0);
            $table->integer('character_position_x')->nullable()->default(32);
            $table->integer('character_position_y')->nullable()->default(32);
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
        Schema::dropIfExists('maps');
    }
}
