<?php

namespace App\Imports;

use App\Models\Absence;
use App\Models\Stagiaire;
use App\Models\Seance;
use App\Models\Groupe;
use App\Models\Pole;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\Auth;

class AbsencesImport implements ToModel, WithHeadingRow
{
    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        // 1. Rechercher le stagiaire par son CEF (clé primaire attendue)
        // Excel heading exact côté utilisateur : CEF
        $cef = null;
        if (isset($row['CEF'])) {
            $cef = trim((string) $row['CEF']);
        } elseif (isset($row['cef'])) {
            $cef = trim((string) $row['cef']);
        } elseif (isset($row['Code'])) {
            $cef = trim((string) $row['Code']);
        }

        // Fallback: si le fichier ne contient pas CEF mais contient email, on ne peut pas identifier correctement.
        // Donc on s'arrête.
        if (!$cef) {
            return null;
        }


        $stagiaire = Stagiaire::where('cef', $cef)->first();

        // Résolution du groupe_id
        $groupeId = null;
        if (isset($row['groupe_id']) && !empty($row['groupe_id'])) {
            $groupeId = (int) $row['groupe_id'];
        } else {
            // Rechercher par nom de groupe (essaye différents en-têtes possibles)
            $groupeNom = null;
            foreach (['groupe_nom', 'nom_groupe', 'groupe', 'classe'] as $key) {
                if (isset($row[$key]) && !empty($row[$key])) {
                    $groupeNom = trim((string) $row[$key]);
                    break;
                }
            }

            if ($groupeNom) {
                $groupeObj = Groupe::where('nom', $groupeNom)->first();
                if ($groupeObj) {
                    $groupeId = $groupeObj->id;
                } else {
                    // Si le groupe n'existe pas encore, on le crée sous le pôle du gestionnaire connecté
                    $gestionnaire = Auth::user();
                    $poleId = $gestionnaire ? $gestionnaire->pole_id : null;
                    if (!$poleId) {
                        $firstPole = Pole::first();
                        $poleId = $firstPole ? $firstPole->id : null;
                    }

                    if ($poleId) {
                        $groupeObj = Groupe::create([
                            'nom' => $groupeNom,
                            'pole_id' => $poleId,
                        ]);
                        $groupeId = $groupeObj->id;
                    }
                }
            }
        }

        // Fallback si aucun groupe résolu pour un nouveau stagiaire
        if (!$groupeId && !$stagiaire) {
            $gestionnaire = Auth::user();
            if ($gestionnaire && $gestionnaire->pole_id) {
                $firstGroupe = Groupe::where('pole_id', $gestionnaire->pole_id)->first();
                if ($firstGroupe) {
                    $groupeId = $firstGroupe->id;
                }
            }
            if (!$groupeId) {
                $firstGroupe = Groupe::first();
                if ($firstGroupe) {
                    $groupeId = $firstGroupe->id;
                }
            }
        }

        if (!$stagiaire) {
            // Si le stagiaire n'est pas trouvé et qu'on n'a pas pu résoudre de groupe_id, on ignore la ligne
            if (!$groupeId) {
                return null;
            }

            // Génération de valeurs par défaut saines pour éviter les contraintes NOT NULL de la BDD
            $nom = isset($row['nom']) && !empty($row['nom']) ? trim((string) $row['nom']) : 'Stagiaire';
            $prenom = isset($row['prenom']) && !empty($row['prenom']) ? trim((string) $row['prenom']) : 'Nouveau';
            
            // Génération d'un email unique
            $email = isset($row['email']) && !empty($row['email']) ? trim((string) $row['email']) : (strtolower($cef) . '@absence.com');
            $baseEmail = $email;
            $counter = 1;
            while (Stagiaire::where('email', $email)->exists()) {
                $parts = explode('@', $baseEmail);
                $email = $parts[0] . $counter . '@' . ($parts[1] ?? 'absence.com');
                $counter++;
            }

            $stagiaire = new Stagiaire([
                'cef' => $cef,
                'nom' => $nom,
                'prenom' => $prenom,
                'email' => $email,
                'image' => isset($row['image']) && !empty($row['image']) ? trim((string) $row['image']) : null,
                'groupe_id' => $groupeId,
            ]);

            $stagiaire->save();
        } else {
            // Mettre à jour les infos du stagiaire existant si présentes dans l'Excel
            if (isset($row['nom']) && !empty($row['nom'])) {
                $stagiaire->nom = trim((string) $row['nom']);
            }
            if (isset($row['prenom']) && !empty($row['prenom'])) {
                $stagiaire->prenom = trim((string) $row['prenom']);
            }
            if (isset($row['email']) && !empty($row['email'])) {
                $newEmail = trim((string) $row['email']);
                if ($newEmail !== $stagiaire->email && !Stagiaire::where('email', $newEmail)->exists()) {
                    $stagiaire->email = $newEmail;
                }
            }
            if (isset($row['image']) && !empty($row['image'])) {
                $stagiaire->image = trim((string) $row['image']);
            }
            if ($groupeId) {
                $stagiaire->groupe_id = $groupeId;
            }

            $stagiaire->save();
        }


        // 3. Valider et parser la date de la séance
        $dateStr = isset($row['date_debut_seance']) ? trim($row['date_debut_seance']) : null;
        if (!$dateStr) {
            return null;
        }

        try {
            $dateDebut = Carbon::parse($dateStr);
        } catch (\Exception $e) {
            return null;
        }

        // 3. Durée de la séance (par défaut 2.5)
        $duree = isset($row['duree_heures']) ? (float) $row['duree_heures'] : 2.50;

        // 4. Rechercher ou créer la séance associée au groupe du stagiaire
        // On associe la séance au premier formateur du groupe ou à l'utilisateur connecté
        // NOTE: module/science ne sont pas fournis via Excel dans ton import actuel.
        // On les laisse donc null.
        $seance = Seance::firstOrCreate(
            [
                'groupe_id' => $stagiaire->groupe_id,
                'date_debut' => $dateDebut,
            ],
            [
                'formateur_id' => $stagiaire->groupe->formateurs()->first()?->id ?? Auth::id() ?? 1,
                'duree_heures' => $duree,
                'est_validee' => true, // Importée administrativement = automatiquement validée
            ]
        );

        // 5. Créer l'absence de manière unique (évite les doublons si ré-import)
        $absenceExistante = Absence::where('stagiaire_id', $stagiaire->id)
            ->where('seance_id', $seance->id)
            ->first();

        if ($absenceExistante) {
            return null;
        }

        return new Absence([
            'stagiaire_id' => $stagiaire->id,
            'seance_id' => $seance->id,
        ]);
    }
}
