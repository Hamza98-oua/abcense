<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('sciences', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->timestamps();
        });

        Schema::create('modules', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->foreignId('science_id')->constrained('sciences')->cascadeOnDelete();
            $table->timestamps();
        });

        // Programme attendu par groupe (niveau science)
        Schema::create('groupe_sciences', function (Blueprint $table) {
            $table->foreignId('groupe_id')->constrained('groupes')->cascadeOnDelete();
            $table->foreignId('science_id')->constrained('sciences')->cascadeOnDelete();
            $table->primary(['groupe_id', 'science_id']);
        });

        // Programme attendu par groupe (niveau module)
        Schema::create('groupe_modules', function (Blueprint $table) {
            $table->foreignId('groupe_id')->constrained('groupes')->cascadeOnDelete();
            $table->foreignId('module_id')->constrained('modules')->cascadeOnDelete();
            $table->primary(['groupe_id', 'module_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('groupe_modules');
        Schema::dropIfExists('groupe_sciences');
        Schema::dropIfExists('modules');
        Schema::dropIfExists('sciences');
    }
};

