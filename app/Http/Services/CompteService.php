<?php

namespace App\Http\Services;

use App\Http\Repositories\CompteRepositoryInterface;

class CompteService implements CompteServiceInterface
{
    protected CompteRepositoryInterface $repo;

    public function __construct(CompteRepositoryInterface $repo)
    {
        $this->repo = $repo;
    }

    public function createCompte(array $data)
    {
        $data['solde'] = $data['solde'] ?? 0;
        $data['status'] = $data['status'] ?? 'actif';
        return $this->repo->create($data);
    }

    public function getCompte(string $id)
    {
        return $this->repo->find($id);
    }

    public function closeCompte(string $id): bool
    {
        $updated = $this->repo->update($id, ['status' => 'suspendu']);
        return (bool) $updated;
    }

    public function getAllComptes()
    {
        return $this->repo->all();
    }

    public function getCompteById(string $id)
    {
        return $this->repo->find($id);
    }

    public function updateCompte(string $id, array $data)
    {
        return $this->repo->update($id, $data);
    }

    public function deleteCompte(string $id)
    {
        return $this->repo->delete($id);
    }

    public function getCompteByNumero(string $numero)
    {
        return $this->repo->findByNumero($numero);
    }
}
