<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Pole extends Model
{
    use HasFactory;

    protected $fillable = ['nom'];

    /**
     * Un pôle contient plusieurs groupes de stagiaires.
     */
    public function groupes()
    {
        return $this->hasMany(Groupe::class);
    }

    /**
     * Un pôle peut avoir plusieurs gestionnaires associés.
     */
    public function gestionnaires()
    {
        return $this->hasMany(User::class);
    }
}
