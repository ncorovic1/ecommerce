<?php

namespace App\Http\Controllers\Category;

use App\Category;
use Illuminate\Http\Request;
use App\Http\Controllers\ApiController;

class CategorySellerController extends ApiController
{
    /**
     * Display a listing of sellers of the specified category.
     *
     * @param  \App\Category  $category
     * @return \Illuminate\Http\Response
     */
    public function index(Category $category)
    {
        $sellers = $category->products()
                    ->with('seller')
                    ->get()
                    ->pluck('seller')
                    ->unique('id')
                    ->values();
        
        return $this->showAll($sellers);
    }
 }