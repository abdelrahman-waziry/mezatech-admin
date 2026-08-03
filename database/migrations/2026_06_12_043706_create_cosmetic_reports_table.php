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
        Schema::create('cosmetic_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('diagnostic_device_id')->constrained('diagnostic_devices')->cascadeOnDelete();
            $table->timestamp('timestamp')->nullable();
            $table->string('grade')->nullable();
            $table->integer('overall_score')->nullable();
            $table->integer('total_defects')->nullable();
            $table->string('color')->nullable();
            $table->string('label')->nullable();
            $table->text('description')->nullable();
            $table->json('defect_summary')->nullable();
            $table->json('image_scores')->nullable();
            $table->json('images')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cosmetic_reports');
    }
};
