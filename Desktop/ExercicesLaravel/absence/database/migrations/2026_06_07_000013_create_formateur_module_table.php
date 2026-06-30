<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('formateur_module', function (Blueprint $table) {
            $table->foreignId('formateur_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('module_id')->constrained('modules')->cascadeOnDelete();
            $table->primary(['formateur_id', 'module_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('formateur_module');
    }
};
