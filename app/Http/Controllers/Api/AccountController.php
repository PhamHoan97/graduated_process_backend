<?php

namespace App\Http\Controllers\Api;

use App\Accounts;
use App\Companies;
use App\Departments;
use App\Employees;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;
use Config;

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
        $username = Accounts::where('username', $request->username)->first();
        if($username && Hash::check($request->password, $username->password)){
            $credentials = ["username" => $request->username, "password" => $request->password];
            $token = self::getToken($credentials);
            $username->auth_token = $token;
            $username->save();

            $response = [
                'success'=>true,
                'message' => 'Login account successful',
                'token'=> $token,
                'id' => $username->id,
                'employee_id' => $username->employee_id,
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

    public function getDataOfEmployee(Request $request){
        $idEmployee = $request->idEmployee;
        if(!isset($idEmployee)){
            return response()->json(['error' => 1, 'message' => "idEmployee is required"], 400);
        }
        try{
            $employee = Employees::find($idEmployee);
            $employee->role;
            $employee->processes;
            $department_id = $employee['department_id'];
            $department = Departments::find($department_id);
            $company = Companies::find($department['company_id']);

        }catch ( \Exception $e){
            return response()->json(['error' => 1, 'message' => $e->getMessage()], 400);
        }
        return response()->json([
            'success'=>true,
            'message' => 'get data of employee',
            'employee' => $employee,
            'department' => $department,
            'company' => $company,
        ]);
    }

    public function updateInformationOfEmployee(Request $request){
        $name = $request->name;
        $email = $request->email;
        $token = $request->tokenData;
        $birth = $request->birth;
        $address = $request->address;
        $phone = $request->phone;
        $about_me = $request->about_me;
        $checkEmployee = Accounts::where('auth_token',$token)->first();
        if(!$checkEmployee){
            return response()->json(['error' => 1, 'message' => "something was wrong with token"], 400);
        }else{
            try{
                $employee = Employees::find($checkEmployee->employee_id);
                $employee->name = $name;
                $employee->email = $email;
                $employee->birth = $birth;
                $employee->address = $address;
                $employee->phone = $phone;
                $employee->about_me = $about_me;
                if($request->hasFile('avatar')){
                    $file = $request->file('avatar');
                    $photo_name = mt_rand();
                    $type = $file->getClientOriginalExtension();
                    $link = "avatar/employee/";
                    $file->move($link,$photo_name.".".$type);
                    $url = $link.$photo_name.".".$type;
                    $employee->avatar = $url;
                }
                $employee->update();
            }catch ( \Exception $e){
                return response()->json(['error' => 1, 'message' => $e->getMessage()], 400);
            }
            return response()->json(['success'=>true, 'message' => 'updated employee']);
        }
    }
}
