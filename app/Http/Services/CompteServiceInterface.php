<?php

namespace App\Http\Services;

interface CompteServiceInterface
{
    public function createCompte(array $data);
    public function getAllComptes();
    public function getCompteById(string $id);
    public function updateCompte(string $id, array $data);
    public function deleteCompte(string $id);
    public function getCompte(string $id);
    public function closeCompte(string $id): bool;
}
