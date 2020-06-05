<?php

namespace App\Http\Controllers\Api;

use App\Accounts;
use App\Companies;
use App\Departments;
use App\ElementComments;
use App\Elements;
use App\Emails;
use App\Employees;
use App\Mail\ResetPasswordEmployee;
use App\Processes;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Mockery\Exception;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;
use Config;
use DB;
use Illuminate\Support\Facades\Mail;

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

    public function loginAccount(Request $request){
        $username = Accounts::where('username', $request->username)->first();
        if($username && Hash::check($request->password, $username->password)){
            if($username->online === 2){
                return response()->json(['error' => true, 'message' => 'Tài khoản đang được sử dụng'], 201);
            }
            $credentials = ["username" => $request->username, "password" => $request->password];
            $token = self::getToken($credentials);
            $username->auth_token = $token;
            $username->online = 2;
            $username->save();

            $response = [
                'success'=>true,
                'message' => 'Đăng nhập thành công',
                'token'=> $token,
                'account_id' => $username->id,
                'employee_id' => $username->employee_id,
                'isEmployee' => true
            ];
        }else{
            $response = ['error'=>true, 'message'=>'Tài khoản không tồn tại'];
            return response()->json($response, 201);
        }

        return response()->json($response, 200);
    }

    public function logoutAccount(Request $request)
    {
        $token = $request->token;
        if(!$token){
            return response()->json(['error' => true, 'message' => 'Xảy ra lỗi với token'], 201);
        }
        try{
            $account = Accounts::where('auth_token', $token)->first();
            $account->online = 1;
            $account->update();
        }catch (\Exception $e){
            return response()->json(['error' => true, 'message' => $e->getMessage()]);
        }
        $this->guard()->logout();

        return response()->json(['success'=>true,'message' => 'Đăng xuất thành công']);
    }

    public function getDataOfEmployee(Request $request){
        $token = $request->token;
        if(!isset($token)){
            return response()->json(['error' => 1, 'message' => "token is required"], 400);
        }
        try{
            $account = Accounts::where('auth_token', $token)->first();
            $employee = Employees::find($account->employee_id);
            $employee->role;
            $employee->processesEmployees;
            $employee->processesRoles;
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
            'username_account' => $account->username,
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
                    $employee->avatar = "/".$url;
                }
                $employee->update();
            }catch ( \Exception $e){
                return response()->json(['error' => 1, 'message' => $e->getMessage()], 400);
            }
            return response()->json(['success'=>true, 'message' => 'updated employee']);
        }
    }

    public function getProcessOfEmployeePaginate(Request $request){
        $token = $request->token;
        $page = $request->page;
        if(!isset($token)){
            return response()->json(['error' => 1, 'message' => "token is required"], 400);
        }
        if(!isset($page)){
            return response()->json(['error' => 1, 'message' => "page is required"], 400);
        }
        try{
            $account = Accounts::where('auth_token', $token)->first();
            $employee = Employees::find($account->employee_id);
            $processes = DB::table('processes')
                ->leftJoin('processes_employees', 'processes.id', '=', 'processes_employees.process_id')
                ->leftJoin('employees', 'processes_employees.employee_id', '=', 'employees.id')
                ->leftJoin('processes_roles', 'processes.id', '=', 'processes_roles.process_id')
                ->leftJoin('roles', 'processes_roles.role_id', '=', 'roles.id')
                ->where('employees.id',$employee->id)->orWhere('roles.id', $employee->role_id)
                ->select('processes.id as id',
                    'processes.name as name',
                    'processes.description as description',
                    'processes.created_at as created_at')
                ->forPage($page, 6)->get();

        }catch ( \Exception $e){
            return response()->json(['error' => 1, 'message' => $e->getMessage()], 400);
        }
        return response()->json(['success'=>true, 'message' => 'got paginate process', 'processes' => $processes]);
    }

    public function updateAccountOfEmployee(Request $request){
        $username = $request->username;
        $password = $request->password;
        $newPassword = $request->newPassword;
        $token = $request->tokenData;

        try{
            $account = Accounts::where('auth_token', $token)->first();
            if(!$account){
                return response()->json(['error' => 1, 'message' => "xảy ra lỗi với token"], 201);
            }
            if(Hash::check($password, $account->password)){
                $account->username = $username;
                $account->password = Hash::make($newPassword);
                $account->update();
            }else{
                return response()->json(['error' => 1, 'message' => "Không đúng mật khẩu", "password" => true],  201);
            }
        }catch (\Exception $e){
            return response()->json(['error' => 1, 'message' => $e->getMessage()], 201);
        }
        return response()->json(['success'=>true, 'message' => 'Cập nhật tài khoản thành công']);
    }

    public function searchProcesses(Request $request){
        $search = $request->search;
        if(!$search){
            return response()->json(['error' => 1, 'message' => "search is required"], 400);
        }
        try{
            $processes = Processes::where('name',  'LIKE', '%' . $search . '%')->get();
        }catch (\Exception $e){
            return response()->json(['error' => 1, 'message' => $e->getMessage()], 400);
        }
        return response()->json(['success'=>true, 'message' => 'search processes', 'processes' => $processes]);
    }

    public function getFiveNotification(Request $request){
        $token = $request->token;
        if(!$token){
            return response()->json(['error' => 1, 'message' => "token is required"], 400);
        }
        try{
            $account = Accounts::where('auth_token', $token)->first();
            $employee = Employees::find($account->employee_id);
            $notifications = DB::table('processes')
                ->leftJoin('processes_employees', 'processes.id', '=', 'processes_employees.process_id')
                ->leftJoin('employees', 'processes_employees.employee_id', '=', 'employees.id')
                ->leftJoin('processes_roles', 'processes.id', '=', 'processes_roles.process_id')
                ->leftJoin('roles', 'processes_roles.role_id', '=', 'roles.id')
                ->where('employees.id',$employee->id)->orWhere('roles.id', $employee->role_id)
                ->select('processes.id as id',
                    'processes.name as name',
                    'processes.created_at as created_at')->take(5)->orderBy('created_at', 'desc')
                ->get();
        }catch (\Exception $e){
            return response()->json(['error' => 1, 'message' => $e->getMessage()], 400);
        }
        return response()->json(['success' => true, 'message' => "got 3 notifications", "notifications"=> $notifications], 200);
    }

    public function resetPasswordForEmployee(Request $request){
        $emailData = $request->email;
        if(!$emailData){
            return response()->json(['error' => 1, 'message' => "Email is required"], 400);
        }
        try{
            $employee = Employees::where('email', $emailData)->first();
            if(!$employee){
                return response()->json(['error' => 1, 'message' => "Email này không hợp lệ"], 201);
            }
            $account = Accounts::where('employee_id', $employee->id)->first();
        }catch (\Exception $e){
            return response()->json(['error' => 1, 'message' => $e->getMessage()], 400);
        }

        try{
            Mail::to($emailData)->send(new ResetPasswordEmployee($account));
        }catch (\Exception $e){
            $email = new Emails();
            $email->type = "Reset Password Employee";
            $email->to = $emailData;
            $email->status = 2;
            $email->response = $e->getMessage();
            $email->save();
            return response()->json(['error' => 1, 'message' => $e->getMessage()], 201);
        }

        $email = new Emails();
        $email->type = "Reset Password Employee";
        $email->to = $emailData;
        $email->response = "success";
        $email->save();
        return response()->json(['success' => true, 'message' => "Hệ thống đã gửi email cho email của bạn"], 200);
    }

    public function handleResetPasswordForEmployee(Request $request){
        $id = $request->id;
        $newPassword = $request->newPassword;
        if(!$id){
            return response()->json(['error' => 1, 'message' => "id is required"], 400);
        }
        if(!$newPassword){
            return response()->json(['error' => 1, 'message' => "newPassword is required"], 400);
        }
        try{
            $account = Accounts::find($id);
            if(!$account){
                return response()->json(['error' => 1, 'message' => "xảy ra lỗi với id"], 201);
            }
            $account->password = Hash::make($newPassword);
            $account->update();
        }catch (\Exception $e){
            return response()->json(['error' => 1, 'message' => $e->getMessage()], 201);
        }
        return response()->json(['success' => true, 'message' => "cập nhật mật khẩu thành công"], 200);
    }

    public function addCommentForProcess(Request $request){
        $idProcess= $request->idProcess;
        $content = $request->comment;
        $time = $request->time;
        $element_name = $request->element_name;
        $token = $request->token;
        $type = $request->typeElement;
        if(!$idProcess){
            return response()->json(['error' => 1, 'message' => "idProcess is required"], 400);
        }
        if(!$content){
            return response()->json(['error' => 1, 'message' => "content is required"], 400);
        }
        if(!$time){
            return response()->json(['error' => 1, 'message' => "time is required"], 400);
        }
        if(!$element_name){
            return response()->json(['error' => 1, 'message' => "element_name is required"], 400);
        }
        if(!$token){
            return response()->json(['error' => 1, 'message' => "token is required"], 400);
        }
        if(!$type){
            return response()->json(['error' => 1, 'message' => "type is required"], 400);
        }
        try{
            $element = Elements::where('process_id', $idProcess)->where('element', $element_name)->first();
            $account = Accounts::where('auth_token', $token)->first();
            if(!$element){
                $element = new Elements();
                $element->element = $element_name;
                $element->type = $type;
                $element->process_id = $idProcess;
                $element->save();
            }
            if(!$account){
                return response()->json(['error' => 1, 'message' => "Xẩy ra lỗi với token"]);
            }
            $comment = new ElementComments();
            $comment->element_id = $element->id;
            $comment->employee_id = $account->employee_id;
            $comment->comment = $content;
            $comment->update_at = $time;
            $comment->save();
        }catch (\Exception $e){
            return response()->json(['error' => 1, 'message' => $e->getMessage()], 201);
        }
        return response()->json(['success' => true, 'message' => "Thêm mới bình luận thành công", "comment" => $comment], 200);
    }

    public function deleteCommentInProcess(Request $request){
        $id = $request->idComment;
        if(!$id){
            return response()->json(['error' => 1, 'message' => "id is required"], 400);
        }
        try{
            $comment = ElementComments::find($id);
            $comment->delete();
        }catch (\Exception $e){
            return response()->json(['error' => 1, 'message' => $e->getMessage()], 201);
        }
        return response()->json(['success' => true, 'message' => "Xóa bình luận thành công", "comment" => $comment], 200);
    }

    public function getInformationOfEmployee(Request $request){
        $token = $request->token;
        if(!isset($token)){
            return response()->json(['error' => 1, 'message' => "token is required"], 400);
        }
        try{
            $account = Accounts::where('auth_token', $token)->first();
            $employee = Employees::find($account->employee_id);
        }catch ( \Exception $e){
            return response()->json(['error' => 1, 'message' => $e->getMessage()], 400);
        }
        return response()->json(['success' => true, 'message' => "Lấy thông tin nhân viên thành công", "employee" => $employee], 200);
    }

    public function checkTokenOfEmployee(Request $request){
        $token = $request->token;
        if(!isset($token)){
            return response()->json(['error' => 1, 'message' => "token is required"], 400);
        }
        try{
            $isEmployeeLoggedIn = false;
            $account = Accounts::where('auth_token', $token);
            if($account){
                $isEmployeeLoggedIn = true;
            }
        }catch (\Exception $e){
            return response()->json(['error' => 1, 'message' => $e->getMessage()], 400);
        }
        return response()->json([
            'success' => true,
            'message' => "Kiểm tra token thành công",
            "employeeLoggedIn" => $isEmployeeLoggedIn],
            200);
    }
}
