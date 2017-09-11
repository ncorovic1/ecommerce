<?php

namespace App\Http\Controllers\Product;

use App\Product;
use Illuminate\Http\Request;
use App\Http\Controllers\ApiController;

class ProductBuyerController extends ApiController
{
    /**
     * Display a listing of buyers of the specified product.
     *
     * @param  \App\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function index(Product $product)
    {
        $buyers = $product->transactions()
                    ->with('buyer')
                    ->get()
                    ->pluck('buyer')
                    ->unique('id')
                    ->values();

        return $this->showAll($buyers);
    }
}
