<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('api_requests', function (Blueprint $table) {
            $table->id();
            $table->string('method');
            $table->string('endpoint');
            $table->string('status_code');
            $table->string('error_type')->nullable();
            $table->unsignedInteger('response_time_ms');
            $table->timestamp('recorded_at')->useCurrent();
            $table->timestamps();
            $table->index(['endpoint', 'recorded_at']);
            $table->index('status_code');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('api_requests');
    }
};

