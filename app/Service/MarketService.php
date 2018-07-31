<?php

namespace App\Service;


use App\Entity\Lot;
use App\Entity\Trade;
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
use App\Repository\Contracts\MoneyRepository;
use App\Repository\Contracts\TradeRepository;
use App\Repository\Contracts\UserRepository;
use App\Repository\Contracts\WalletRepository;
use App\Request\Contracts\AddLotRequest;
use App\Request\Contracts\BuyLotRequest;
use App\Request\MoneyRequest;
use App\Response\Contracts\LotResponse;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;

class MarketService implements Contracts\MarketService
{
    private $lotRepository;
    private $currencyRepository;
    private $userRepository;
    private $moneyRepository;
    private $walletRepository;

    public function __construct(LotRepository $lotRepository)
    {
        $this->lotRepository = $lotRepository;
        $this->currencyRepository = app(CurrencyRepository::class);
        $this->userRepository = app(UserRepository::class);
        $this->moneyRepository = app(MoneyRepository::class);
        $this->walletRepository = app(WalletRepository::class);
    }

    public function addLot(AddLotRequest $lotRequest): Lot
    {
        //check existing seller_id and currency_id
        $currency = $this->currencyRepository->getById($lotRequest->getCurrencyId());
        if (!isset($currency)) {
            throw new \LogicException("Currency doesn't exist with id = {$lotRequest->getCurrencyId()}");
        }
        $seller = $this->userRepository->getById($lotRequest->getSellerId());
        if (!isset($seller)) {
            throw  new \LogicException("Seller doesn't exist with id = {$lotRequest->getSellerId()}");
        }

        $activeLots = $this->lotRepository->findAllActiveLots($lotRequest->getSellerId());
        foreach ($activeLots as $activeLot) {
            if ($activeLot->currency_id == $lotRequest->getCurrencyId()) {
                throw new ActiveLotExistsException("You can't have more than one active session the same currency.");
            }
        }
        if ($lotRequest->getDateTimeClose() < $lotRequest->getDateTimeOpen()) {
            throw new IncorrectTimeCloseException("The closing date of the session can not be less than
                 the opening date");
        }
        if ($lotRequest->getPrice() < 0) {
            throw new IncorrectPriceException("The price of the lot can not be negative");
        }
        $lot = new Lot();
        $lot->currency_id = $lotRequest->getCurrencyId();
        $lot->seller_id = $lotRequest->getSellerId();
        $lot->date_time_open = Carbon::createFromTimestamp($lotRequest->getDateTimeOpen());
        $lot->date_time_close = Carbon::createFromTimestamp($lotRequest->getDateTimeClose());
        $lot->price = $lotRequest->getPrice();
        return $lot;
    }

    public function buyLot(BuyLotRequest $lotRequest): Trade
    {
        $userId = $lotRequest->getUserId();
        $lotId = $lotRequest->getLotId();
        $amount = $lotRequest->getAmount();
        $lot = $this->lotRepository->getById($lotId);

        if ($amount < 1) {
            throw new BuyNegativeAmountException("User can not buy less than one currency unit");
        }
        if (!isset($lot)) {
            throw new LotDoesNotExistException("Lot doesn't exist with id = {$lotId}.");
        }

        if ($lot->seller_id == $userId) {
            throw new BuyOwnCurrencyException("User can not buy own currency");
        }
        $sellerAmount = $this->moneyRepository->getSellerAmount($lot);

        if ($amount > $sellerAmount) {
            throw new IncorrectLotAmountException("User can not buy more currency than contains lot");
        }

        if (Carbon::parse($lot->date_time_close)->timestamp <= Carbon::parse(now())->timestamp)
            throw  new BuyInactiveLotException("User can not buy currency from closed lot");

        $currencyId = $lot->currency_id;

        $walletService = app(Contracts\WalletService::class);
//        $moneyRepository = app(MoneyRepository::class);

        //salesman
        $walletId = $this->walletRepository->findByUser($lot->seller_id)->id;
        $moneyRequest = new MoneyRequest($walletId, $currencyId, $amount);
        $this->moneyRepository->save($walletService->takeMoney($moneyRequest));

        //customer
        $walletId = $this->walletRepository->findByUser($userId)->id;

        $moneyRequest = new MoneyRequest($walletId, $currencyId, $amount);
        $this->moneyRepository->save($walletService->addMoney($moneyRequest));


        //add Trade
        $tradeRepository = app(TradeRepository::class);
        $trade = new Trade(['lot_id' => $lotId, 'user_id' => $userId, 'amount' => $amount]);

        $salesmanEmail = $this->userRepository->getById($lot->seller_id)->email;
        //send mail
        Mail::to($salesmanEmail)->send(new TradeCreated($trade));
        return $tradeRepository->add($trade);
    }

    public function getLot(int $id): LotResponse
    {
        $lot = $this->lotRepository->getById($id);
        if (!isset($lot)) {
            throw new LotDoesNotExistException("Lot doesn't exist with id = {$id}.");
        }

        $lotResponse = app(LotResponse::class, ['lot' => $lot]);
        return $lotResponse;
    }

    public function getLotList(): array
    {
        $lotLists = $this->lotRepository->findAll();
        $lotResponseList = [];
        foreach ($lotLists as $lot) {
            $lotResponseList[] = app(LotResponse::class, ['lot' => $lot]);
        }
        return $lotResponseList;
    }

}