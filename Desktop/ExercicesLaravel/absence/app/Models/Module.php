<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Module extends Model
{
    use HasFactory;

    protected $fillable = ['nom', 'science_id'];

    public function science()
    {
        return $this->belongsTo(Science::class);
    }

    public function groupes()
    {
        return $this->belongsToMany(Groupe::class, 'groupe_modules', 'module_id', 'groupe_id');
    }

    public function groupeModules()
    {
        return $this->hasMany(GroupeModule::class);
    }

    public function formateurs()
    {
        return $this->belongsToMany(User::class, 'formateur_module', 'module_id', 'formateur_id');
    }
}


