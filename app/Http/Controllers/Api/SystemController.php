<?php

namespace App\Http\Controllers\Api;

use App\Admins;
use App\Companies;
use App\Departments;
use App\Emails;
use App\Fields;
use App\Mail\Reject;
use App\Mail\ResendEmail;
use App\Mail\SendAdminAccount;
use App\Processes;
use App\ProcessesFields;
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
use Carbon\Carbon;

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
                    'message' => 'Tài khoản hoặc mật khẩu không đúng',
                ]);
            }
        } catch (JWTException $e) {
            return response()->json([
                'error' => true,
                'message' => 'Tạo token lỗi',
            ]);
        }

        return $token;
    }

    public function loginSystem(Request $request){
        try {
            $system = Systems::where('email', $request->email)->get()->first();
            if ($system && Hash::check($request->password, $system->password)) {
                if($system->online === 2){
                    return response()->json(['error' => true, 'message' => 'Tài khoản đang được sử dụng'], 201);
                }
                $credentials = $request->only('email', 'password');
                $token = self::getToken($credentials);
                $system->auth_token = $token;
                $system->online = 2;
                $system->save();

                $response = [
                    'success' => true,
                    'message' => 'Đăng nhập thành công',
                    'token' => $token,
                    'id' => $system->id,
                    'isSystem' => true
                ];
            } else {
                $response = ['error' => true, 'message' => 'Tài khoản không tồn tại'];
                return response()->json($response, 201);
            }
        }catch (\Exception $e){
            $response = ['error' => true, 'message' => $e->getMessage()];
            return response()->json($response, 400);
        }

        return response()->json($response, 200);
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logoutSystem(Request $request)
    {
        $token = $request->token;
        if(!$token){
            return response()->json(['error' => true, 'message' => 'Xảy ra lỗi với token'], 201);
        }
        try{
            $system = Systems::where('auth_token', $token)->first();
            $system->online = 1;
            $system->update();
        }catch (\Exception $e){
            return response()->json(['error' => true, 'message' => $e->getMessage()]);
        }
        $this->guard()->logout();

        return response()->json(['success'=>true,'message' => 'Đăng xuất thành công']);
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
            try{
                $company = \App\Waitings::find($request->idRegistration);
            }catch (\Exception $e){
                return response()->json(['error' => true, 'message' => $e->getMessage()]);
            }
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
            return response()->json(['error' => true, 'message' => "Yêu cầu biến username"]);
        }else if(!$request->password){
            return response()->json(['error' => true, 'message' => "Yêu cầu biến password"]);
        }else if(!$request->idRegistration){
            return response()->json(['error' => true, 'message' => "Yêu cầu biến idRegistration"]);
        }else{
            try{
                $checkUsername = Admins::where('username', $request->username)->first();
                if($checkUsername){
                    return response()->json(['error' => true, 'errorUsername' => 1, 'message' => 'username đã được sử dụng']);
                }else{
                    $company = Companies::where('registration_id',$request->idRegistration)->first();
                    if(!$company){
                        return response()->json(['error' => true, 'message' => "Xảy ra lỗi với idRegistration"]);
                    }
                    $admin  = new Admins();
                    $admin->username = $request->username;
                    $admin->password = Hash::make($request->password);
                    $admin->initial_password = $request->password;
                    $admin->company_id = $company->id;
                    $admin->save();
                }
            }catch (\Exception $e){
                return response()->json(['error' => true, 'message' => $e->getMessage()]);
            }
            return response()->json(['success' => true, 'message' => "Tạo tài khoản thành công", 'admin' => $admin]);
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
            return response()->json(['error' => true, 'message' => "Yêu cầu biến idAdmin"]);
        }else if(!$request->tokenData){
            return response()->json(['error' => true, 'message' => "Yêu cầu biến tokenData"]);
        }else{
            try{
                $system = Systems::where('auth_token',$request->tokenData)->first();
                $admin = Admins::find($request->idAdmin);
                if(!$system){
                    return response()->json(['error' => true, 'message' => "Xảy ra lỗi với token"]);
                }
                if(!$admin){
                    return response()->json(['error' => true, 'message' => "Xảy ra lỗi với tài khoản công ty"]);
                }
                $company = Companies::find($admin->company_id);
            }catch (\Exception $e){
                return response()->json(['error' => true, 'message' => $e->getMessage()]);
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
                $email->content = json_encode([
                    'username'=>$admin->username,
                    'password'=> $admin->initial_password,
                    'recipientName' => $company->ceo,
                ]);
                $email->save();
                return response()->json(['error' => true, 'message' => $e->getMessage()]);
            }

            $email = new Emails();
            $email->type = "Send Accounts";
            $email->to = $company->contact;
            $email->system_id = $system->id;
            $email->response = "success";
            $email->content = json_encode([
                'username'=>$admin->username,
                'password'=> $admin->initial_password,
                'recipientName' => $company->ceo,
            ]);
            $email->save();
            return response()->json(['success' => true, 'message' => 'Đã gửi tài khoản cho email liên hệ']);
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
            $numberDepartments = Departments::all()->count();
            $numberCompanies = Companies::where('active',1)->count();
            $numberProcesses = Processes::all()->count();
            $statistic = [
                'departments' => $numberDepartments,
                'companies' => $numberCompanies,
                'processes' => $numberProcesses,
            ];
        }catch (ModelNotFoundException $exception){
            return response()->json(['error' => true, 'message' => $exception->getMessage()]);
        }
        return response()->json([
            'success' => true,
            'message' => "Got data successful",
            'companies' => $companies,
            'statistic' => $statistic
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
            return response()->json(['error' => true, 'message' => "Yêu cầu biến username"]);
        }else if(!$request->password){
            return response()->json(['error' => true, 'message' => "Yêu cầu biến password"]);
        }else if(!$request->idCompany){
            return response()->json(['error' => true, 'message' => "Yêu cầu biến idCompany"]);
        }else{
            try{
                $checkUsername = Admins::where('username', $request->username)->first();
                if($checkUsername){
                    return response()->json(['error' => true, 'errorUsername' => 1, 'message' => 'username đã được sử dụng']);
                }else{
                    $company = Companies::find($request->idCompany);
                    if(!$company){
                        return response()->json(['error' => true, 'message' => "xảy ra lỗi với idCompany"]);
                    }
                    $admin  = new Admins();
                    $admin->username = $request->username;
                    $admin->password = Hash::make($request->password);
                    $admin->initial_password = $request->password;
                    $admin->company_id = $company->id;
                    $admin->save();
                }
            }catch (\Exception $e){
                return response()->json(['error' => true, 'message' => $e->getMessage()]);
            }
            return response()->json(['success' => true, 'message' => "Tạo tài khoản cho công ty thành công", 'admin' => $admin]);
        }
    }

    public function getSentEmailInSystem(){
        try{
            $data = [];
            $emails = Emails::all();
            if($emails){
                foreach ($emails as $email){
                    $system_id = $email->system_id;
                    if($system_id){
                        $system = Systems::find($system_id);
                        $email->sender = ['username' => $system->username, 'email' => $system->email];
                    }
                    $data[] = $email;
                }
            }
        }catch (\Exception $e){
            return response()->json(['error' => true, 'message' => $e->getMessage()]);
        }
        return response()->json(['success' => true, 'message' => "get emails", 'email' => $data]);
    }

    public function getEmailInformation(Request $request){
        if(!$request->idEmail){
            return response()->json(['error' => true, 'message' => "idEmail is required"]);
        }else{
            try{
                $email = \App\Emails::find($request->idEmail);
                if($email->system_id){
                    $system = Systems::find($email->system_id);
                    $email->sender = ['username' => $system->username, 'email' => $system->email];
                }
            }catch (\Exception $e){
                return response()->json(['error' => true, 'message' => $e->getMessage()]);
            }
            return response()->json([
                'success' => true,
                'message' => "Get data successful",
                'email' => $email
            ]);
        }
    }

    public function resendEmail(Request $request){
        if(!$request->idEmail){
            return response()->json(['error' => true, 'message' => "idEmail is required"]);
        }else if(!$request->tokenData){
            return response()->json(['error' => true, 'message' => "tokenData is required"]);
        }else{
            try{
                $system = Systems::where('auth_token',$request->tokenData)->first();
                $email = Emails::find($request->idEmail);
                if(!$system){
                    return response()->json(['error' => true, 'message' => "Something was wrong with the token"]);
                }
                if(!$email){
                    return response()->json(['error' => true, 'message' => "Something was wrong with old email"]);
                }
            }catch (\Exception $e){
                return response()->json(['error' => true, 'message' => "Something was wrong with request data"]);
            }
            if($email->type === "Send Account"){
                try{
                    Mail::to($email->to)->send(new ResendEmail($email,$system));
                }catch (\Exception $e){
                    $email->system_id = $system->id;
                    $email->status = 2;
                    $email->response = $e->getMessage();
                    $email->updated_at = Carbon::now();
                    $email->update();
                    return response()->json(['error' => true, 'message' => $e->getMessage()]);
                }
                $email->system_id = $system->id;
                $email->response = "success";
                $email->updated_at = Carbon::now();
                $email->update();
                return response()->json(['success' => true, 'message' => 'sent account to company']);
            }else if($email->type === "Reject"){
                try{
                    Mail::to($email->to)->send(new ResendEmail($email, $system));
                }catch (\Exception $e){
                    $email->system_id = $system->id;
                    $email->status = 2;
                    $email->response = $e->getMessage();
                    $email->updated_at = Carbon::now();
                    $email->update();
                    return response()->json(['error' => true, 'message' => $e->getMessage()]);
                }

                $email->system_id = $system->id;
                $email->response = "success";
                $email->updated_at = Carbon::now();
                $email->update();

                return response()->json(['success' => true, 'message' => 'sent reject email']);
            }
        }
    }

    public function getSystemAccountInformation(Request $request){
        $token = $request->token;
        if(!$token){
            return response()->json(['error' => true, 'message' => "token is required"]);
        }
        try{
            $system = Systems::where('auth_token', $token)->first();
            $data = ['name' => $system->username, 'email' => $system->email];
        }catch (\Exception $e){
            return response()->json(['error' => true, 'message' => $e->getMessage()]);
        }
        return response()->json(['success' => true, 'message' => 'got system account information', 'system' => $data]);
    }

    public function checkTokenOfSystem(Request $request){
        $token = $request->token;
        if(!isset($token)){
            return response()->json(['error' => 1, 'message' => "token is required"], 400);
        }
        try{
            $isSystemLoggedIn = false;
            $system = Systems::where('auth_token', $token);
            if($system){
                $isSystemLoggedIn = true;
            }
        }catch (\Exception $e){
            return response()->json(['error' => 1, 'message' => $e->getMessage()], 400);
        }
        return response()->json([
            'success' => true,
            'message' => "Kiểm tra token thành công",
            "systemLoggedIn" => $isSystemLoggedIn],
            200);
    }
    public function searchCompaniesInSystem(Request $request){
        $search = $request->search;
        try{
            if(!$search){
                $companies = Companies::all();
            }else{
                $companies = Companies::where('active',1)
                    ->where('name', 'LIKE', '%' . $search . '%')
                    ->orWhere('ceo', 'LIKE', '%' . $search . '%')
                    ->orWhere('contact', 'LIKE', '%' . $search . '%')
                    ->get();
            }
        }catch (\Exception $e){
            return response()->json(['error' => 1, 'message' => $e->getMessage()], 400);
        }
        return response()->json([
            'success' => true,
            'message' => "Tìm kiếm thành công",
            "companies" => $companies],
            200);
    }

    public function searchCompaniesRegistrationInSystem(Request $request){
        $search = $request->search;
        try{
            if(!$search){
                $registration = Waitings::all();
            }else{
                $registration = Waitings::where('approve', '<', 1)
                    ->where('name', 'LIKE', '%' . $search . '%')
                    ->orWhere('ceo', 'LIKE', '%' . $search . '%')
                    ->orWhere('contact', 'LIKE', '%' . $search . '%')
                    ->get();
            }
        }catch (\Exception $e){
            return response()->json(['error' => 1, 'message' => $e->getMessage()], 400);
        }
        return response()->json([
            'success' => true,
            'message' => "Tìm kiếm thành công",
            "registration" => $registration],
            200);
    }

    public function searchEmailInSystem(Request $request){
        $search = $request->search;
        try{
            if(!$search){
                $email = Emails::all();
            }else{
                $email = Emails::where('type', 'LIKE', '%' . $search . '%')
                    ->orWhere('to', 'LIKE', '%' . $search . '%')
                    ->get();
            }
        }catch (\Exception $e){
            return response()->json(['error' => 1, 'message' => $e->getMessage()], 400);
        }
        return response()->json([
            'success' => true,
            'message' => "Tìm kiếm thành công",
            "email" => $email],
            200);
    }

    public function searchTemplateInField(Request $request){
        $search = $request->search;
        $fieldId = $request->fieldId;
        try{
            if(!$fieldId){
                return response()->json(['error' => 1, 'message' => "Xảy ra lỗi với lĩnh vực"], 201);
            }
            if(!$search){
                $processes = ProcessesFields::where('field_id',$fieldId)->get();
            }else{
                $processes = ProcessesFields::where('field_id', $fieldId)
                    ->where('name', 'LIKE', '%' . $search . '%')
                    ->get();
            }
        }catch (\Exception $e){
            return response()->json(['error' => 1, 'message' => $e->getMessage()], 400);
        }
        return response()->json([
            'success' => true,
            'message' => "Tìm kiếm thành công",
            "processes" => $processes],
            200);
    }

    public function searchFieldInSystem(Request $request){
        $search = $request->search;
        try{
            if(!$search){
                $field = Fields::all();
            }else{
                $field = Fields::where('name', 'LIKE', '%' . $search . '%')->get();
            }
        }catch (\Exception $e){
            return response()->json(['error' => 1, 'message' => $e->getMessage()], 400);
        }
        return response()->json([
            'success' => true,
            'message' => "Tìm kiếm thành công",
            "field" => $field],
            200);
    }
}
