<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'pole_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // --- Relations Eloquent ---

    /**
     * Un utilisateur (gestionnaire) appartient à un pôle de compétences.
     */
    public function pole()
    {
        return $this->belongsTo(Pole::class);
    }

    /**
     * Un utilisateur (formateur) enseigne dans plusieurs groupes.
     */
    public function groupes()
    {
        return $this->belongsToMany(Groupe::class, 'formateur_groupe', 'formateur_id', 'groupe_id');
    }

    /**
     * Un formateur peut etre autorise sur plusieurs modules.
     */
    public function modules()
    {
        return $this->belongsToMany(Module::class, 'formateur_module', 'formateur_id', 'module_id');
    }

    /**
     * Un utilisateur (formateur) anime plusieurs séances.
     */
    public function seances()
    {
        return $this->hasMany(Seance::class, 'formateur_id');
    }

    // --- Helpers de Rôle ---

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isFormateur(): bool
    {
        return $this->role === 'formateur';
    }

    public function isGestionnaire(): bool
    {
        return $this->role === 'gestionnaire';
    }
}
