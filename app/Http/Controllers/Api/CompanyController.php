<?php

namespace App\Http\Controllers\Api;

use App\Admins;
use App\Departments;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use DB;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;
use Config;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Support\Facades\Auth;


class CompanyController extends Controller
{
    public function __construct()
    {
        Config::set('jwt.user', Admins::class);
        Config::set('auth.providers', ['users' => [
            'driver' => 'eloquent',
            'model' => Admins::class,
        ]]);
    }

    public function guard() {
        return Auth::guard();
    }

    public function register(Request $request){
        $name = $request->name;
        $signature = $request->signature;
        $ceo = $request->ceo;
        $workforce = $request->workforce;
        $field = $request->field;
        $address = $request->address;
        $contact = $request->contact;

        $record = DB::table('waitings')->where('contact', $contact)->first();

        if($record){
            return response()->json(["error" => "This email contact is used by someone"], 400);
        }

        try{
            $waitings = new \App\Waitings();
            $waitings->name = $name;
            $waitings->signature = $signature;
            $waitings->ceo = $ceo;
            $waitings->workforce = $workforce;
            $waitings->field = $field;
            $waitings->address = $address;
            $waitings->contact = $contact;
            $waitings->save();

            return response()->json(["success" => true, "message" => "Sent to admin of system","company" => $waitings], 200);
        }catch (\Exception $e){
            return response()->json(["error" => true, "message" => "Something was wrong with information company"], 400);
        }
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

    public function loginCompany(Request $request){
        $adminEmail = Admins::where('email', $request->account)->first();
        $adminUserName = Admins::where('username', $request->account)->first();
        if ($adminEmail && Hash::check($request->password, $adminEmail->password))
        {
            $credentials = ["email" => $request->account, "password" => $request->password];
            $token = self::getToken($credentials);
            $adminEmail->auth_token = $token;
            $adminEmail->save();

            $response = [
                'success'=>true,
                'message' => 'Login company successful',
                'token'=> $token,
                'id' => $adminEmail->id,
                'company_id' => $adminEmail->company_id,
                'isAdmin' => true
            ];
        } else if($adminUserName && Hash::check($request->password, $adminUserName->password)){
            $credentials = ["username" => $request->account, "password" => $request->password];
            $token = self::getToken($credentials);
            $adminUserName->auth_token = $token;
            $adminUserName->save();

            $response = [
                'success'=>true,
                'message' => 'Login company successful',
                'token'=> $token,
                'id' => $adminUserName->id,
                'company_id' => $adminUserName->company_id,
                'isAdmin' => true
            ];
        }else{
            $response = ['error'=>true, 'message'=>'Record doesnt exists'];
            return response()->json($response, 400);
        }

        return response()->json($response, 201);
    }

    public function logoutCompany()
    {
        $this->guard()->logout();

        return response()->json(['success'=>true,'message' => 'Logged out']);
    }

    public function getAllDepartmentsOfCompany(Request $request){
        $idCompany = $request->idCompany;
        if(!isset($idCompany)){
            return response()->json(['error' => 1, 'message' => "idCompany is required"], 400);
        }
        try{
            $departments = Departments::where('company_id', $idCompany)->get();
        }catch (\Exception $e){
            return response()->json(['error'=>true, 'message'=> $e->getMessage()], 400);
        }

        return response()->json(['success'=>true,'message' => 'Got departments', 'department' => $departments]);
    }
    public function getAllEmployeesOfCompany(Request $request){
        $idCompany = $request->idCompany;
        if(!isset($idCompany)){
            return response()->json(['error' => 1, 'message' => "idCompany is required"], 400);
        }
        try{

        }catch (\Exception $e){
            return response()->json(['error'=>true, 'message'=> $e->getMessage()], 400);
        }
    }
}
