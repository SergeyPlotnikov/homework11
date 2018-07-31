<?php

namespace Tests\Unit;

use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Task2 extends TestCase
{
//    use RefreshDatabase;

    public function testAddLot()
    {
        $storeData = [
            'currency_id' => 1,
            'seller_id' => 2,
            'date_time_open' => 1533036120,
            'date_time_close' => 1543036120,
            'price' => 5000,
        ];
        $expectedData = [
            'currency_id' => 1,
            'date_time_open' => 1533036120,
            'date_time_close' => 1543036120,
            'price' => 5000,
        ];
        $user = User::find($storeData['seller_id']);
        $response = $this->actingAs($user, 'api')->json('POST', '/api/v1/lots', $storeData);

        $response->assertStatus(201);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertJsonFragment($expectedData);
//        $this->assertDatabaseHas('lots', $storeData);
    }

    public function testAddLotWithWrongDateTime()
    {
        $storeData = [
            'currency_id' => 2,
            'seller_id' => 2,
            'date_time_open' => 1532991815,
            'date_time_close' => 1532970215,
            'price' => 5000,
        ];
        $user = User::find($storeData['seller_id']);
        $response = $this->actingAs($user, 'api')->json('POST', '/api/v1/lots', $storeData);

        $response->assertStatus(400);
        $response->assertHeader('Content-Type', 'application/json');
    }

    public function testGetLot()
    {
        $id = 1;
        $response = $this->json('GET', "/api/v1/lots/{$id}");
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertJsonFragment([
            'id' => 1,
        ]);
    }


    public function testLotList()
    {
        $response = $this->json('GET', '/api/v1/lots');
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/json');
    }

    public function testBuyLot()
    {
        $storeData = [
            'user_id' => 1,
            'lot_id' => 1,
            'amount' => 12,
        ];
        $expectedData = [
            'lot_id' => 1,
            'amount' => 12
        ];
        $user = User::find($storeData['user_id']);
        $response = $this->actingAs($user, 'api')->json('POST', '/api/v1/trades', $storeData);
        $response->assertStatus(201);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertJsonFragment($expectedData);
    }

    public function testBuyLotWithWrongAmount()
    {
        $storeData = [
            'user_id' => 1,
            'lot_id' => 1,
            'amount' => -100,
        ];

        $user = User::find($storeData['user_id']);
        $response = $this->actingAs($user, 'api')->json('POST', '/api/v1/trades', $storeData);
        $response->assertStatus(400);
        $response->assertHeader('Content-Type', 'application/json');
    }
}

