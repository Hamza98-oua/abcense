<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Absence extends Model
{
    use HasFactory;

    protected $fillable = ['stagiaire_id', 'seance_id', 'type', 'autorisation_suivante'];

    protected $casts = [
        'autorisation_suivante' => 'boolean',
    ];

    /**
     * L'absence concerne un stagiaire spécifique.
     */
    public function stagiaire()
    {
        return $this->belongsTo(Stagiaire::class);
    }

    /**
     * L'absence s'est produite lors d'une séance spécifique.
     */
    public function seance()
    {
        return $this->belongsTo(Seance::class);
    }

    /**
     * Une absence peut avoir (au maximum) une justification.
     */
    public function justification()
    {
        return $this->hasOne(Justification::class);
    }
}
