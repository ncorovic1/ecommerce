<?php

namespace App\Http\Controllers\Seller;

use App\Seller;
use App\User;
use App\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\ApiController;
use Symfony\Component\HttpKernel\Exception\HttpException;

class SellerProductController extends ApiController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Seller $seller)
    {
        $products = $seller->products;

        return $this->showAll($products);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, User $seller)
    {
         $rules = [
            'name'        => 'required',
            'description' => 'required',
            'quantity'    => 'required|integer|min:1',
            'image'       => 'required|image',
        ];

        $this->validate($request, $rules);

        $data = $request->all();
        $data['status'] = Product::UNAVAILABLE_PRODUCT;
        //filesystem optional if not the default one
        //path is path inside the root path in filesystem
        //$data['image']  = $request->image->store('path', 'filesystem');
        $data['image']  = $request->image->store('');
        $data['seller_id']  = $seller->id;

        $product = Product::create($data);

        return $this->showOne($product, 201);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Seller  $seller
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Seller $seller, Product $product)
    {
        $rules = [
            'quantity' => 'integer|min:1',
            'status'   => 'in:' . Product::AVAILABLE_PRODUCT . ',' . Product::UNAVAILABLE_PRODUCT,
            'image'    => 'image',
        ];

        $this->validate($request, $rules);
        $this->checkSeller($seller, $product);

        //since no additional check is needed intersect is used to ignore empty or null values, 
        //so no individual check like %request->has('password') && is not null% is needed
        $product->fill($request->intersect([
            'name',
            'description',
            'quantity',
        ]));

        if ($request->has('status')) {
            $product->status = $request->status;

            if ($product->isAvailable() && $product->categories()->count() == 0) {
                return $this->errorResponse('Product with no categories cannot become available.', 409);
            }
        }

        if ($request->hasFile('image')) {
            
        }

        //Dirty when something changed <==> $product->isClean()
        if (!$product->isDirty()) {
            return $this->errorResponse('You need to specify a different value to update.', 422);
        }

        $product->save();

        return $this->showOne($product);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Seller  $seller
     * @return \Illuminate\Http\Response
     */
    public function destroy(Seller $seller, Product $product)
    {
        $this->checkSeller($seller, $product);

        Storage::delete($product->image);
        $product->delete();

        return $this->showOne($product);
    }

    protected function checkSeller(Seller $seller, Product $product) {
        if ($seller->id != $product->seller->id) 
            throw new HttpException(422, 'Specified product is not in the ownership of seller requesting the change.');
    }
}
