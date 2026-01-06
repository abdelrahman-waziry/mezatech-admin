<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('analytics_events', function (Blueprint $table) {
            $table->id();
            $table->string('event_name')->index();
            $table->string('user_id')->nullable()->index();
            $table->string('brand')->index();
            $table->string('model')->index();
            $table->string('condition');
            $table->decimal('quoted_price', 10, 2)->nullable();
            $table->string('country');
            $table->string('city')->index();
            $table->string('area')->nullable();
            $table->string('district')->nullable();
            $table->string('device_brand');
            $table->string('device_model');
            $table->string('device_os_version');
            $table->timestamp('created_at')->useCurrent()->index();
            // We don't use updated_at for append-only log
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('analytics_events');
    }
};
