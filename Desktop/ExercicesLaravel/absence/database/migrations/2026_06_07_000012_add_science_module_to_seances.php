<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('seances', function (Blueprint $table) {
            $table->foreignId('science_id')->nullable()->after('groupe_id')->constrained('sciences')->nullOnDelete();
            $table->foreignId('module_id')->nullable()->after('science_id')->constrained('modules')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('seances', function (Blueprint $table) {
            $table->dropForeign(['science_id']);
            $table->dropForeign(['module_id']);
            $table->dropColumn(['science_id', 'module_id']);
        });
    }
};

