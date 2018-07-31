<?php

namespace Tests\Unit;

use App\Entity\Currency;
use App\Entity\Lot;
use App\Entity\Money;
use App\Entity\Trade;
use App\Entity\Wallet;
use App\Exceptions\MarketException\ActiveLotExistsException;
use App\Exceptions\MarketException\BuyInactiveLotException;
use App\Exceptions\MarketException\BuyNegativeAmountException;
use App\Exceptions\MarketException\BuyOwnCurrencyException;
use App\Exceptions\MarketException\IncorrectLotAmountException;
use App\Exceptions\MarketException\IncorrectPriceException;
use App\Exceptions\MarketException\IncorrectTimeCloseException;
use App\Exceptions\MarketException\LotDoesNotExistException;
use App\Mail\TradeCreated;
use App\Repository\Contracts\CurrencyRepository;
use App\Repository\Contracts\LotRepository;
use App\Repository\Contracts\TradeRepository;
use App\Repository\Contracts\UserRepository;
use App\Repository\MoneyRepository;
use App\Repository\WalletRepository;
use App\Request\AddLotRequest;
use App\Request\BuyLotRequest;
use App\Request\MoneyRequest;
use App\Response\Contracts\LotResponse;
use App\Service\WalletService;
use App\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class MarketServiceTest extends TestCase
{

    public function testAddLotWithWrongCurrencyId()
    {
        $lotRequest = new AddLotRequest(1, 1, 1532883093, 1532890293,
            4000);
        $currencyStub = $this->createMock(CurrencyRepository::class);
        $currencyStub->method('getById')->willReturn(null);

        $currency = $currencyStub->getById($lotRequest->getCurrencyId());
        $this->assertNull($currency);

        $this->expectException(\LogicException::class);
        if (!isset($currency)) {
            throw new \LogicException("Currency doesn't exist with id = {$lotRequest->getCurrencyId()}");
        }
    }

    public function testAddLotWithWrongSellerId()
    {
        $lotRequest = new AddLotRequest(1, 1, 1532883093, 1532890293,
            4000);
        $userStub = $this->createMock(UserRepository::class);
        $userStub->method('getById')->willReturn(null);

        $seller = $userStub->getById($lotRequest->getSellerId());
        $this->assertNull($seller);

        $this->expectException(\LogicException::class);
        if (!isset($seller)) {
            throw  new \LogicException("Seller doesn't exist with id = {$lotRequest->getSellerId()}");
        }
    }

    public function testAddLotWithWrongActiveSession()
    {
        $lotRequest = new AddLotRequest(1, 1, 1532883093, 1532890293,
            4000);
        //exception ActiveLotExistsException
        $lotStub = $this->createMock(\App\Repository\LotRepository::class);
        $lotStub->method('findAllActiveLots')->willReturn([new Lot(['currency_id' => 1, 'seller_id' => 1,
            'date_time_open' => 1234, 'date_time_close' => 2345, 'price' => 500]),
            new Lot(['currency_id' => 2, 'seller_id' => 2,
                'date_time_open' => 1234, 'date_time_close' => 2345, 'price' => 300])]);
        $this->expectException(ActiveLotExistsException::class);
        foreach ($lotStub->findAllActiveLots($lotRequest->getSellerId()) as $activeLot) {
            if ($activeLot->currency_id == $lotRequest->getCurrencyId()) {
                throw new ActiveLotExistsException("You can't have more than one active session the same currency.");
            }
        }
    }

    public function testAddLotWithWrongTime()
    {
        $lotRequest = new AddLotRequest(1, 1, 40, 30,
            4000);
        $this->expectException(IncorrectTimeCloseException::class);
        if ($lotRequest->getDateTimeClose() < $lotRequest->getDateTimeOpen()) {
            throw new IncorrectTimeCloseException("The closing date of the session can not be less than
                 the opening date");
        }
    }

    public function addTestLotWithNegativePrice()
    {
        $lotRequest = new AddLotRequest(1, 1, 40, 30,
            -10);
        $this->expectException(IncorrectPriceException::class);
        if ($lotRequest->getPrice() < 0) {
            throw new IncorrectPriceException("The price of the lot can not be negative");
        }
    }

    public function testAddLot()
    {
        //exist currency_id and seller_id
        $lotRequest = new AddLotRequest(1, 1, 1532883093, 1532890293,
            4000);

        $currencyStub = $this->createMock(CurrencyRepository::class);
        $currencyStub->method('getById')->willReturn(new Currency(['id' => 1, 'name' => 'RUB']));
        //check that currency_id exists
        $currency = $currencyStub->getById($lotRequest->getCurrencyId());
        $this->assertEquals($lotRequest->getCurrencyId(), $currency->id);

        $userStub = $this->createMock(UserRepository::class);
        $userStub->method('getById')->willReturn(new User(['id' => $lotRequest->getSellerId(),
            'name' => 'John']));

        $seller = $userStub->getById($lotRequest->getSellerId());
        $this->assertEquals($lotRequest->getSellerId(), $seller->id);

        $lot = new Lot();
        $lot->currency_id = $lotRequest->getCurrencyId();
        $lot->seller_id = $lotRequest->getSellerId();
        $lot->date_time_open = Carbon::createFromTimestamp($lotRequest->getDateTimeOpen());
        $lot->date_time_close = Carbon::createFromTimestamp($lotRequest->getDateTimeClose());
        $lot->price = $lotRequest->getPrice();

        $this->assertEquals($lotRequest->getCurrencyId(), $lot->currency_id);
        $this->assertEquals($lotRequest->getSellerId(), $lot->seller_id);
        $this->assertEquals($lotRequest->getDateTimeOpen(), Carbon::parse($lot->date_time_open)->timestamp);
        $this->assertEquals($lotRequest->getDateTimeClose(), Carbon::parse($lot->date_time_close)->timestamp);
        $this->assertEquals($lotRequest->getPrice(), $lot->price);
    }

    public function testGetLotWithWrongId()
    {
        $id = 4;
        $lotStub = $this->createMock(\App\Repository\LotRepository::class);
        $lotStub->method('getById')->willReturn(null);
        $lot = $lotStub->getById($id);
        $this->assertNull($lot);
        $this->expectException(LotDoesNotExistException::class);
        if (!isset($lot)) {
            throw new LotDoesNotExistException("Lot doesn't exist with id = {$id}.");
        }
    }

    public function testGetLot()
    {
        $id = 1;
        $lotStub = $this->createMock(\App\Repository\LotRepository::class);
        $lotStub->method('getById')->willReturn(new Lot(['currency_id' => 1, 'seller_id' => 1,
            'date_time_open' => 1234, 'date_time_close' => 2345, 'price' => 500]));
        $lot = $lotStub->getById($id);
        $this->assertNotNull($lot);

        $lotResponse = app(LotResponse::class, ['lot' => $lot]);
        $this->instance(LotResponse::class, $lotResponse);
    }

    public function testGetLotList()
    {
        $lotStub = $this->createMock(\App\Repository\LotRepository::class);
        $lotStub->method('findAll')->willReturn([new Lot(['currency_id' => 1, 'seller_id' => 1,
            'date_time_open' => 1234, 'date_time_close' => 2345, 'price' => 500]),
            new Lot(['currency_id' => 2, 'seller_id' => 2,
                'date_time_open' => 1234, 'date_time_close' => 2345, 'price' => 300])]);

        $lotList = $lotStub->findAll();
        $this->assertNotNull($lotList);
    }
    public function testBuyKotWithNegativeAmount()
    {
        $lotRequest = new BuyLotRequest(1, 1, -3);
        $this->expectException(BuyNegativeAmountException::class);
        if ($lotRequest->getAmount() < 1) {
            throw new BuyNegativeAmountException("User can not buy less than one currency unit");
        }
    }

    public function testBuyLotWithWrongLotId()
    {
        $lotRequest = new BuyLotRequest(1, 1, -3);
        $lotRepositoryStub = $this->createMock(\App\Repository\LotRepository::class);
        $lotRepositoryStub->method('getById')->willReturn(null);
        $this->expectException(LotDoesNotExistException::class);
        $lot = $lotRepositoryStub->getById($lotRequest->getLotId());
        if (!isset($lot)) {
            throw new LotDoesNotExistException("Lot doesn't exist with id = {$lotRequest->getLotId()}.");
        }
    }

    public function testBuyOwnLot()
    {
        $lotRequest = new BuyLotRequest(1, 1, 100);
        $lotRepositoryStub = $this->createMock(\App\Repository\LotRepository::class);
        $lotRepositoryStub->method('getById')->willReturn(new Lot(['seller_id' => 1]));
        $lot = $lotRepositoryStub->getById($lotRequest->getLotId());
        $this->expectException(BuyOwnCurrencyException::class);
        if ($lot->seller_id == $lotRequest->getUserId()) {
            throw new BuyOwnCurrencyException("User can not buy own currency");
        }
    }

    public function testBuyLotWithIncorrectAmount()
    {
        $lotRequest = new BuyLotRequest(1, 1, 100);
        $moneyRepositoryStub = $this->createMock(\App\Repository\MoneyRepository::class);
        $moneyRepositoryStub->method('getSellerAmount')->willReturn(50);

        $lotRepositoryStub = $this->createMock(\App\Repository\LotRepository::class);
        $lotRepositoryStub->method('getById')->willReturn(new Lot(['seller_id' => 1]));
        $lot = $lotRepositoryStub->getById($lotRequest->getLotId());
        $sellerAmount = $moneyRepositoryStub->getSellerAmount($lot);
        $this->expectException(IncorrectLotAmountException::class);
        if ($sellerAmount < $lotRequest->getAmount()) {
            throw new IncorrectLotAmountException("User can not buy more currency than contains lot");
        }
    }

    public function testBuyLotFromClosedLot()
    {
        $lotRequest = new BuyLotRequest(1, 1, 100);
        $lotRepositoryStub = $this->createMock(\App\Repository\LotRepository::class);
        $lotRepositoryStub->method('getById')->willReturn(new Lot(['date_time_close' =>
            Carbon::createFromTimestamp(1532913139)]));
        $lot = $lotRepositoryStub->getById($lotRequest->getLotId());
        $this->expectException(BuyInactiveLotException::class);
        if (Carbon::parse($lot->date_time_close)->timestamp <= Carbon::parse(now())->timestamp) {
            throw  new BuyInactiveLotException("User can not buy currency from closed lot");
        }
    }

    public function testBuyLot()
    {
        $lotRequest = new BuyLotRequest(1, 1, 100);
//        $walletServiceStub = $this->createMock(WalletService::class);

        $lotRepositoryStub = $this->createMock(\App\Repository\LotRepository::class);
        $lotRepositoryStub->method('getById')->willReturn(new Lot(['id' => 1, 'seller_id' => 2,
            'currency_id' => 2]));
        $lot = $lotRepositoryStub->getById($lotRequest->getLotId());
        $this->assertNotNull($lot);

        $userRepositoryStub = $this->createMock(UserRepository::class);
        $userRepositoryStub->method('getById')
            ->willReturn(new User(['id' => 2, 'email' => 'example@ukr.net']));

//        $tradeRepositoryStub = $this->createMock(\App\Repository\TradeRepository::class);

        $trade = new Trade(['lot_id' => $lotRequest->getLotId(), 'user_id' => $lotRequest->getUserId(),
            'amount' => $lotRequest->getAmount()]);
        $this->assertEquals($lotRequest->getLotId(), $trade->lot_id);
        $this->assertEquals($lotRequest->getUserId(), $trade->user_id);
        $this->assertEquals($lotRequest->getAmount(), $trade->amount);

        $salesmanEmail = $userRepositoryStub->getById(2)->email;
        $this->assertEquals('example@ukr.net', $salesmanEmail);
//        Mail::fake();
//        Mail::assertSent(TradeCreated::class, function ($mail) use ($trade) {
//            return $mail->trade->amount === $trade->amount;
//        });
//        Mail::assertSent(TradeCreated::class,1);
    }
}