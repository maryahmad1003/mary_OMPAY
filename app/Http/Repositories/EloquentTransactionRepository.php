<?php

namespace App\Http\Repositories;

use App\Models\Transaction;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EloquentTransactionRepository implements TransactionRepositoryInterface
{
    public function all(): Collection
    {
        return Transaction::all();
    }

    public function find(string $id): ?Transaction
    {
        return Transaction::find($id);
    }

    public function create(array $data): Transaction
    {
        return Transaction::create($data);
    }

    public function update(string $id, array $data): ?Transaction
    {
        $transaction = $this->find($id);
        if (!$transaction) {
            return null;
        }
        $transaction->update($data);
        return $transaction;
    }

    public function delete(string $id): bool
    {
        $transaction = $this->find($id);
        if (!$transaction) {
            return false;
        }
        return (bool) $transaction->delete();
    }

    public function paginateForCompte(string $compteId, int $perPage = 15): LengthAwarePaginator
    {
        return Transaction::where('compte_source_id', $compteId)
            ->orWhere('compte_destination_id', $compteId)
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }
}
