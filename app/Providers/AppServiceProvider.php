<?php

namespace App\Providers;

use App\Repository\Contracts\CurrencyRepository;
use App\Repository\Contracts\LotRepository;
use App\Repository\Contracts\MoneyRepository;
use App\Repository\Contracts\TradeRepository;
use App\Repository\Contracts\UserRepository;
use App\Repository\Contracts\WalletRepository;
use App\Request\Contracts\AddCurrencyRequest;
use App\Request\Contracts\AddLotRequest;
use App\Request\Contracts\BuyLotRequest;
use App\Request\Contracts\CreateWalletRequest;
use App\Request\Contracts\MoneyRequest;
use App\Response\Contracts\LotResponse;
use App\Service\Contracts\CurrencyService;
use App\Service\Contracts\MarketService;
use App\Service\Contracts\WalletService;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Schema::defaultStringLength(191);
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //User
        $this->app->singleton(UserRepository::class, \App\Repository\UserRepository::class);
        //Currency
        $this->app->bind(AddCurrencyRequest::class, function ($app) {
            return new \App\Request\AddCurrencyRequest($app->request->name);
        });
        $this->app->singleton(CurrencyRepository::class, \App\Repository\CurrencyRepository::class);
        $this->app->singleton(CurrencyService::class, \App\Service\CurrencyService::class);

        //Wallet
        $this->app->bind(CreateWalletRequest::class, function ($app) {
            return new \App\Request\CreateWalletRequest($app->request->user_id);
        });
        $this->app->singleton(WalletRepository::class, \App\Repository\WalletRepository::class);
        $this->app->singleton(WalletService::class, \App\Service\WalletService::class);

        //Money
        $this->app->bind(MoneyRequest::class, function ($app) {
            return new \App\Request\MoneyRequest($app->request->wallet_id, $app->request->currency_id,
                $app->request->amount);
        });
        $this->app->singleton(MoneyRepository::class, \App\Repository\MoneyRepository::class);


        //Lot
        $this->app->bind(AddLotRequest::class, function ($app) {
            return new \App\Request\AddLotRequest($app->request->currency_id, $app->request->seller_id,
                $app->request->date_time_open, $app->request->date_time_close, $app->request->price);
        });
        $this->app->singleton(LotRepository::class, \App\Repository\LotRepository::class);
        $this->app->bind(BuyLotRequest::class, function ($app) {
            return new \App\Request\BuyLotRequest($app->request->user_id, $app->request->lot_id, $app->request->amount);
        });


        //Market
        $this->app->singleton(MarketService::class, \App\Service\MarketService::class);

        //LotResponse
        $this->app->bind(LotResponse::class, function ($app, $params) {
            return new \App\Response\LotResponse($params['lot']);
        });

        //Trade
        $this->app->singleton(TradeRepository::class, \App\Repository\TradeRepository::class);

    }
}
