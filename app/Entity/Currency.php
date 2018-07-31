<?php

namespace App\Entity;

use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    protected $fillable = [
        "id",
        "name"
    ];

    public function wallet()
    {
        return $this->belongsToMany('App\Entity\Wallet', 'money')->using('App\Entity\Money')
            ->withPivot('amount');
    }
}
