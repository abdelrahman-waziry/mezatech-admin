<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('trade_in_requests', function (Blueprint $table) {
            $table->json('condition_answers')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('trade_in_requests', function (Blueprint $table) {
            $table->dropColumn('condition_answers');
        });
    }
};
