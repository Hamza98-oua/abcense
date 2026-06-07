<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Science extends Model
{
    use HasFactory;

    protected $fillable = ['nom'];

    public function modules()
    {
        return $this->hasMany(Module::class);
    }

    public function groupeSciences()
    {
        return $this->hasMany(GroupeScience::class);
    }
}

