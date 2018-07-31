<?php

namespace App\Request;


class AddCurrencyRequest implements Contracts\AddCurrencyRequest
{
    private $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function getName(): string
    {
        return $this->name;
    }

}