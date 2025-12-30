<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trade_in_journeys', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('device_name')->nullable();
            $table->unsignedBigInteger('variant_id')->nullable();
            $table->string('serial_number')->nullable();
            $table->boolean('is_functioning')->default(false);
            $table->tinyInteger('condition_rating')->nullable();
            $table->json('parts_status')->nullable();
            $table->json('survey_payload')->nullable();
            $table->decimal('estimated_price', 14, 2)->nullable();
            $table->string('currency', 3)->default('EGP');
            $table->json('pricing_context')->nullable();
            $table->enum('status', ['pending', 'quoted', 'accepted', 'rejected'])->default('pending');
            $table->text('notes')->nullable();
            $table->dateTime('logged_at')->nullable();
            $table->dateTime('processed_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'variant_id']);
            $table->index('status');
            $table->index('logged_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trade_in_journeys');
    }
};

