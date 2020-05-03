<?php

namespace App\Http\Middleware;

use App\Employees;
use Closure;

class UpdateEmployeeMiddleware
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
        $email = $request->email;
        $checkEmailExist = Employees::where('email', $email)->first();
        if(!isset($name)){
            return response()->json(['error' => 1, 'message' => "name is required"], 400);
        }
        if(!isset($email)){
            return response()->json(['error' => 1, 'message' => "email is required"], 400);
        }
        if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return response()->json(['error' => 1, 'message' => "invalid email format"], 400);
        }
        if($checkEmailExist){
            return response()->json(['error' => 1, 'message' => "email was used by someone"], 400);
        }

        if($request->hasFile('avatar')){
            $file = $request->file('avatar');
            if(!in_array($file->getClientOriginalExtension(),['jpeg','png','jpg'])){
                $error = "invalid file format!!";
                return response()->json(['error' =>1, 'message'=> $error]);
            }
        }

        return $next($request);
    }
}
