<?php

namespace App\Repository;


use App\Entity\Lot;

class LotRepository implements Contracts\LotRepository
{
    public function add(Lot $lot): Lot
    {
        $lot->save();
        return $lot;
    }

    public function getById(int $id): ?Lot
    {
        return Lot::find($id);
    }

    public function findAll()
    {
        return Lot::all();
    }

    public function findActiveLot(int $userId): ?Lot
    {
        return Lot::where('seller_id', $userId)->where('date_time_close', '>', now())->first();
    }

    public function findAllActiveLots(int $userId)
    {
        return Lot::where('seller_id', $userId)->where('date_time_close', '>', now())->get();
    }

}