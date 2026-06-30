<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Justification extends Model
{
    use HasFactory;

    protected $fillable = ['absence_id', 'motif', 'fichier_joint', 'est_valide'];

    protected $casts = [
        'est_valide' => 'boolean',
    ];

    /**
     * La justification correspond à une absence spécifique.
     */
    public function absence()
    {
        return $this->belongsTo(Absence::class);
    }
}
