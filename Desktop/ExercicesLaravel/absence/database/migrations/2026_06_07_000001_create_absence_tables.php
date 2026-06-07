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
        // 1. Table des Pôles
        Schema::create('poles', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->timestamps();
        });

        // 2. Modification de la table users (Ajout de role et pole_id)
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('formateur'); // 'admin', 'formateur', 'gestionnaire'
            $table->foreignId('pole_id')->nullable()->constrained('poles')->nullOnDelete();
        });

        // 3. Table des Groupes
        Schema::create('groupes', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->foreignId('pole_id')->constrained('poles')->cascadeOnDelete();
            $table->timestamps();
        });

        // 4. Table Pivot Formateur <-> Groupe
        Schema::create('formateur_groupe', function (Blueprint $table) {
            $table->foreignId('formateur_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('groupe_id')->constrained('groupes')->cascadeOnDelete();
            $table->primary(['formateur_id', 'groupe_id']);
        });

        // 5. Table des Stagiaires
        Schema::create('stagiaires', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->string('prenom');
            $table->string('email')->unique();
            $table->foreignId('groupe_id')->constrained('groupes')->cascadeOnDelete();
            $table->timestamps();
        });

        // 6. Table des Séances (Chaque séance dure 2.5 heures)
        Schema::create('seances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('groupe_id')->constrained('groupes')->cascadeOnDelete();
            $table->foreignId('formateur_id')->constrained('users')->cascadeOnDelete();
            $table->dateTime('date_debut');
            $table->decimal('duree_heures', 4, 2)->default(2.50);
            $table->boolean('est_validee')->default(false); // true quand l'appel est fait et validé
            $table->timestamps();
        });

        // 7. Table des Absences
        Schema::create('absences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stagiaire_id')->constrained('stagiaires')->cascadeOnDelete();
            $table->foreignId('seance_id')->constrained('seances')->cascadeOnDelete();
            $table->timestamps();
        });

        // 8. Table des Justifications
        Schema::create('justifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('absence_id')->unique()->constrained('absences')->cascadeOnDelete();
            $table->text('motif');
            $table->string('fichier_joint')->nullable(); // Chemin vers le PDF/Image
            $table->boolean('est_valide')->default(false); // Validé par le gestionnaire
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('justifications');
        Schema::dropIfExists('absences');
        Schema::dropIfExists('seances');
        Schema::dropIfExists('stagiaires');
        Schema::dropIfExists('formateur_groupe');
        Schema::dropIfExists('groupes');

        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['pole_id']);
            $table->dropColumn(['role', 'pole_id']);
        });

        Schema::dropIfExists('poles');
    }
};
