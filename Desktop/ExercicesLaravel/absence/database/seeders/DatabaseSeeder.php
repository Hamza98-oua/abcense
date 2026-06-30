<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Pole;
use App\Models\Groupe;
use App\Models\Stagiaire;
use App\Models\Seance;
use App\Models\Absence;
use App\Models\Justification;
use Carbon\Carbon;
use Database\Seeders\ScienceModuleSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed sciences/modules (optionnel)
        // 1. Création des Pôles
        $poleDigital = Pole::firstOrCreate(['nom' => 'Digital & Technologie']);
        $poleCommerce = Pole::firstOrCreate(['nom' => 'Gestion & Commerce']);

        // 2. Création des Utilisateurs avec différents rôles (idempotent)
        $admin = User::firstOrCreate(
            ['email' => 'admin@absence.com'],
            ['name' => 'Directeur Admin', 'password' => Hash::make('password123'), 'role' => 'admin']
        );

        $formateurDupont = User::firstOrCreate(
            ['email' => 'dupont@absence.com'],
            ['name' => 'Jean Dupont', 'password' => Hash::make('password123'), 'role' => 'formateur']
        );

        $formateurMartin = User::firstOrCreate(
            ['email' => 'martin@absence.com'],
            ['name' => 'Alice Martin', 'password' => Hash::make('password123'), 'role' => 'formateur']
        );

        $gestionnaireDigital = User::firstOrCreate(
            ['email' => 'gestion.digital@absence.com'],
            ['name' => 'Salah El Khadi', 'password' => Hash::make('password123'), 'role' => 'gestionnaire', 'pole_id' => $poleDigital->id]
        );

        $gestionnaireCommerce = User::firstOrCreate(
            ['email' => 'gestion.commerce@absence.com'],
            ['name' => 'Karima Bennani', 'password' => Hash::make('password123'), 'role' => 'gestionnaire', 'pole_id' => $poleCommerce->id]
        );


        // 3. Création des Groupes (idempotent)
        $dev101 = Groupe::firstOrCreate(['nom' => 'DEV-101', 'pole_id' => $poleDigital->id]);
        $dev102 = Groupe::firstOrCreate(['nom' => 'DEV-102', 'pole_id' => $poleDigital->id]);
        $mkt101 = Groupe::firstOrCreate(['nom' => 'MKT-101', 'pole_id' => $poleCommerce->id]);

        // Seed sciences/modules apres creation des groupes pour associer le programme attendu.
        $this->call(ScienceModuleSeeder::class);
        $modulesProgramme = \App\Models\Module::with('science')->orderBy('id')->get()->values();

        // 4. Assignation des Formateurs aux Groupes
        $formateurDupont->groupes()->syncWithoutDetaching([$dev101->id, $dev102->id]);
        $formateurMartin->groupes()->syncWithoutDetaching([$mkt101->id]);

        // 5. Création des Stagiaires
        // Groupe DEV-102 (Pôle Digital)
        $yassine = Stagiaire::firstOrCreate(
            ['email' => 'yassine.alami@gmail.com'],
            ['nom' => 'ALAMI', 'prenom' => 'Yassine', 'cef' => 'CEF-YAS-0001', 'groupe_id' => $dev102->id]
        );
        $sofia = Stagiaire::firstOrCreate(
            ['email' => 'sofia.benj@gmail.com'],
            ['nom' => 'BENJELLOUN', 'prenom' => 'Sofia', 'cef' => 'CEF-SOF-0002', 'groupe_id' => $dev102->id]
        );
        $omar = Stagiaire::firstOrCreate(
            ['email' => 'omar.cherradi@gmail.com'],
            ['nom' => 'CHERRADI', 'prenom' => 'Omar', 'cef' => 'CEF-OMA-0003', 'groupe_id' => $dev102->id]
        );
        $kenza = Stagiaire::firstOrCreate(
            ['email' => 'kenza.daoudi@gmail.com'],
            ['nom' => 'DAOUDI', 'prenom' => 'Kenza', 'cef' => 'CEF-KEN-0004', 'groupe_id' => $dev102->id]
        );
        $reda = Stagiaire::firstOrCreate(
            ['email' => 'reda.essaidi@gmail.com'],
            ['nom' => 'ESSAIDI', 'prenom' => 'Reda', 'cef' => 'CEF-RED-0005', 'groupe_id' => $dev102->id]
        );

        // Groupe DEV-101 (Pôle Digital)
        Stagiaire::firstOrCreate(
            ['email' => 'amine.kabiri@gmail.com'],
            ['nom' => 'KABIRI', 'prenom' => 'Amine', 'cef' => 'CEF-AMI-0006', 'groupe_id' => $dev101->id]
        );
        Stagiaire::firstOrCreate(
            ['email' => 'sanaa.idrissi@gmail.com'],
            ['nom' => 'IDRISSI', 'prenom' => 'Sanaa', 'cef' => 'CEF-SAN-0007', 'groupe_id' => $dev101->id]
        );

        // Groupe MKT-101 (Pôle Commerce)
        Stagiaire::firstOrCreate(
            ['email' => 'laila.taraji@gmail.com'],
            ['nom' => 'TARAJI', 'prenom' => 'Laila', 'cef' => 'CEF-LAI-0008', 'groupe_id' => $mkt101->id]
        );



        // 6. Création des Séances pour DEV-102 (animées par Dupont)
        // Nous allons générer 5 séances sur les 5 derniers jours pour tester la logique de décrochage.
        $maintenant = Carbon::now();

        $seances = [];
        for ($i = 4; $i >= 0; $i--) {
            // Séance de 2.5h (ex: de 08:30 à 11:00)
            $dateDebut = Carbon::now()->subDays($i)->setTime(8, 30, 0);
            $module = $modulesProgramme->isNotEmpty()
                ? $modulesProgramme->get((4 - $i) % $modulesProgramme->count())
                : null;
            
            $seances[] = Seance::create([
                'groupe_id' => $dev102->id,
                'formateur_id' => $formateurDupont->id,
                'science_id' => $module?->science_id,
                'module_id' => $module?->id,
                'date_debut' => $dateDebut,
                'num_seance' => 1, // Toutes les séances de base sont à 8h30 (Séance 1)
                'duree_heures' => 2.50,
                'est_validee' => true, // Validées par défaut pour le test
            ]);
        }

        // Créer une Séance 2 pour aujourd'hui (pour tester le contrôle d'accès en séance 2/3)
        $dateDebutSeance2 = Carbon::now()->setTime(11, 0, 0);
        $seance2Today = Seance::create([
            'groupe_id' => $dev102->id,
            'formateur_id' => $formateurDupont->id,
            'science_id' => $seances[4]->science_id,
            'module_id' => $seances[4]->module_id,
            'date_debut' => $dateDebutSeance2,
            'num_seance' => 2,
            'duree_heures' => 2.50,
            'est_validee' => true,
        ]);

        // Séance 0: Il y a 4 jours
        // Séance 1: Il y a 3 jours
        // Séance 2: Il y a 2 jours
        // Séance 3: Il y a 1 jour (Hier)
        // Séance 4: Aujourd'hui (Séance 1)

        // 7. Création des Absences pour tester le décrochage :
        
        // * Yassine ALAMI : 0 absence (Présent partout)
        // -> total_heures_absence = 0h
        // -> heures_absences_successives = 0h (Vert)
        
        // * Sofia BENJELLOUN : Absente aux 2 dernières séances (Séance 3 et Séance 4)
        // -> total_heures_absence = 5h
        // -> heures_absences_successives = 5h (Orange / Alerte modérée)
        Absence::create(['stagiaire_id' => $sofia->id, 'seance_id' => $seances[3]->id]);
        Absence::create(['stagiaire_id' => $sofia->id, 'seance_id' => $seances[4]->id]);

        // * Omar CHERRADI : Absent aux 3 dernières séances (Séance 2, Séance 3 et Séance 4) + la séance 2 d'aujourd'hui
        // -> total_heures_absence = 10h
        // -> heures_absences_successives = 10h (Rouge / Décrochage critique)
        Absence::create(['stagiaire_id' => $omar->id, 'seance_id' => $seances[2]->id]);
        Absence::create(['stagiaire_id' => $omar->id, 'seance_id' => $seances[3]->id]);
        Absence::create(['stagiaire_id' => $omar->id, 'seance_id' => $seances[4]->id]);
        Absence::create(['stagiaire_id' => $omar->id, 'seance_id' => $seance2Today->id]);

        // * Kenza DAOUDI : Absente à la Séance 0 (il y a 4 jours) et Séance 2 (il y a 2 jours), 
        // mais PRÉSENTE à la Séance 3 (Hier) et Séance 4 (Aujourd'hui).
        // -> total_heures_absence = 5h
        // -> heures_absences_successives = 0h (car présente aux séances les plus récentes) (Vert)
        $abs1 = Absence::create(['stagiaire_id' => $kenza->id, 'seance_id' => $seances[0]->id]);
        $abs2 = Absence::create(['stagiaire_id' => $kenza->id, 'seance_id' => $seances[2]->id]);

        // 8. Création des Justifications
        // Justification validée pour Kenza pour sa première absence (il y a 4 jours)
        Justification::create([
            'absence_id' => $abs1->id,
            'motif' => 'Rendez-vous médical chez le dentiste (certificat fourni)',
            'fichier_joint' => 'justificatifs/certificat_medical.pdf',
            'est_valide' => true
        ]);

        // Justification en attente de validation pour Kenza pour sa deuxième absence
        Justification::create([
            'absence_id' => $abs2->id,
            'motif' => 'Panne de transport (RER en grève)',
            'fichier_joint' => null, // Justification textuelle simple
            'est_valide' => false
        ]);
    }
}
