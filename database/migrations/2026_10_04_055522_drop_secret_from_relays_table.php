<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('relays', function (Blueprint $table) {
            $table->dropColumn('secret');
        });
    }

    public function down(): void
    {
        Schema::table('relays', function (Blueprint $table) {
            $table->longText('secret')->nullable()->after('webhook_url');
        });
    }
};
