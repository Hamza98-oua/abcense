<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignId('user_id')
                ->constrained()
                ->restrictOnDelete(); // On conserve les commandes même si l'user est supprimé

            $table->enum('status', [
                'pending',      // Panier validé, paiement non initié
                'processing',   // Paiement en cours
                'paid',         // Paiement confirmé
                'shipped',      // Expédié
                'delivered',    // Livré
                'cancelled',    // Annulé
                'refunded',     // Remboursé
            ])->default('pending');

            $table->decimal('subtotal', 10, 2);
            $table->decimal('tax_amount', 10, 2)->default(0);
            $table->decimal('shipping_cost', 10, 2)->default(0);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->decimal('total', 10, 2);

            // Stripe
            $table->string('payment_intent_id')->nullable()->unique();
            $table->enum('payment_status', ['unpaid', 'paid', 'failed', 'refunded'])->default('unpaid');

            // Adresse figée au moment de la commande (même si l'user la modifie ensuite)
            $table->json('shipping_address');
            $table->json('billing_address')->nullable();

            $table->string('notes')->nullable();
            $table->string('tracking_number')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('shipped_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index('payment_intent_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};