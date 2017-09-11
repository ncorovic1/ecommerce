<?php

namespace App\Http\Controllers\Category;

use App\Category;
use App\Transformers\CategoryTransformer;
use Illuminate\Http\Request;
use App\Http\Controllers\ApiController;

class CategoryController extends ApiController
{
    public function __construct() {
        parent::__construct();

        $this->middleware('transform.input:' . CategoryTransformer::class)->only(['store', 'update']);
    }

    /**
     * Display a listing of the categories.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $categories = Category::all();

        return $this->showAll($categories);
    }

    /**
     * Store a newly created category in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $rules = [
            'name' => 'required',
            'description' => 'required',
        ];
        $this->validate($request, $rules);

        $data = $request->all();
        $category = Category::create($data);

        return $this->showOne($category, 201);
    }

    /**
     * Display the specified category.
     *
     * @param  \App\Category  $category
     * @return \Illuminate\Http\Response
     */
    public function show(Category $category)
    {
        return  $this->showOne($category);
    }

    /**
     * Update the specified category in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Category  $category
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Category $category)
    {
        if ($request->has('name')) {
            $category->name = $request->name;
        }

        if ($request->has('description')) {
            $category->description = $request->description;
        }

        //Dirty when something changed
        if (!$category->isDirty()) {
            return $this->errorResponse('You need to specify a different value to update', 421);
        }
        
        $category->save();

        return $this->showOne($category);
    }

    /**
     * Remove the specified category from storage.
     *
     * @param  \App\Category  $category
     * @return \Illuminate\Http\Response
     */
    public function destroy(Category $category)
    {
        $category->delete();

        return $this->showOne($category);
    }
}
