<?php

namespace App\Repository;


use App\Entity\Trade;

class TradeRepository implements Contracts\TradeRepository
{
    public function add(Trade $trade): Trade
    {
        $trade->save();
        return $trade;
    }
}