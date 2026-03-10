<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        \Illuminate\Support\Facades\DB::statement("ALTER TABLE trade_in_requests MODIFY COLUMN status ENUM('pending', 'accepted', 'rejected', 'confirmed') DEFAULT 'pending'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        \Illuminate\Support\Facades\DB::table('trade_in_requests')
            ->where('status', 'confirmed')
            ->update(['status' => 'pending']);

        \Illuminate\Support\Facades\DB::statement("ALTER TABLE trade_in_requests MODIFY COLUMN status ENUM('pending', 'accepted', 'rejected') DEFAULT 'pending'");
    }
};
