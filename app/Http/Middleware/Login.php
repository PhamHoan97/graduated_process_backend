<?php

namespace App\Http\Middleware;

use Closure;

class Login
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
            return response()->json(['error' => 1, 'message' => "email is required"], 400);
        }
        if(!isset($password)){
            return response()->json(['error' => 1, 'message' => "password is required"], 400);
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return response()->json(['error' => "invalid email format"], 400);
        }

        return $next($request);
    }
}
