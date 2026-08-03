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
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();

            // User Information
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('user_name')->nullable();
            $table->string('user_email')->nullable();
            $table->string('user_role')->nullable();

            // Session
            $table->string('session_id')->nullable();

            // Action Details
            $table->string('action');                     // e.g., 'login', 'update_price'
            $table->string('category');                   // e.g., 'authentication', 'pricing'
            $table->string('resource')->nullable();       // e.g., 'RepairPrice', 'User'
            $table->string('resource_id')->nullable();    // ID of affected resource

            // Change Tracking
            $table->json('previous_value')->nullable();
            $table->json('new_value')->nullable();

            // Result
            $table->boolean('success')->default(true);
            $table->text('error_message')->nullable();

            // Network Information
            $table->string('ip_address', 45)->nullable();
            $table->string('country')->nullable();
            $table->string('city')->nullable();
            $table->string('timezone')->nullable();
            $table->string('isp')->nullable();

            // Device Information
            $table->string('browser')->nullable();
            $table->string('browser_version')->nullable();
            $table->string('operating_system')->nullable();
            $table->string('device_type')->nullable();    // desktop, mobile, tablet
            $table->text('user_agent')->nullable();

            // Request Information
            $table->string('request_url')->nullable();
            $table->string('http_method', 10)->nullable();
            $table->unsignedSmallInteger('response_status')->nullable();
            $table->unsignedInteger('execution_time_ms')->nullable();

            // Suspicious Activity
            $table->boolean('is_suspicious')->default(false);
            $table->string('suspicious_reason')->nullable();

            // Extra Context
            $table->json('metadata')->nullable();

            $table->timestamp('created_at')->useCurrent();

            // Performance Indexes
            $table->index(['user_id', 'created_at']);
            $table->index(['action', 'created_at']);
            $table->index(['category', 'created_at']);
            $table->index('ip_address');
            $table->index(['is_suspicious', 'created_at']);
            $table->index('created_at');
            $table->index('session_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
