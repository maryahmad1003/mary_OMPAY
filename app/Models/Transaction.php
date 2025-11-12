<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Transaction extends Model
{
    use HasFactory, HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'compte_source_id',
        'compte_destination_id',
        'type',
        'montant',
        'status',
        'reference',
        'description',
        'mode',
    ];

    protected $casts = [
        'montant' => 'decimal:2',
    ];

    protected static function booted()
    {
        static::creating(function ($tx) {
            if (empty($tx->reference)) {
                $tx->reference = self::generateReference();
            }
        });
    }

    public static function generateReference(): string
    {
        return 'TX' . now()->format('YmdHis') . rand(1000, 9999);
    }

    public function compteSource()
    {
        return $this->belongsTo(Compte::class, 'compte_source_id');
    }

    public function compteDestination()
    {
        return $this->belongsTo(Compte::class, 'compte_destination_id');
    }
}
