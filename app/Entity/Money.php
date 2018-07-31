<?php

namespace App\Entity;

use Illuminate\Database\Eloquent\Relations\Pivot;

class Money extends Pivot
{
    protected $fillable = [
        "wallet_id",
        "currency_id",
        "amount"
    ];
}
