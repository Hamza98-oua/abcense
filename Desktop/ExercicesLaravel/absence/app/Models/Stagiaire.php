<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class Stagiaire extends Model
{
    use HasFactory;

    protected $fillable = ['nom', "image", 'prenom', 'email', "cef","phone", 'groupe_id'];

    /**
     * Un stagiaire appartient à un groupe.
     */
    public function groupe()
    {
        return $this->belongsTo(Groupe::class);
    }

    /**
     * Un stagiaire peut avoir plusieurs absences enregistrées.
     */
    public function absences()
    {
        return $this->hasMany(Absence::class);
    }

    // --- Logique Métier (Calculs et Indicateurs) ---

    /**
     * 1. Total des heures d'absence.
     * Somme les durées de toutes les séances manquées par le stagiaire.
     */
    public function getTotalHeuresAbsenceAttribute(): float
    {
        return (float) $this->absences()
            ->join('seances', 'absences.seance_id', '=', 'seances.id')
            ->sum('seances.duree_heures');
    }

    /**
     * Total des heures d'absence non justifiées.
     */
    public function getHeuresAbsenceNonJustifieeAttribute(): float
    {
        return (float) $this->absences()
            ->where(function ($query) {
                $query->whereDoesntHave('justification')
                    ->orWhereHas('justification', function ($q) {
                        $q->where('est_valide', false);
                    });
            })
            ->join('seances', 'absences.seance_id', '=', 'seances.id')
            ->sum('seances.duree_heures');
    }

    /**
     * Total des heures d'absence justifiées.
     */
    public function getHeuresAbsenceJustifieeAttribute(): float
    {
        return (float) $this->absences()
            ->whereHas('justification', function ($q) {
                $q->where('est_valide', true);
            })
            ->join('seances', 'absences.seance_id', '=', 'seances.id')
            ->sum('seances.duree_heures');
    }

    /**
     * 2. Indicateur de Décrochage : Nombre d'heures d'absence successives.
     * Analyse l'historique des séances validées de son groupe, du plus récent au plus ancien.
     * S'arrête à la première séance où le stagiaire n'était pas absent (considéré présent).
     */
    public function getHeuresAbsencesSuccessivesAttribute(): float
    {
        // Récupérer toutes les séances déjà validées par un formateur pour ce groupe (les plus récentes d'abord)
        $seancesValidees = Seance::where('groupe_id', $this->groupe_id)
            ->where('est_validee', true)
            ->orderBy('date_debut', 'desc')
            ->get();

        // Récupérer tous les IDs des séances manquées par ce stagiaire (en une seule fois pour éviter le problème N+1)
        $absenceSeanceIds = $this->absences()->pluck('seance_id')->toArray();

        $heuresSuccessives = 0;

        foreach ($seancesValidees as $seance) {
            if (in_array($seance->id, $absenceSeanceIds)) {
                // Le stagiaire était absent, on ajoute la durée de la séance
                $heuresSuccessives += (float) $seance->duree_heures;
            } else {
                // Le stagiaire était présent (séance validée sans absence enregistrée), on arrête le calcul !
                break;
            }
        }

        return $heuresSuccessives;
    }

    /**
     * 3. Dernière absence au format relatif.
     * Affiche par exemple : "Aujourd'hui", "Hier", "Il y a 3 jours", "Jamais absent"
     */
    public function getDerniereAbsenceRelativeAttribute(): string
    {
        // Récupérer la dernière absence enregistrée pour une séance
        $derniereAbsence = $this->absences()
            ->join('seances', 'absences.seance_id', '=', 'seances.id')
            ->orderBy('seances.date_debut', 'desc')
            ->select('seances.date_debut')
            ->first();

        if (!$derniereAbsence) {
            return "Jamais absent";
        }

        $date = Carbon::parse($derniereAbsence->date_debut);
        
        if ($date->isToday()) {
            return "Aujourd'hui";
        }
        if ($date->isYesterday()) {
            return "Hier";
        }

        // Configuration en Français pour le format relatif
        return $date->locale('fr')->diffForHumans();
    }
}
