<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Groupe extends Model
{
    use HasFactory;

    protected $fillable = ['nom', 'pole_id'];

    /**
     * Un groupe appartient à un pôle de compétence.
     */
    public function pole()
    {
        return $this->belongsTo(Pole::class);
    }

    /**
     * Un groupe contient plusieurs stagiaires.
     */
    public function stagiaires()
    {
        return $this->hasMany(Stagiaire::class);
    }

    /**
     * Un groupe a plusieurs séances de cours programmées.
     */
    public function seances()
    {
        return $this->hasMany(Seance::class);
    }

    /**
     * Un groupe est assigné à plusieurs formateurs (relation Many-to-Many).
     */
    public function formateurs()
    {
        return $this->belongsToMany(User::class, 'formateur_groupe', 'groupe_id', 'formateur_id');
    }

    // Programme attendu (sciences)
    public function sciences()
    {
        return $this->belongsToMany(Science::class, 'groupe_sciences', 'groupe_id', 'science_id');
    }

    // Programme attendu (modules)
    public function modules()
    {
        return $this->belongsToMany(Module::class, 'groupe_modules', 'groupe_id', 'module_id');
    }

}

