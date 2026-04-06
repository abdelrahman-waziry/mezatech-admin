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
        Schema::create('repair_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('repair_subcategory_id')->constrained('repair_subcategories')->cascadeOnDelete();
            $table->string('product_number')->nullable();
            $table->string('model');
            $table->decimal('price', 10, 2);
            $table->decimal('discount', 5, 2)->default(0);
            $table->decimal('price_after_discount', 10, 2);
            $table->string('warranty')->nullable();
            $table->string('sla')->nullable();
            $table->boolean('is_etisalat_offer')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('repair_prices');
    }
};
