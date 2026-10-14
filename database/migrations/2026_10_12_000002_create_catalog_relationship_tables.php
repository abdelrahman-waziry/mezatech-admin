<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('products', 'name')) {
            Schema::table('products', function (Blueprint $table): void {
                $table->string('name')->after('id');
                $table->string('sku')->unique()->after('name');
                $table->text('description')->nullable()->after('sku');
                $table->integer('condition')->default(0)->after('description');
                $table->text('notes')->nullable()->after('condition');
                $table->decimal('minimum_buying_price', 12, 2)->default(0)->after('notes');
                $table->decimal('waste_price', 12, 2)->default(0)->after('minimum_buying_price');
                $table->unsignedBigInteger('brand_id')->nullable()->after('waste_price');
                $table->timestamps();
                $table->foreign('brand_id')->references('id')->on('brands')->nullOnDelete();
            });
        }

        if (! Schema::hasTable('tags')) {
            Schema::create('tags', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
            });
        }

        if (! Schema::hasTable('product_tag')) {
            Schema::create('product_tag', function (Blueprint $table): void {
            $table->foreignId('product_id');
            $table->foreign('product_id')->references('id')->on('products')->cascadeOnDelete();
            $table->foreignId('tag_id')->constrained()->cascadeOnDelete();
            $table->primary(['product_id', 'tag_id']);
            });
        }

        if (! Schema::hasTable('features')) {
            Schema::create('features', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->foreignId('product_id')->nullable();
            $table->foreign('product_id')->references('id')->on('products')->nullOnDelete();
            $table->text('values_summary')->nullable();
            $table->timestamps();
            });
        }

        if (! Schema::hasTable('feature_values')) {
            Schema::create('feature_values', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('feature_id')->constrained()->cascadeOnDelete();
            $table->string('value');
            $table->string('image')->nullable();
            $table->timestamps();
            });
        }

        if (! Schema::hasTable('variants')) {
            Schema::create('variants', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('product_id');
            $table->foreign('product_id')->references('id')->on('products')->cascadeOnDelete();
            $table->string('name');
            $table->decimal('buying_price', 12, 2)->nullable();
            $table->decimal('price_before_discount', 12, 2)->nullable();
            $table->decimal('discount', 12, 2)->nullable();
            $table->decimal('price_after_discount', 12, 2)->nullable();
            $table->unsignedInteger('stock')->default(0);
            $table->timestamps();
            });
        }

        if (! Schema::hasTable('parts')) {
            Schema::create('parts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('product_id');
            $table->foreign('product_id')->references('id')->on('products')->cascadeOnDelete();
            $table->string('name');
            $table->decimal('price', 12, 2)->default(0);
            $table->unsignedInteger('type')->nullable();
            $table->unsignedInteger('condition')->nullable();
            $table->json('info')->nullable();
            $table->timestamps();
            });
        }

        if (! Schema::hasTable('variant_features')) {
            Schema::create('variant_features', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('variant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('feature_id')->constrained()->cascadeOnDelete();
            $table->foreignId('feature_value_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('variant_features');
        Schema::dropIfExists('parts');
        Schema::dropIfExists('variants');
        Schema::dropIfExists('feature_values');
        Schema::dropIfExists('features');
        Schema::dropIfExists('product_tag');
        Schema::dropIfExists('tags');
    }
};