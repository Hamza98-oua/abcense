<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Seance extends Model
{
    use HasFactory;

    protected $fillable = ['groupe_id', 'formateur_id', 'date_debut', 'duree_heures', 'est_validee', 'science_id', 'module_id', 'num_seance'];

    protected $casts = [
        'date_debut' => 'datetime',
        'est_validee' => 'boolean',
        'duree_heures' => 'float',
        'num_seance' => 'integer',
    ];

    /**
     * La séance est organisée pour un groupe spécifique.
     */
    public function groupe()
    {
        return $this->belongsTo(Groupe::class);
    }

    public function science()
    {
        return $this->belongsTo(Science::class);
    }

    public function module()
    {
        return $this->belongsTo(Module::class);
    }


    /**
     * La séance est animée par un formateur (qui est un User).
     */
    public function formateur()
    {
        return $this->belongsTo(User::class, 'formateur_id');
    }

    /**
     * Une séance peut enregistrer plusieurs absences de stagiaires.
     */
    public function absences()
    {
        return $this->hasMany(Absence::class);
    }

    /**
     * Accès pratique aux justifications associées aux absences de cette séance.
     * (permet le chargement de `absences.seance.justification` ou `justifications`)
     */
    public function justifications()
    {
        return $this->hasManyThrough(
            Justification::class,
            Absence::class,
            'seance_id', // FK sur absences vers seances
            'absence_id', // FK sur justifications vers absences
            'id', // PK de seances
            'id' // PK de absences
        );
    }

    /**
     * Alias pour compatibilité avec le code existant.
     * Le controller utilise `...seance.justification` (singulier).
     */
    public function justification()
    {
        return $this->justifications();
    }
}


