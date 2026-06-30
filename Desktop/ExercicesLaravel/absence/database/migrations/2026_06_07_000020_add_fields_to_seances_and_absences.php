<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('seances', function (Blueprint $table) {
            $table->tinyInteger('num_seance')->nullable()->after('date_debut');
        });

        Schema::table('absences', function (Blueprint $table) {
            $table->string('type')->default('absence')->after('seance_id'); // 'absence' ou 'retard'
            $table->boolean('autorisation_suivante')->default(false)->after('seance_id'); // autoriser à entrer à la séance suivante sans justification
        });
    }

    public function down(): void
    {
        Schema::table('seances', function (Blueprint $table) {
            $table->dropColumn('num_seance');
        });

        Schema::table('absences', function (Blueprint $table) {
            $table->dropColumn(['type', 'autorisation_suivante']);
        });
    }
};
