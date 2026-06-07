<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('category_id')
                  ->constrained()
                  ->restrictOnDelete();

            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->decimal('base_price', 10, 2);
            $table->decimal('compare_price', 10, 2)->nullable(); // Prix barré
            $table->string('sku')->unique()->nullable();
            $table->unsignedInteger('stock_qty')->default(0);
            $table->enum('status', ['draft', 'active', 'archived'])->default('draft');
            $table->json('images')->nullable();  // [{url, alt, position}]
            $table->json('meta')->nullable();    // SEO, attributs libres
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'category_id']);
            $table->index('slug');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};