<?php

namespace App\Http\Middleware;
use App\Systems;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Exceptions\UserNotDefinedException;
use Config;
use JWTAuth;
use Closure;

class SystemJwtMiddleware
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
            Config::set( 'jwt.user', 'App\Systems' );
            Config::set( 'auth.providers.users.model', Systems::class );
            $system = JWTAuth::parseToken()->authenticate();
        } catch (\Exception $e) {
            if ($e instanceof TokenInvalidException) {
                return response()->json(['error' =>1, 'message'=>'Token is Invalid']);
            } else if ($e instanceof TokenExpiredException){
                return response()->json(['error' =>1, 'message'=>'Token is Expired']);
            }  else if ($e instanceof UserNotDefinedException ){
                return response()->json(['error' =>1, 'message'=>'Authorization is required']);
            }  else {
                return response()->json(['error' =>1, 'message'=>'Authorization Token not found']);
            }
        }
        return $next($request);
    }
}
