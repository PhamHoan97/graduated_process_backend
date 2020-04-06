<?php

namespace App\Http\Middleware;
use Tymon\JWTAuth\JWTAuth;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
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
            $system = JWTAuth::toUser($request->input('auth_token'));

        } catch (\Exception $e) {
            if ($e instanceof TokenInvalidException) {
                return $next($request);
                return response()->json(['error'=>'Token is Invalid']);
            } else if ($e instanceof TokenExpiredException){
                return $next($request);
                return response()->json(['error'=>'Token is Expired']);
            } else {
                return response()->json(['error'=>'Something is wrong']);
            }
        }
        return $next($request);
    }
}
