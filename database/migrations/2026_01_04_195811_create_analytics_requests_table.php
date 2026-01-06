<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('analytics_requests', function (Blueprint $table) {
            $table->id();
            $table->uuid('request_id')->index();
            $table->string('endpoint')->index();
            $table->string('method');
            $table->integer('status');
            $table->integer('duration_ms');
            $table->string('error_type')->nullable();
            $table->string('app_source');
            $table->string('app_version');
            $table->string('device_os');
            $table->string('device_model');
            $table->string('device_network');
            $table->timestamp('created_at')->useCurrent()->index();
            // We don't use updated_at for append-only log
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('analytics_requests');
    }
};
