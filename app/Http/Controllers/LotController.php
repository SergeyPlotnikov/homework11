<?php

namespace App\Http\Controllers;

use App\Repository\Contracts\LotRepository;
use App\Request\Contracts\AddLotRequest;
use App\Request\Contracts\BuyLotRequest;
use App\Service\Contracts\MarketService;
use Illuminate\Support\Facades\Auth;

class LotController extends Controller
{

    private $lotRepository;

    public function __construct(LotRepository $lotRepository)
    {
        $this->lotRepository = $lotRepository;
    }

    public function createLot()
    {
        return view('create-lot');
    }

    public function addLot(AddLotRequest $addLotRequest)
    {
        $marketService = app(MarketService::class);
        try {
            $this->lotRepository->add($marketService->addLot($addLotRequest));
        } catch (\Exception $exception) {
            return view('error', ['errorText' => $exception->getMessage()]);
        }
        return view('success');
    }

    public
    function add(AddLotRequest $addLotRequest)
    {
        if (!Auth::check()) {
            return response()->json([
                'error' => [
                    'message' => "You are not authenticated user"
                ]
            ], 403);

        }
        $marketService = app(MarketService::class);
        try {
            $lot = $this->lotRepository->add($marketService->addLot($addLotRequest));
        } catch (\Exception $exception) {
            return response()->json([
                'error' => [
                    'message' => $exception->getMessage(),
                    'status_code' => $exception->getCode()
                ]
            ], 400);
        }
        return response()->json([
            'currency_id' => $lot->currency_id,
            'date_time_open' => $lot->getDateTimeOpen(),
            'date_time_close' => $lot->getDateTimeClose(),
            'price' => $lot->price
        ], 201);


    }


    public
    function show($id)
    {
        $marketService = app(MarketService::class);
        $lot = $marketService->getLot($id);
        return response()->json([
            'id' => $lot->getId(),
            'user_name' => $lot->getUserName(),
            'currency_name' => $lot->getCurrencyName(),
            'amount' => $lot->getAmount(),
            'date_time_open' => $lot->getDateTimeOpen(),
            'date_time_close' => $lot->getDateTimeClose(),
            'price' => $lot->getPrice()
        ]);
    }

    public
    function list()
    {
        $marketService = app(MarketService::class);
        $lotList = $marketService->getLotList();
        $res = [];
        foreach ($lotList as $num => $lot) {
            $res[$num]['id'] = $lot->getId();
            $res[$num]['user_name'] = $lot->getUserName();
            $res[$num]['currency_name'] = $lot->getCurrencyName();
            $res[$num]['amount'] = $lot->getAmount();
            $res[$num]['date_time_open'] = $lot->getDateTimeOpen();
            $res[$num]['date_time_close'] = $lot->getDateTimeClose();
            $res[$num]['price'] = $lot->getPrice();
        }
        return response()->json($res);

    }


    public function buy(BuyLotRequest $request)
    {
        if (Auth::check()) {
            $marketService = app(MarketService::class);
            try {
                $trade = $marketService->buyLot($request);
            } catch (\Exception $exception) {
                return response()->json([
                    'error' => [
                        'message' => $exception->getMessage(),
                        'status_code' => $exception->getCode()
                    ]
                ], 400);
            }
            return response()->json([
                'lot_id' => $trade->lot_id,
                'amount' => $trade->amount
            ], 201);
        }
        return response()->json([
            'error' => [
                'message' => "You are not authenticated user"
            ]
        ], 403);
    }
}