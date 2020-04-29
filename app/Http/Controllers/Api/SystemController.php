<?php

namespace App\Http\Controllers\Api;

use App\Admins;
use App\Companies;
use App\Emails;
use App\Mail\Reject;
use App\Mail\SendAdminAccount;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use App\Systems;
use App\Waitings;
use Config;
use Illuminate\Support\Facades\Mail;
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
                    'token' => $token,
                    'id' => $system->id,
                    'isSystem' => true
                ];
            } else {
                $response = ['error' => true, 'message' => 'Record doesn\'t exists'];
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
            $registrations = Waitings::where('approve',0)->get();
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
        if(!$request->idRegistration){
            return response()->json(['error' => true, 'message' => "idRegistration is required"]);
        }else{
            $company = \App\Waitings::find($request->idRegistration);
            return response()->json([
                'success' => true,
                'message' => "Get data successful",
                'information' => $company
            ]);
        }
    }

    public  function approveCompany(Request $request){
        if(!$request->idRegistration){
            return response()->json(['error' => true, 'message' => "idRegistration is required"]);
        }else if(!$request->tokenData){
            return response()->json(['error' => true, 'message' => "tokenData is required to verify user"]);
        }else{
            try{
                $system = Systems::where('auth_token',$request->tokenData)->first();
                if(!$system){
                    return response()->json(['error' => true, 'message' => "Something was wrong with the token"]);
                }
                //save data
                $registration = Waitings::find($request->idRegistration);
                $registration->approve = 1;
                $registration->approve_by = $system->id;
                $registration->save();
                //insert to Company table
                $company = new Companies();
                $company->name = $registration->name;
                $company->signature = $registration->signature;
                $company->address = $registration->address;
                $company->ceo = $registration->ceo;
                $company->field = $registration->field;
                $company->workforce = $registration->workforce;
                $company->contact = $registration->contact;
                $company->registration_id = $request->idRegistration;
                $company->save();
                //send email
            }catch (\Exception $e){
                return response()->json(['error' => true, 'message' => $e->getMessage()]);
            }
            return response()->json(['success' => true, 'message' => "Approved this company"]);
        }
    }

    public function createAdmin(Request $request){
        if(!$request->username){
            return response()->json(['error' => true, 'message' => "username is required"]);
        }else if(!$request->password){
            return response()->json(['error' => true, 'message' => "password is required"]);
        }else if(!$request->idRegistration){
            return response()->json(['error' => true, 'message' => "idRegistration is required"]);
        }else{
            try{
                $checkUsername = Admins::where('username', $request->username)->first();
                if($checkUsername){
                    return response()->json(['error' => true, 'errorUsername' => 1, 'message' => 'username must be unique']);
                }else{
                    $company = Companies::where('registration_id',$request->idRegistration)->first();
                    if(!$company){
                        return response()->json(['error' => true, 'message' => "something was wrong with idRegistration"]);
                    }
                    $admin  = new Admins();
                    $admin->username = $request->username;
                    $admin->email = $company->contact;
                    $admin->password = Hash::make($request->password);
                    $admin->initial_password = $request->password;
                    $admin->company_id = $company->id;
                    $admin->save();
                }
            }catch (\Exception $e){
                return response()->json(['error' => true, 'message' => $e->getMessage()]);
            }
            return response()->json(['success' => true, 'message' => "Created account", 'admin' => $admin]);
        }
    }

    public function getAdminAccountsOfCompany(Request $request){
        if(!$request->idCompany){
            return response()->json(['error' => true, 'message' => "idCompany is required"]);
        }else{
            try{
                $admins = Admins::where('company_id', $request->idCompany)->get();
            }catch (\Exception $e){
                return response()->json(['error' => true, 'message' => $e->getMessage()]);
            }
            return response()->json(['success' => true, 'message' => 'got admin accounts of the company', 'admins' => $admins]);
        }
    }

    public function sendEmailAdminAccount(Request $request){
        if(!$request->idAdmin){
            return response()->json(['error' => true, 'message' => "idAdmin is required"]);
        }else if(!$request->tokenData){
            return response()->json(['error' => true, 'message' => "tokenData is required"]);
        }else{
            try{
                $system = Systems::where('auth_token',$request->tokenData)->first();
                $admin = Admins::find($request->idAdmin);
                if(!$system){
                    return response()->json(['error' => true, 'message' => "Something was wrong with the token"]);
                }
                if(!$admin){
                    return response()->json(['error' => true, 'message' => "Something was wrong with the admin account"]);
                }
                $company = Companies::find($admin->company_id);
            }catch (\Exception $e){
                return response()->json(['error' => true, 'message' => "Something was wrong with request data"]);
            }

            try{
                Mail::to($company->contact)->send(new SendAdminAccount($admin,$system,$company));
            }catch (\Exception $e){
                $email = new Emails();
                $email->type = "Send Accounts";
                $email->to = $company->contact;
                $email->system_id = $system->id;
                $email->status = 2;
                $email->response = $e->getMessage();
                $email->save();
                return response()->json(['error' => true, 'message' => $e->getMessage()]);
            }

            $email = new Emails();
            $email->type = "Send Accounts";
            $email->to = $company->contact;
            $email->system_id = $system->id;
            $email->response = "success";
            $email->save();
            return response()->json(['success' => true, 'message' => 'sent account to company']);
        }
    }

    public function sendRejectEmail(Request $request){
        if(!$request->idRegistration){
            return response()->json(['error' => true, 'message' => "idRegistration is required"]);
        }else if(!$request->tokenData){
            return response()->json(['error' => true, 'message' => "tokenData is required"]);
        }else if(!$request->reason){
            return response()->json(['error' => true, 'message' => "reason is required"]);
        }else{
            try{
                $registration = Waitings::find($request->idRegistration);
                $system = Systems::where('auth_token',$request->tokenData)->first();
                if(!$system){
                    return response()->json(['error' => true, 'message' => "Something was wrong with the token"]);
                }
                if(!$registration){
                    return response()->json(['error' => true, 'message' => "Something was wrong with the registration"]);
                }
            }catch (\Exception $e){
                return response()->json(['error' => true, 'message' => "Something was wrong with request data"]);
            }

            try{
                Mail::to($registration->contact)->send(new Reject($request->reason,$registration,$system));
            }catch (\Exception $e){
                $email = new Emails();
                $email->type = "Reject";
                $email->to = $registration->contact;
                $email->content = $request->reason;
                $email->system_id = $system->id;
                $email->status = 2;
                $email->response = $e->getMessage();
                $email->save();
                return response()->json(['error' => true, 'message' => $e->getMessage()]);
            }

            $email = new Emails();
            $email->type = "Reject";
            $email->to = $registration->contact;
            $email->content = $request->reason;
            $email->system_id = $system->id;
            $email->response = "success";
            $email->save();

            $registration->approve = 2;
            $registration->approve_by = $system->id;
            $registration->save();

            return response()->json(['success' => true, 'message' => 'sent reject email']);
        }
    }

    public function getListCompanies(Request $request){
        try{
            $companies = Companies::where('active',1)->get();
        }catch (ModelNotFoundException $exception){
            return response()->json(['error' => true, 'message' => $exception->getMessage()]);
        }
        return response()->json([
            'success' => true,
            'message' => "Get data successful",
            'companies' => $companies
        ]);
    }

    public function getformationOfCompany(Request $request){
        if(!$request->idCompany){
            return response()->json(['error' => true, 'message' => "idCompany is required"]);
        }else{
            $company = \App\Companies::find($request->idCompany);
            return response()->json([
                'success' => true,
                'message' => "Get data successful",
                'information' => $company
            ]);
        }
    }

    public function moreAdmin(Request $request){
        if(!$request->username){
            return response()->json(['error' => true, 'message' => "username is required"]);
        }else if(!$request->password){
            return response()->json(['error' => true, 'message' => "password is required"]);
        }else if(!$request->idCompany){
            return response()->json(['error' => true, 'message' => "idCompany is required"]);
        }else{
            try{
                $checkUsername = Admins::where('username', $request->username)->first();
                if($checkUsername){
                    return response()->json(['error' => true, 'errorUsername' => 1, 'message' => 'username must be unique']);
                }else{
                    $company = Companies::find($request->idCompany);
                    if(!$company){
                        return response()->json(['error' => true, 'message' => "something was wrong with idCompany"]);
                    }
                    $admin  = new Admins();
                    $admin->username = $request->username;
                    $admin->email = $company->contact;
                    $admin->password = Hash::make($request->password);
                    $admin->initial_password = $request->password;
                    $admin->company_id = $company->id;
                    $admin->save();
                }
            }catch (\Exception $e){
                return response()->json(['error' => true, 'message' => $e->getMessage()]);
            }
            return response()->json(['success' => true, 'message' => "Created account", 'admin' => $admin]);
        }
    }
}
