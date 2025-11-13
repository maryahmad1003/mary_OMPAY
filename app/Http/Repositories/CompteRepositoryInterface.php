<?php

namespace App\Http\Repositories;

use Illuminate\Database\Eloquent\Collection;
use App\Models\Compte;

interface CompteRepositoryInterface
{
    public function all(): Collection;
    public function find(string $id): ?Compte;
    public function findByNumero(string $numero): ?Compte;
    public function create(array $data): Compte;
    public function update(string $id, array $data): ?Compte;
    public function delete(string $id): bool;
}
