<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasUuids;

    /**
     * Use string UUID as primary key
     */
    protected $keyType = 'string';

    public $incrementing = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nom',
        'prenom',
        'name',
        'telephone',
        'email',
        'status',
        'cni',
        'password',
        'login_token',
        'is_verified',
        'qr_code',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_verified' => 'boolean',
    ];

    /**
     * A user has one compte
     */
    public function compte()
    {
        return $this->hasOne(\App\Models\Compte::class, 'user_id', 'id');
    }

    /**
     * A user can have many operations (through comptes)
     */
    public function operations()
    {
        return $this->hasManyThrough(\App\Models\Operation::class, \App\Models\Compte::class, 'user_id', 'compte_id', 'id', 'id');
    }

    public function liensConnexion()
    {
        return $this->hasMany(\App\Models\LienConnexion::class);
    }

    public function codesSecrets()
    {
        return $this->hasMany(\App\Models\CodeSecret::class);
    }
}
