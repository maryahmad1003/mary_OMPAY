<?php

namespace App\Http\Services;

use App\Http\Repositories\TransactionRepositoryInterface;
use App\Http\Repositories\CompteRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TransactionService implements TransactionServiceInterface
{
    protected TransactionRepositoryInterface $repo;
    protected CompteRepositoryInterface $compteRepo;

    public function __construct(TransactionRepositoryInterface $repo, CompteRepositoryInterface $compteRepo)
    {
        $this->repo = $repo;
        $this->compteRepo = $compteRepo;
    }

    public function createTransaction(array $data)
    {
        return DB::transaction(function () use ($data) {
            $source = $this->compteRepo->find($data['compte_source_id']);
            if (! $source) {
                throw ValidationException::withMessages(['compte_source_id' => 'Compte source introuvable']);
            }

            if (in_array($data['type'], ['transfert', 'retrait'])) {
                if ($source->solde < $data['montant']) {
                    throw ValidationException::withMessages(['montant' => 'Solde insuffisant']);
                }
                $source->decrement('solde', $data['montant']);
            }

            if (! empty($data['compte_destination_id']) && in_array($data['type'], ['transfert', 'depot'])) {
                $dest = $this->compteRepo->find($data['compte_destination_id']);
                if (! $dest) {
                    throw ValidationException::withMessages(['compte_destination_id' => 'Compte destination introuvable']);
                }
                $dest->increment('solde', $data['montant']);
            }

            return $this->repo->create($data);
        });
    }

    public function getTransaction(string $id)
    {
        return $this->repo->find($id);
    }

    public function getAllTransactions()
    {
        return $this->repo->all();
    }

    public function getTransactionById(int $id)
    {
        return $this->repo->find($id);
    }

    public function updateTransaction(int $id, array $data)
    {
        return $this->repo->update($id, $data);
    }

    public function deleteTransaction(int $id)
    {
        return $this->repo->delete($id);
    }
}
