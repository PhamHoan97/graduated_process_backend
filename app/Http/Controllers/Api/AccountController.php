<?php

namespace App\Http\Controllers\Api;

use App\Accounts;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class AccountController extends Controller
{
    public function __construct()
    {
        Config::set('jwt.user', Accounts::class);
        Config::set('auth.providers', ['users' => [
            'driver' => 'eloquent',
            'model' => Accounts::class,
        ]]);
    }

    public function guard() {
        return Auth::guard();
    }

    private function getToken($credentials){
        $token = null;
        try {
            if (!$token = JWTAuth::attempt($credentials)) {
                return response()->json([
                    'error' => true,
                    'message' => 'Password or account is invalid',
                ]);
            }
        } catch (JWTException $e) {
            return response()->json([
                'error' => true,
                'message' => 'Token creation failed',
            ]);
        }

        return $token;
    }

    public function loginAccount(Request $request){
        $accountEmail = Accounts::where('email', $request->account)->first();
        $accountUsername = Accounts::where('username', $request->account)->first();
        if ($accountEmail && Hash::check($request->password, $accountEmail->password))
        {
            $credentials = ["email" => $request->account, "password" => $request->password];
            $token = self::getToken($credentials);
            $accountEmail->auth_token = $token;
            $accountEmail->save();

            $response = [
                'success'=>true,
                'message' => 'Login company successful',
                'token'=> $token,
                'id' => $accountEmail->id,
                'employee_id' => $accountEmail->employee_id,
                'isEmployee' => true
            ];
        } else if($accountUsername && Hash::check($request->password, $accountUsername->password)){
            $credentials = ["username" => $request->account, "password" => $request->password];
            $token = self::getToken($credentials);
            $accountUsername->auth_token = $token;
            $accountUsername->save();

            $response = [
                'success'=>true,
                'message' => 'Login account successful',
                'token'=> $token,
                'id' => $accountUsername->id,
                'employee_id' => $accountUsername->employee_id,
                'isEmployee' => true
            ];
        }else{
            $response = ['error'=>true, 'message'=>'Record doesnt exists'];
        }

        return response()->json($response, 201);
    }

    public function logoutAccount()
    {
        $this->guard()->logout();

        return response()->json(['success'=>true,'message' => 'Logged out']);
    }
}
