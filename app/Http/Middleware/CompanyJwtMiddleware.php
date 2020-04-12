<?php

namespace App\Http\Middleware;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Closure;

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
            $admin = auth('admins')->user();

        } catch (\Exception $e) {
            if ($e instanceof TokenInvalidException) {
                return response()->json(['error'=>'Token is Invalid']);
            } else if ($e instanceof TokenExpiredException){
                return response()->json(['error'=>'Token is Expired']);
            } else {
                return response()->json(['error'=>'Authorization Token not found']);
            }
        }
        return $next($request);
    }
}
