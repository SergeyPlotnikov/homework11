<?php

namespace App\Request;


class CreateWalletRequest implements Contracts\CreateWalletRequest
{
    private $id;

    public function __construct(int $id)
    {
        $this->id = $id;
    }

    public function getUserId(): int
    {
        return $this->id;
    }

}