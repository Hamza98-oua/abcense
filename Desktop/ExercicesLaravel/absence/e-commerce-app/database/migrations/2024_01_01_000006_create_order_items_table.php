<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('order_id')
                  ->constrained()
                  ->cascadeOnDelete();
            $table->foreignUuid('product_id')
                  ->constrained()
                  ->restrictOnDelete();
            $table->foreignUuid('variant_id')
                  ->nullable()
                  ->constrained('product_variants')
                  ->nullOnDelete();

            // Snapshot des données produit au moment de la commande
            $table->string('product_name');
            $table->string('variant_label')->nullable(); // Ex: "Taille: XL / Couleur: Noir"
            $table->decimal('unit_price', 10, 2);
            $table->unsignedInteger('quantity');
            $table->decimal('subtotal', 10, 2); // unit_price * quantity

            $table->timestamps();

            $table->index('order_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};