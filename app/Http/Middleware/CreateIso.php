<?php

namespace App\Http\Middleware;

use Closure;

class CreateIso
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
        $year = $request->year;
        $content = $request->isoContent;

        if(!isset($name)){
            return response()->json(['error' => 1, 'message' => "name is required"], 400);
        }
        if(!isset($year)){
            return response()->json(['error' => 1, 'message' => "year is required"], 400);
        }

        if(!isset($content)){
            return response()->json(['error' => 1, 'message' => "content is required"], 400);
        }

        return $next($request);
    }
}
