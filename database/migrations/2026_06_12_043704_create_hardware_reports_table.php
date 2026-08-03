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
        Schema::create('hardware_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('diagnostic_device_id')->constrained('diagnostic_devices')->cascadeOnDelete();
            $table->timestamp('timestamp')->nullable();
            $table->string('overall_status')->nullable();
            $table->string('battery_health')->nullable();
            $table->integer('cycle_count')->nullable();
            $table->json('summary')->nullable();
            $table->json('battery_data')->nullable();
            $table->json('display_data')->nullable();
            $table->json('components_data')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hardware_reports');
    }
};
