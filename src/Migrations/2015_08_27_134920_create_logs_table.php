<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('logs', function (Blueprint $table)
        {
            $table->increments('id');
            $table->timestamps();
            $table->boolean('read')->default(false);
            $table->string('message')->nullable();
            $table->text('context')->nullable();
            $table->integer('level');
            $table->string('level_name');
            $table->string('channel');
            $table->dateTime('generated');
            $table->text('extra')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('logs');
    }
}
