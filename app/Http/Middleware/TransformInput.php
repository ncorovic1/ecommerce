<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Validation\ValidationException;

class TransformInput
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, $transformer)
    {
        $transformerInput = [];

        foreach($request->request->all() as $input => $value) {
            $transformedInput[$transformer::originalAttribute($input)] = $value; 
        }
        //replace names for original inputs with the new ones
        $request->replace($transformedInput);
        $response = $next($request);

        //if error occured during validation replace the field names with the coresponding transformed names
        if (isset($response->exception) && $response->exception instanceof ValidationException) {
            $data = $response->getData();    
            $transformedErrors = [];

            foreach ($data->error as $att => $value) {
                $transformedText = explode(' ', $value[0], 3);
                $transformedText[1] = $transformer::transformedAttribute($att);            
                $value[0] = implode(' ', $transformedText);

                $transformedErrors[$transformer::transformedAttribute($att)] = $value;
            }

            $data->error = $transformedErrors;
            $response->setData($data);
        }
        return $response;
    }
}
