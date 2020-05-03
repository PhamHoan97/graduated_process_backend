<?php

namespace App\Http\Middleware;

use Closure;

class LoginCompany
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
        $username = $request->username;
        $password = $request->password;

        if(!isset($username)){
            return response()->json(['error' => 1, 'message' => "username is required"], 400);
        }
        if(!isset($password)){
            return response()->json(['error' => 1, 'message' => "password is required"], 400);
        }

        return $next($request);
    }
}
