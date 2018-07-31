<?php

namespace App\Request;


class AddLotRequest implements Contracts\AddLotRequest
{
    private $currencyId;
    private $sellerId;
    private $dateTimeOpen;
    private $dateTimeClose;
    private $price;

    public function __construct(int $currencyId, int $sellerId, int $dateTimeOpen, int $dateTimeClose, float $price)
    {
        $this->currencyId = $currencyId;
        $this->sellerId = $sellerId;
        $this->dateTimeOpen = $dateTimeOpen;
        $this->dateTimeClose = $dateTimeClose;
        $this->price = $price;
    }

    public function getCurrencyId(): int
    {
        return $this->currencyId;
    }

    public function getSellerId(): int
    {
        return $this->sellerId;
    }

    public function getDateTimeOpen(): int
    {
        return $this->dateTimeOpen;
    }

    public function getDateTimeClose(): int
    {
        return $this->dateTimeClose;
    }

    public function getPrice(): float
    {
        return $this->price;
    }

}