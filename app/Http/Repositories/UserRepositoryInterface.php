<?php

namespace App\Http\Repositories;

interface UserRepositoryInterface
{
    public function findById(string $id);
    public function findByTelephone(string $telephone);
    public function create(array $data);
}
