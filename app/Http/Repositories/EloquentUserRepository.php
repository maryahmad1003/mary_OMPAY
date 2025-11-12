<?php

namespace App\Http\Repositories;

use App\Models\User;

class EloquentUserRepository implements UserRepositoryInterface
{
    public function findById(string $id)
    {
        return User::find($id);
    }

    public function findByTelephone(string $telephone)
    {
        return User::where('telephone', $telephone)->first();
    }

    public function create(array $data)
    {
        return User::create($data);
    }
}
