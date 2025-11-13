<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Compte extends Model
{
    use HasFactory, HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'numero_compte',
        'user_id',
        'solde',
        'status',
        'type',
        'code_marchand',
        'numero_client',
        'login',
        'password',
    ];

    protected $casts = [
        'solde' => 'decimal:2',
        'password' =>'hashed',
        'user_id' => 'string',
    ];

    protected static function booted()
    {
        static::creating(function ($compte) {
            if (empty($compte->numero_compte)) {
                $compte->numero_compte = self::generateNumero();
            }
            if ($compte->type === 'marchand' && empty($compte->code_marchand)) {
                $compte->code_marchand = self::generateCodeMarchand();
            }
        });
    }

    public static function generateNumero(): string
    {
        return 'ACC' . now()->format('YmdHis') . rand(1000, 9999);
    }

    public static function generateCodeMarchand(): string
    {
        return 'MARCHAND' . now()->format('YmdHis') . rand(100, 999);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function operations()
    {
        return $this->hasMany(Operation::class, 'compte_id');
    }
}
