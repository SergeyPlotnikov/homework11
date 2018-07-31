<?php

use Faker\Generator as Faker;

$factory->define(App\Entity\Money::class, function (Faker $faker) {
    return [
        'currency_id' => 1,
        'amount' => $faker->numberBetween(100, 1000),
        'wallet_id' => 1
    ];
});