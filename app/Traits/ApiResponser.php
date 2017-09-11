<?php

namespace App\Traits;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;
use Illuminate\Pagination\LengthAwarePaginator;

trait ApiResponser {
    protected function successResponse($data, $code) {
        return response()->json($data, $code);
    }

    protected function errorResponse($message, $code) {
        return response()->json(['error' => $message, 'code' => $code], $code);
    }

    protected function showAll(Collection $collection, $code = 200) {
        if (!$collection->isEmpty()) {
            $transformer = $collection->first()->transformer;
            $collection  = $this->filterData($collection, $transformer);
            $collection  = $this->sortData($collection, $transformer);
            $collection  = $this->paginate($collection);
            $collection  = $this->transformData($collection, $transformer);
            $collection  = $this->cacheResponse($collection);
            return $this->successResponse($collection, $code);
        }
        //fractal includes 'data' by itself so add only on empty collection
        return $this->successResponse(['data' => $collection], $code);
    }

    protected function showOne(Model $model, $code = 200) {
        $transformer = $model->transformer;
        $model       = $this->transformData($model, $transformer);
        
        return $this->successResponse($model, $code);
    }

    protected function showMessage($message, $code = 200) {
        return $this->successResponse(['data' => $message], $code);
    }

    protected function filterData(Collection $collection, $transformer) {
        if (request()->has('sort_by')) {
            $attribute = $transformer::originalAttribute(request()->sort_by);
            $collection = $collection->sortBy->{$attribute};
        }

        return $collection;
    }

    protected function sortData(Collection $collection, $transformer) {
        foreach (request()->query() as $query => $value) {
            $attribute = $transformer::originalAttribute($query);
            //attribute is null==!isset if does not exist, value is null==!isset if not set
            if (isset($attribute, $value))
                $collection = $collection->where($attribute, $value);
        }

        return $collection;
    }

    protected function paginate(Collection $collection) {
		$rules = [
			'per_page' => 'integer|min:2|max:50',
		];
		Validator::validate(request()->all(), $rules);

		$perPage = 15;
        if (request()->has('per_page')) {
			$perPage = (int) request()->per_page;
		}

        $page = LengthAwarePaginator::resolveCurrentPage();
        $path = LengthAwarePaginator::resolveCurrentPath();

		$results = $collection->forPage($page, $perPage);
        $paginated = new LengthAwarePaginator($results, $collection->count(), $perPage, $page, [
            'path' => $path,
        ]);
        
        $paginated->appends(request()->all());

        return $paginated;
	}

    protected function transformData($data, $transformer) {
		$transformation = fractal($data, new $transformer);
        
		return $transformation->toArray();
	}
    //Array and not a collection
    protected function cacheResponse($data) {
        //url without query parameters
		$url = request()->url();
        $queryParams = request()->query();
        //Sorting by reference, mixing the order of params doesnt produce multiple caching
        ksort($queryParams);
        $queryString = http_build_query($queryParams);

        $fullUrl = "{$url}?{$queryString}";
        
		return Cache::remember($fullUrl, 30/60, function() use($data) {
            return $data;  
        });
	}
}