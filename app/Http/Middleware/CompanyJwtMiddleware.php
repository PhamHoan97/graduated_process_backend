<?php

namespace App\Http\Middleware;
use Closure;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Exceptions\UserNotDefinedException;
use Config;
use JWTAuth;

class CompanyJwtMiddleware
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
        try {
            Config::set('jwt.user', 'App\Admins');
            Config::set('auth.providers.users.model', \App\Admins::class);
            $admin = JWTAuth::parseToken()->authenticate();
        } catch (\Exception $e) {
            if ($e instanceof TokenInvalidException) {
                return response()->json(['error' => 1, 'message'=>'Token is Invalid']);
            } else if ($e instanceof TokenExpiredException){
                return response()->json(['error' => 1, 'message'=>'Token is Expired']);
            }  else if ($e instanceof UserNotDefinedException ){
                return response()->json(['error' => 1, 'message'=>'Authorization is required']);
            }  else {
                return response()->json(['error' => 1, 'message'=> $e->getMessage()]);
            }
        }
        return $next($request)->header('Access-Control-Allow-Origin', '*')
            ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
    }
}
