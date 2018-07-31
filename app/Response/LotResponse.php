<?php

namespace App\Response;

use App\Entity\Lot;
use Carbon\Carbon;

class LotResponse implements Contracts\LotResponse
{
    private $lot;

    public function __construct(Lot $lot)
    {
        $this->lot = $lot;
    }

    public function getId(): int
    {
        return $this->lot->id;
    }

    public function getUserName(): string
    {
        return $this->lot->user->name;
    }

    public function getCurrencyName(): string
    {
        return $this->lot->currency->name;
    }

    public function getAmount(): float
    {
        $sellerAmount = 0;
        foreach ($this->lot->currency->wallet as $wallet) {
            if ($wallet->user_id == $this->lot->seller_id) {
                $sellerAmount = $wallet->pivot->amount;
                break;
            }
        }
        return $sellerAmount;
    }

    public function getDateTimeOpen(): string
    {
        return Carbon::parse($this->lot->date_time_open)->format('Y/m/d H:m:s');
    }

    public function getDateTimeClose(): string
    {
        return Carbon::parse($this->lot->date_time_close)->format('Y/m/d H:m:s');
    }

    public function getPrice(): string
    {
        return number_format($this->lot->price, 2);
    }

}