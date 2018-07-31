<?php

namespace App\Service;

use App\Entity\Currency;
use App\Entity\Money;
use App\Entity\Wallet;
use App\Repository\Contracts\MoneyRepository;
use App\Repository\Contracts\WalletRepository;
use App\Request\Contracts\CreateWalletRequest;
use App\Request\Contracts\MoneyRequest;
use App\User;

class WalletService implements Contracts\WalletService
{
    private $walletRepository;
    private $moneyRepository;

    public function __construct(WalletRepository $walletRepository, MoneyRepository $moneyRepository)
    {
        $this->walletRepository = $walletRepository;
        $this->moneyRepository = $moneyRepository;
    }

    public function addWallet(CreateWalletRequest $walletRequest): Wallet
    {
        $userId = $walletRequest->getUserId();
        $user = User::find($userId);
        if (isset($user)) {
            $wallet = $this->walletRepository->findByUser($userId);
            if (!isset($wallet)) {
                return new Wallet(['user_id' => $userId]);
            }
            throw new \LogicException("The table can't hold more than 1 record with the same user_id");
        }
        throw new \LogicException("User doesn't exist with id = {$userId}");
    }

    public function addMoney(MoneyRequest $moneyRequest): Money
    {
        $money = $this->getMoneyRecord($moneyRequest);
        $money->amount += $moneyRequest->getAmount();
        return $money;
    }

    public function takeMoney(MoneyRequest $moneyRequest): Money
    {
        $money = $this->getMoneyRecord($moneyRequest);
        if (isset($money)) {
            $amount = $moneyRequest->getAmount();
            if ($amount <=$money->amount) {
                $money->amount -= $moneyRequest->getAmount();
                return $money;
            }
            throw new \LogicException("Currency amount can't be greater than your balance in your wallet.");
        }
        throw new \LogicException("Doesn't exist money record");
    }

    private function getMoneyRecord(MoneyRequest $moneyRequest)
    {
        $walletId = $moneyRequest->getWalletId();
        $wallet = Wallet::find($walletId);
        if (!isset($wallet)) {
            throw new \LogicException("Doesn't exist wallet with id = {$walletId}");
        };
        $currencyId = $moneyRequest->getCurrencyId();
        $currency = Currency::find($currencyId);
        if (!isset($currency)) {
            throw new \LogicException("Doesn't exist currency with id = {$currencyId}");
        };
        return $this->moneyRepository->findByWalletAndCurrency($walletId, $currencyId);
    }
}