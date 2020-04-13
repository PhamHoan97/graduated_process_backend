<?php

namespace App\Http\Controllers\Api;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use App\Systems;
use App\Waitings;
use Config;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Auth;

class SystemController extends Controller
{
    public function __construct()
    {
        Config::set('jwt.user', Systems::class);
        Config::set('auth.providers', ['users' => [
            'driver' => 'eloquent',
            'model' => Systems::class,
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
                    'message' => 'Password or email is invalid',
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

    public function loginSystem(Request $request){
        try {

            $system = Systems::where('email', $request->email)->get()->first();
            if ($system && Hash::check($request->password, $system->password)) {
                $credentials = $request->only('email', 'password');
                $token = self::getToken($credentials);
                $system->auth_token = $token;
                $system->save();

                $response = [
                    'success' => true,
                    'message' => 'Login system successful',
                    'token' => $token
                ];
            } else {
                $response = ['error' => true, 'message' => 'Record doesnt exists'];
            }
        }catch (\Exception $e){
            $response = ['error' => true, 'message' => $e->getMessage()];
        }

        return response()->json($response, 201);
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logoutSystem()
    {
        $this->guard()->logout();

        return response()->json(['success'=>true,'message' => 'Logged out']);
    }

    public function getRegistrationListOfCompanies(){
        try{
            $registrations = Waitings::all();
        }catch (ModelNotFoundException $exception){
            return response()->json(['error' => true, 'message' => $exception->getMessage()]);
        }
        return response()->json([
            'success' => true,
            'message' => "Get data successful",
            'registrations' => $registrations
        ]);
    }

    public function getRegistrationInformationOfCompany(Request $request){
        if(!$request->idCompany){
            return response()->json(['error' => true, 'message' => $request->idCompany]);
        }else{
            $company = \App\Waitings::find($request->idCompany);
            return response()->json([
                'success' => true,
                'message' => "Get data successful",
                'information' => $company
            ]);
        }
    }

}
