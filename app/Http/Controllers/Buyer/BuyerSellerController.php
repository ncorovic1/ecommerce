<?php

namespace App\Http\Controllers\Buyer;

use App\Buyer;
use Illuminate\Http\Request;
use App\Http\Controllers\ApiController;

class BuyerSellerController extends ApiController
{
    /**
     * Display a listing of sellers of the specified buyer.
     *
     * @param  \App\Buyer  $buyer
     * @return \Illuminate\Http\Response
     */
    public function index(Buyer $buyer)
    {
        $sellers = $buyer->transactions()
                    ->with('product.seller')
                    ->get()
                    ->pluck('product.seller')
                    ->unique('id')
                    ->values();

        return $this->showAll($sellers);
    }
}
