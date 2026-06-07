<?php

namespace App\Imports;

use App\Models\Absence;
use App\Models\Stagiaire;
use App\Models\Seance;
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

        if (!$stagiaire) {
            // Si le stagiaire n'est pas trouvé, on le crée à partir de l'Excel.
            // Remarque: pour être associé à un groupe, on attend au minimum `groupe_id` ou `groupe_nom`.
            // Si aucune info de groupe n'est fournie, on ignore la ligne.
            $groupeId = null;
            if (isset($row['groupe_id']) && !empty($row['groupe_id'])) {
                $groupeId = (int) $row['groupe_id'];
            }

            if (!$groupeId) {
                return null;
            }

            $stagiaire = new Stagiaire([
                'cef' => $cef,
                'nom' => isset($row['nom']) && !empty($row['nom']) ? trim((string) $row['nom']) : null,
                'prenom' => isset($row['prenom']) && !empty($row['prenom']) ? trim((string) $row['prenom']) : null,
                'email' => isset($row['email']) && !empty($row['email']) ? trim((string) $row['email']) : null,
                'image' => isset($row['image']) && !empty($row['image']) ? trim((string) $row['image']) : null,
                'groupe_id' => $groupeId,
            ]);

            $stagiaire->save();
        } else {
            // 2. (Optionnel) Mettre à jour les infos stagiaire depuis Excel
            // - noms/champs non utilisés pour l'absence, mais utiles pour l'UI
            if (isset($row['nom']) && !empty($row['nom'])) {
                $stagiaire->nom = trim((string) $row['nom']);
            }
            if (isset($row['prenom']) && !empty($row['prenom'])) {
                $stagiaire->prenom = trim((string) $row['prenom']);
            }
            if (isset($row['email']) && !empty($row['email'])) {
                $stagiaire->email = trim((string) $row['email']);
            }
            if (isset($row['image']) && !empty($row['image'])) {
                $stagiaire->image = trim((string) $row['image']);
            }
            if (isset($row['groupe_id']) && !empty($row['groupe_id'])) {
                $stagiaire->groupe_id = (int) $row['groupe_id'];
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
