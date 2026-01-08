<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRelaysTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('relays', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->longText('webhook_url');
            $table->bigInteger('user_id');
            $table->timestamps();
        });

        Schema::create('relay_logs', function (Blueprint $table) {
            $table->id();
            $table->longText('payload');
            $table->bigInteger('relay_id');
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
        Schema::dropIfExists('relays');
        Schema::dropIfExists('relay_logss');
    }
}
