<?php

namespace App\Http\Middleware;

use Closure;

class LoginSystem
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
        $email = $request->email;
        $password = $request->password;

        if(!isset($email)){
            return response()->json(['error' => "Email is empty"], 400);
        }
        if(!isset($password)){
            return response()->json(['error' => "Password is empty"], 400);
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return response()->json(['error' => "Invalid email format"], 400);
        }

        return $next($request);
    }
}
