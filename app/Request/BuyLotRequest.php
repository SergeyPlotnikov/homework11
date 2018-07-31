<?php

namespace App\Request;


class BuyLotRequest implements Contracts\BuyLotRequest
{
    private $userId;
    private $lotId;
    private $amount;

    public function __construct(int $userId,int $lotId,float $amount)
    {
        $this->userId=$userId;
        $this->lotId = $lotId;
        $this->amount=$amount;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function getLotId(): int
    {
        return $this->lotId;
    }

    public function getAmount(): float
    {
        return $this->amount;
    }


}