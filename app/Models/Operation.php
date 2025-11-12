<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Operation extends Model
{
    use HasFactory, HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'compte_id',
        'type',
        'montant',
        'description',
        'destination_compte_id',
    ];

    protected $casts = [
        'montant' => 'decimal:2',
    ];

    public function compte()
    {
        return $this->belongsTo(Compte::class, 'compte_id');
    }
}
