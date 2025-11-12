<?php

namespace App\Http\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use App\Models\Transaction;

interface TransactionRepositoryInterface
{
    public function all(): Collection;
    public function find(string $id): ?Transaction;
    public function create(array $data): Transaction;
    public function update(string $id, array $data): ?Transaction;
    public function delete(string $id): bool;
    public function paginateForCompte(string $compteId, int $perPage = 15): LengthAwarePaginator;
}
