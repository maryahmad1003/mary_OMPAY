<?php

namespace App\Http\Repositories;

use App\Models\Compte;
use Illuminate\Database\Eloquent\Collection;

class EloquentCompteRepository implements CompteRepositoryInterface
{
    public function all(): Collection
    {
        return Compte::all();
    }

    public function find(string $id): ?Compte
    {
        return Compte::find($id);
    }

    public function create(array $data): Compte
    {
        return Compte::create($data);
    }

    public function update(string $id, array $data): ?Compte
    {
        $compte = $this->find($id);
        if (!$compte) {
            return null;
        }
        $compte->fill($data);
        if ($compte->save()) {
            return $compte;
        }
        return null;
    }

    public function delete(string $id): bool
    {
        $compte = $this->find($id);
        if (!$compte) {
            return false;
        }
        return (bool) $compte->delete();
    }
}
