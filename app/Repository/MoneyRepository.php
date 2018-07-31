<?php

namespace App\Repository;

use App\Entity\Lot;
use App\Entity\Money;

class MoneyRepository implements Contracts\MoneyRepository
{
    public function save(Money $money): Money
    {
        $money->save();
        return $money;
    }

    public function findByWalletAndCurrency(int $walletId, int $currencyId): ?Money
    {
        return Money::where('wallet_id', $walletId)->where('currency_id', $currencyId)->first();
    }

    public function getSellerAmount(Lot $lot): float
    {
        $sellerAmount = 0;
        foreach ($lot->currency->wallet as $wallet) {
            if ($wallet->user_id == $lot->seller_id) {
                $sellerAmount = $wallet->pivot->amount;
            }
        }
        return $sellerAmount;
    }

}