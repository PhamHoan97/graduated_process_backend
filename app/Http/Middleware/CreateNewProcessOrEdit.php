<?php

namespace App\Http\Middleware;

use Closure;

class CreateNewProcessOrEdit
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $tọken = $request->token;
        $information = $request->information;
        $xml = $request->xml;
        $elements = $request->elements;

        if(!isset($tọken)){
            return response()->json(['error' => 1, 'message' => "token is required"], 400);
        }
        if(!isset($information)){
            return response()->json(['error' => 1, 'message' => "information is required"], 400);
        }
        if(!isset($xml)){
            return response()->json(['error' => 1, 'message' => "xml is required"], 400);
        }
        if(!isset($elements)){
            return response()->json(['error' => 1, 'message' => "elements is required"], 400);
        }
        return $next($request);
    }
}
