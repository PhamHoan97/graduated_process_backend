<?php

namespace App\Http\Middleware;

use App\Accounts;
use Closure;

class UpdateAccountEmployee
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
        $newPassword = $request->newPassword;
        $confirmPassword = $request->confirmPassword;
        $token = $request->tokenData;

        if(!isset($token)){
            return response()->json(['error' => 1, 'message' => "token is required"], 400);
        }
        if(!isset($username)){
            return response()->json(['error' => 1, 'message' => "username is required"], 400);
        }
        if(!isset($password)){
            return response()->json(['error' => 1, 'message' => "password is required"], 400);
        }
        if(!isset($newPassword)){
            return response()->json(['error' => 1, 'message' => "newPassword is required"], 400);
        }
        if(!isset($confirmPassword)){
            return response()->json(['error' => 1, 'message' => "confirmPassword is required"], 400);
        }
        if($newPassword !== $confirmPassword){
            return response()->json(['error' => 1, 'message' => "newPassword and confirmPassword are not the same"], 400);
        }
        if(strlen($username)){
            return response()->json(['error' => 1, 'message' => "username must be at least 10 characters"], 400);
        }
        $account = Accounts::where('auth_token', $token)->first();
        $check = Accounts::where('username', $username)->first();
        if($account->username !== $username && $check ){
            return response()->json(['error' => 1, 'message' => "username was used by someone", "username" => true], 201);
        }

        return $next($request);
    }
}
