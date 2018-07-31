<?php

namespace Tests\Browser;

use Tests\DuskTestCase;
use Laravel\Dusk\Browser;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class AddLotTest extends DuskTestCase
{


    public function testAddLotError()
    {
        $this->browse(function (Browser $browser) {
            $browser->resize(1920, 1080);
            $browser->visit('/market/lots/add')
                ->assertSee('seller_id')
                ->assertSee('date_time_open')
                ->assertSee('currency_id')
                ->assertSee('price')
                ->type('currency_id', 2)
                ->type('seller_id', 2)
                ->type('date_time_open', 1533036120)
                ->type('date_time_close', 1543036120)
                ->type('price', -100)
                ->press('Send')
                ->assertPathIs('/market/lots/create')
                ->assertSee('Sorry, error has been occurred:');
        });
    }

    /**
     * A basic browser test example.
     *
     * @return void
     */
    public function testAddLotSuccess()
    {

        $this->browse(function (Browser $browser) {
            $browser->resize(1920, 1080);
            $browser->visit('/market/lots/add')
                ->assertSee('seller_id')
                ->assertSee('date_time_open')
                ->assertSee('currency_id')
                ->assertSee('price')
                ->type('currency_id', 2)
                ->type('seller_id', 2)
                ->type('date_time_open', 1533036120)
                ->type('date_time_close', 1543036120)
                ->type('price', 5000)
                ->press('Send')
                ->assertPathIs('/market/lots/create');
//                ->assertSee('Lot has been added successfully!');
        });
    }


}
