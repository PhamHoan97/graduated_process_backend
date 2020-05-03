<?php

namespace App\Http\Middleware;

use Closure;

class RegisterCompany
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
        $name = $request->name;
        $signature = $request->signature;
        $ceo = $request->ceo;
        $workforce = $request->workforce;
        $field = $request->field;
        $address = $request->address;
        $contact = $request->contact;

        if(!isset($name)){
            return response()->json(['message' => "name is required"], 400);
        }
        if(!isset($signature)){
            return response()->json(['error' => 1, 'message' => "signature is required"], 400);
        }
        if(!isset($ceo)){
            return response()->json(['error' => 1, 'message' => "ceo is required"], 400);
        }
        if(!isset($workforce)){
            return response()->json(['error' => 1, 'message' => "workforce is required"], 400);
        }
        if(!isset($field)){
            return response()->json(['error' => 1, 'message' => "field is required"], 400);
        }
        if(!isset($address)){
            return response()->json(['error' => 1, 'message' => "address is required"], 400);
        }
        if(!isset($contact)){
            return response()->json(['error' => 1, 'message' => "contact is required"], 400);
        }
        if (!filter_var($contact, FILTER_VALIDATE_EMAIL)) {
            return response()->json(['error' => 1, 'message' => "Invalid email format"], 400);
        }

        return $next($request);
    }
}
