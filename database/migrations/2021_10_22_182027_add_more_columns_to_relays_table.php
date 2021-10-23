<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMoreColumnsToRelaysTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('relays', function (Blueprint $table) {
            $table->string('type')->after('name');
            $table->string('webhook_type')->after('description');
            $table->longText('secret')->after('webhook_url');
            $table->integer('status')->default(1)->after('secret');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('relays', function (Blueprint $table) {
            $table->dropColumn([
                'secret',
                'status',
                'webhook_type',
                'type'
            ]);
        });
    }
}
