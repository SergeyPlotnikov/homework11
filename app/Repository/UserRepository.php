<?php

namespace App\Repository;


use App\User;

class UserRepository implements Contracts\UserRepository
{
    public function getById(int $id): ?User
    {
        return User::find($id);
    }

}