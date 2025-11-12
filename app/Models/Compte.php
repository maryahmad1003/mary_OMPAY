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
    ];

    protected $casts = [
        'solde' => 'decimal:2',
    ];

    protected static function booted()
    {
        static::creating(function ($compte) {
            if (empty($compte->numero_compte)) {
                $compte->numero_compte = self::generateNumero();
            }
        });
    }

    public static function generateNumero(): string
    {
        // Simple account number generator: ACC + timestamp + random 4 digits
        return 'ACC' . now()->format('YmdHis') . rand(1000, 9999);
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
