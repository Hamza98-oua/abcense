<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Ajout après 'email'
            $table->string('phone')->nullable()->after('email');
            $table->string('stripe_customer_id')->nullable()->unique()->after('phone');
            $table->json('default_address')->nullable()->after('stripe_customer_id');
            $table->enum('role', ['customer', 'admin'])->default('customer')->after('default_address');
            $table->softDeletes();

            $table->index('stripe_customer_id');
            $table->index('role');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['phone', 'stripe_customer_id', 'default_address', 'role']);
            $table->dropSoftDeletes();
        });
    }
};