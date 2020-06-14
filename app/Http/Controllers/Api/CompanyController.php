<?php

namespace App\Http\Controllers\Api;

use App\Admins;
use App\Companies;
use App\ElementComments;
use App\ElementNotes;
use App\Elements;
use App\Emails;
use App\Employees;
use App\Fields;
use App\Processes;
use App\ProcessesCompanies;
use App\ProcessesDepartments;
use App\ProcessesEmployees;
use App\ProcessesFields;
use App\ProcessesRoles;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tymon\JWTAuth\Facades\JWTAuth;
use Config;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Support\Facades\Auth;
use App\Mail\ResetPasswordCompany;


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
            return response()->json(["success" => true, "message" => "This email contact is used by someone"], 201);
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
                    'message' => 'Tài khoản hoặc mật khẩu không đúng',
                ]);
            }
        } catch (JWTException $e) {
            return response()->json([
                'error' => true,
                'message' => 'Tạo token bị lỗi',
            ]);
        }

        return $token;
    }

    public function loginCompany(Request $request){
        $adminUserName = Admins::where('username', $request->username)->first();
        if($adminUserName && Hash::check($request->password, $adminUserName->password)){
            if($adminUserName->online === 2){
                return response()->json(['error' => true, 'message' => 'Tài khoản đang được sử dụng'], 201);
            }
            $credentials = ["username" => $request->username, "password" => $request->password];
            $token = self::getToken($credentials);
            $adminUserName->auth_token = $token;
            $adminUserName->online = 2;
            $adminUserName->save();

            $response = [
                'success'=>true,
                'message' => 'Đăng nhập thành công',
                'token'=> $token,
                'id' => $adminUserName->id,
                'company_id' => $adminUserName->company_id,
                'isAdmin' => true
            ];
        }else{
            $response = ['error'=>true, 'message'=>'Tài khoản này không tồn tại'];
            return response()->json($response, 201);
        }

        return response()->json($response, 200);
    }

    public function logoutCompany(Request $request)
    {
        $token = $request->token;
        if(!$token){
            return response()->json(['error' => true, 'message' => 'Xảy ra lỗi với token'], 201);
        }
        try{
            $admin = Admins::where('auth_token', $token)->first();
            $admin->online = 1;
            $admin->update();
        }catch (\Exception $e){
            return response()->json(['error' => true, 'message' => $e->getMessage()]);
        }
        $this->guard()->logout();

        return response()->json(['success'=>true,'message' => 'Đăng xuất thành công']);
    }

    public function getAllEmployeeAndRoleOfDepartment(Request $request){
        $idDepartment = $request->idDepartment;
        if(!isset($idDepartment)){
            return response()->json(['error' => 1, 'message' => "idDepartment is required"], 400);
        }
        try{
            $employees = DB::table('departments')
                ->join('employees', 'departments.id', '=', 'employees.department_id')
                ->join('roles', 'employees.role_id', '=', 'roles.id')
                ->where('departments.id', $idDepartment)
                ->select('employees.id as id_employee',
                    'employees.name as name',
                    'employees.email as email',
                    'employees.phone as phone',
                    'employees.address as address',
                    'employees.birth as birth',
                    'employees.avatar as avatar',
                    'employees.gender as gender',
                    'departments.id as id_department',
                    'departments.name as department_name',
                    'roles.name as role_name')
                ->get();
            $roles = DB::table('departments')
                ->join('roles', 'departments.id', '=', 'roles.department_id')
                ->where('departments.id', $idDepartment)
                ->select(
                    'roles.name as role',
                    'roles.id as id_role',
                    'departments.id as id_department',
                    'departments.name as department_name')
                ->get();
        }catch (\Exception $e){
            return response()->json(['error'=>true, 'message'=> $e->getMessage()], 400);
        }
        return response()->json(['success'=>true, 'message'=> "got employees from department", "employees" => $employees, "roles"=> $roles]);
    }

    public function newProcessCompany(Request $request){
        $tọken = $request->token;
        $information = json_decode($request->information);
        $xml = $request->xml;
        $elements = json_decode($request->elements);

        try{
            $admin = Admins::where('auth_token',$tọken)->first();
            if(!$admin){
                return response()->json(['error' => true, 'message' => "Xảy ra lỗi với token"]);
            }
            //create process
            $process = new Processes();
            $process->code = $information->code;
            $process->name = $information->name;
            $process->description = $information->description;
            $process->type = $information->type;
            $process->deadline = $information->deadline;
            $process->update_at = $information->time;
            $process->xml = $xml;
            $process->admin_id = $admin->id;
            if($request->hasFile('file')){
                $file = $request->file('file');
                    $photo_name = mt_rand();
                    $type = $file->getClientOriginalExtension();
                    $link = "file/";
                    $file->move($link,$photo_name.".".$type);
                    $url = $link.$photo_name.".".$type;
                    $process->document = $url;
            }
            $process->save();
            //save processes_employees or process_roles
            if($information->type === 1){
                $assign = $information->assign;
                foreach ($assign as $value){
                    $link = new ProcessesEmployees();
                    $link->process_id = $process->id;
                    $link->employee_id = $value->value;
                    $link->save();
                }
            }else if($information->type === 2){
                $assign = $information->assign;
                foreach ($assign as $value){
                    $link = new ProcessesRoles();
                    $link->process_id = $process->id;
                    $link->role_id = $value->value;
                    $link->save();
                }
            }else if($information->type === 3){
                $assign = $information->assign;
                foreach ($assign as $value){
                    $link = new ProcessesDepartments();
                    $link->process_id = $process->id;
                    $link->department_id = $value->value;
                    $link->save();
                }
            }else if($information->type === 4){
                $link = new ProcessesCompanies();
                $link->process_id = $process->id;
                $link->company_id = $admin->company_id;
                $link->save();
            }
            //save elements
            foreach ($elements as $value){
                if($value->note || $value->comments){
                    $element = new Elements();
                    $element->element = $value->id;
                    $element->type = $value->type;
                    $element->process_id = $process->id;
                    $element->save();
                    //save note element
                    if($value->note){
                        $note = new ElementNotes();
                        $note->element_id = $element->id;
                        $note->admin_id = $admin->id;
                        $note->content = $value->note;
                        $note->save();
                    }
                    //save comment
                    if($value->comments){
                        foreach ($value->comments as $index){
                            $comment =  new ElementComments();
                            $comment->element_id = $element->id;
                            $comment->admin_id = $admin->id;
                            $comment->comment = $index->content;
                            $comment->update_at = $index->time;
                            $comment->save();
                        }
                    }
                }
            }

        }catch (\Exception $e){
            return response()->json(['error' => true, 'message' =>$e->getMessage()]);
        }
        return response()->json(['success' => true, 'message' => "Lưu quy trình thành công", "process" => $process]);
    }

    public function getAllInformationOfProcess(Request $request){
        $idProcess = $request->idProcess;
        if(!isset($idProcess)){
            return response()->json(['error' => 1, 'message' => "idProcess is required"], 400);
        }else{
            try{
                $process = Processes::find($idProcess);
                $process->employees;
                $process->elementNotes;
                $comments = $process->elementComments;
                $process->elements;
                $process->roles;
                $process->departments;
                $newComments = [];
                foreach ($comments as $comment){
                    if($comment->employee_id){
                        $employee = Employees::find($comment->employee_id);
                        $comment->employee_name = $employee->name;
                    }
                    $newComments[] = $comment;
                }
                $process->element_comments = $newComments;
            }catch (\Exception $e){
                return response()->json(['error' => true, 'message' =>$e->getMessage()]);
            }
            return response()->json(
                [
                    'success' => true, 'message' => "got process",
                    "process" => $process
                ]);
        }
    }

    public function editProcessCompany(Request $request){
        $tọken =  $request->token;
        $information = json_decode($request->information);
        $xml = $request->xml;
        $elements =  json_decode($request->elements);

        try{
            $admin = Admins::where('auth_token',$tọken)->first();
            if(!$admin){
                return response()->json(['error' => true, 'message' => "Xảy ra lỗi với token"]);
            }
            $processId = $information->id;
            if(!$processId){
                return response()->json(['error' => true, 'message' => "Xảy ra lỗi với id của quy trình"]);
            }
            $process = Processes::find($processId);
            if(!$process){
                return response()->json(['error' => true, 'message' => "Xảy ra lỗi với quy trình"]);
            }
            //update process
            $process->name = $information->name;
            $process->description = $information->description;
            $process->update_at = $information->time;
            $process->xml = $xml;
            $process->deadline = $information->deadline;
            $process->type = $information->type;
            $process->admin_id = $admin->id;
            $process->save();
            //remove assign employee
            $deleteAssignsEmployee = ProcessesEmployees::where('process_id', $processId)->delete();
            //remove assign role
            $deleteAssignsEmployee = ProcessesRoles::where('process_id', $processId)->delete();
            //remove assign department
            $deleteAssignsDepartment= ProcessesDepartments::where('process_id', $processId)->delete();
            //remove assign company
            $deleteAssignsCompany= ProcessesCompanies::where('process_id', $processId)->delete();
            //remove old elements
            $deletedElements = Elements::where('process_id', $processId)->delete();
            //update processes_employees
            if($information->type === 1){
                $assign = $information->assign;
                foreach ($assign as $value){
                    $link = new ProcessesEmployees();
                    $link->process_id = $process->id;
                    $link->employee_id = $value->value;
                    $link->save();
                }
            }else if($information->type === 2){
                $assign = $information->assign;
                foreach ($assign as $value){
                    $link = new ProcessesRoles();
                    $link->process_id = $process->id;
                    $link->role_id = $value->value;
                    $link->save();
                }
            }else if($information->type === 3){
                $assign = $information->assign;
                foreach ($assign as $value){
                    $link = new ProcessesDepartments();
                    $link->process_id = $process->id;
                    $link->department_id = $value->value;
                    $link->save();
                }
            }else if($information->type === 4){
                $link = new ProcessesCompanies();
                $link->process_id = $process->id;
                $link->company_id = $admin->company_id;
                $link->save();
            }
            //update elements
            foreach ($elements as $value){
                if($value->note || $value->comments){
                    $element = new Elements();
                    $element->element = $value->id;
                    $element->type = $value->type;
                    $element->process_id = $process->id;
                    $element->save();
                    //update note element
                    if($value->note){
                        $note = new ElementNotes();
                        $note->element_id = $element->id;
                        $note->admin_id = $admin->id;
                        $note->content = $value->note;
                        $note->save();
                    }
                    //update comment
                    if($value->comments){
                        foreach ($value->comments as $index){
                            $comment =  new ElementComments();
                            $comment->element_id = $element->id;
                            $comment->comment = $index->content;
                            $comment->update_at = $index->time;
                            if(isset($index->employee_id)){
                                $comment->employee_id = $index->employee_id;
                            }else{
                                $comment->admin_id = $admin->id;
                            }
                            $comment->save();
                        }
                    }
                }
            }
        }catch (\Exception $e){
            return response()->json(['error' => true, 'message' =>$e->getMessage()]);
        }
        return response()->json(['success' => true, 'message' => "Sửa quy trình thành công", "process" => $process]);
    }

    public function getAllEmployeeAndRoleOfCompany(Request $request){
        $token = $request->token;
        if(!$token){
            return response()->json(['error' => true, 'message' => "token is required"]);
        }
        try {
            $admin = Admins::where('auth_token', $token)->first();
            $idCompany = $admin->company_id;
            $employees = DB::table('companies')
                ->join('departments', 'companies.id', '=', 'departments.company_id')
                ->join('employees', 'departments.id', '=', 'employees.department_id')
                ->join('roles', 'employees.role_id', '=', 'roles.id')
                ->where('companies.id', $idCompany)
                ->select('employees.id as id_employee',
                    'employees.name as name',
                    'employees.email as email',
                    'employees.phone as phone',
                    'employees.address as address',
                    'employees.birth as birth',
                    'employees.avatar as avatar',
                    'employees.gender as gender',
                    'departments.id as id_department',
                    'departments.name as department_name',
                     'roles.name as role_name')
                ->get();

            $roles = DB::table('companies')
                ->join('departments', 'companies.id', '=', 'departments.company_id')
                ->join('roles', 'departments.id', '=', 'roles.department_id')
                ->where('companies.id', $idCompany)
                ->select(
                    'roles.name as role',
                    'roles.id as id_role',
                    'departments.id as id_department',
                    'departments.name as department_name')
                ->get();

            $departments = DB::table('companies')
                ->join('departments', 'companies.id', '=', 'departments.company_id')
                ->where('companies.id', $idCompany)
                ->select(
                    'departments.id as id_department',
                    'departments.name as department_name')
                ->get();
            return response()->json([
                    'message'=>'Got all users, roles and departments in company',
                    'employees'=>$employees,
                    'roles' => $roles,
                    'departments' => $departments,
                ],200);
        }catch(\Exception $e) {
            return response()->json(["error" => $e->getMessage()],400);
        }
    }

    public function getAllProcessesOfCompany(Request $request){
        $token = $request->token;
        if(!$token){
            return response()->json(['error' => true, 'message' => "token is required"]);
        }
        try{
            $admin = Admins::where('auth_token', $token)->first();
            $company_id = $admin->company_id;
            $processes1 = DB::table('processes')
                ->leftJoin('processes_employees', 'processes.id', '=', 'processes_employees.process_id')
                ->leftJoin('employees', 'processes_employees.employee_id', '=', 'employees.id')
                ->leftJoin('departments', 'employees.department_id', '=', 'departments.id')
                ->leftJoin('companies', 'departments.company_id', '=', 'companies.id')
                ->where('companies.id',$company_id)
                ->select('processes.id as id',
                    'processes.name as name',
                    'processes.code as code',
                    'processes.description as description',
                    'processes.type as type',
                    'processes.created_at as created_at'
                )->distinct()
                ->get();
            $processes2 = DB::table('processes')
                ->leftJoin('processes_roles', 'processes.id', '=', 'processes_roles.process_id')
                ->leftJoin('roles', 'processes_roles.role_id', '=', 'roles.id')
                ->leftJoin('departments', 'roles.department_id', '=', 'departments.id')
                ->leftJoin('companies', 'departments.company_id', '=', 'companies.id')
                ->where('companies.id',$company_id)
                ->select('processes.id as id',
                    'processes.code as code',
                    'processes.name as name',
                    'processes.description as description',
                    'processes.type as type',
                    'processes.created_at as created_at'
                )->distinct()
                ->get();
            $processes3 = DB::table('processes')
                ->leftJoin('processes_departments', 'processes.id', '=', 'processes_departments.process_id')
                ->leftJoin('departments', 'processes_departments.department_id', '=', 'departments.id')
                ->leftJoin('companies', 'departments.company_id', '=', 'companies.id')
                ->where('companies.id',$company_id)
                ->select('processes.id as id',
                    'processes.code as code',
                    'processes.name as name',
                    'processes.description as description',
                    'processes.type as type',
                    'processes.created_at as created_at'
                )->distinct()
                ->get();
            $processes4 = DB::table('processes')
                ->leftJoin('processes_companies', 'processes.id', '=', 'processes_companies.process_id')
                ->leftJoin('companies', 'processes_companies.company_id', '=', 'companies.id')
                ->where('companies.id',$company_id)
                ->select('processes.id as id',
                    'processes.code as code',
                    'processes.name as name',
                    'processes.description as description',
                    'processes.type as type',
                    'processes.created_at as created_at'
                )->distinct()
                ->get();
         }catch (\Exception $e){
            return response()->json(["error" => $e->getMessage()],400);
        }
        return response()->json(
            [
                'message'=>'got all processes of company',
                'processes1' => $processes1,
                'processes2' => $processes2,
                'processes3' => $processes3,
                'processes4' => $processes4,
            ],200);
    }

    public function getAllEmployeesOfCompany(Request $request){
        $token = $request->token;
        if(!$token){
            return response()->json(['error' => true, 'message' => "token is required"]);
        }
        try{
            $admin = Admins::where('auth_token', $token)->first();
            $company_id = $admin->company_id;
            $company = Companies::find($company_id);
        }catch (\Exception $e){
            return response()->json(["error" => $e->getMessage()],400);
        }
        return response()->json(['message'=>'Got all employees in company ','employees'=> $company->employees],200);
    }

    public function getAllProcessesOfADepartmentOfCompany(Request $request){
        $idDepartment = $request->idDepartment;
        if(!$idDepartment){
            return response()->json(['error' => true, 'message' => "idDepartment is required"]);
        }
        try{
            $processes1 = DB::table('processes')
                ->leftJoin('processes_roles', 'processes.id', '=', 'processes_roles.process_id')
                ->leftJoin('roles', 'processes_roles.role_id', '=', 'roles.id')
                ->leftJoin('departments', 'roles.department_id', '=', 'departments.id')
                ->where('departments.id',$idDepartment)
                ->select('processes.id as id',
                    'processes.name as name',
                    'processes.description as description',
                    'processes.type as type',
                    'processes.created_at as created_at'
                )->distinct()
                ->get();

            $processes2 = DB::table('processes')
                ->leftJoin('processes_employees', 'processes.id', '=', 'processes_employees.process_id')
                ->leftJoin('employees', 'processes_employees.employee_id', '=', 'employees.id')
                ->leftJoin('departments', 'employees.department_id', '=', 'departments.id')
                ->where('departments.id',$idDepartment)
                ->select('processes.id as id',
                    'processes.name as name',
                    'processes.description as description',
                    'processes.type as type',
                    'processes.created_at as created_at'
                )->distinct()
                ->get();
        }catch (\Exception $e){
            return response()->json(["error" => $e->getMessage()],400);
        }
        return response()->json(
            [
                'message'=>'got all processes of a department of company',
                'processes1' => $processes1,
                'processes2' => $processes2,
            ],200);
    }

    public function getAllProcessesOfAEmployeeOfCompany(Request $request){
        $idEmployee = $request->idEmployee ;
        if(!$idEmployee){
            return response()->json(['error' => true, 'message' => "idEmployee is required"]);
        }
        try{
            $employee = Employees::find($idEmployee);
            $processes1 = DB::table('processes')
                ->leftJoin('processes_roles', 'processes.id', '=', 'processes_roles.process_id')
                ->leftJoin('roles', 'processes_roles.role_id', '=', 'roles.id')
                ->leftJoin('employees', 'roles.id', '=', 'employees.role_id')
                ->where('employees.id',$idEmployee)
                ->select('processes.id as id',
                    'processes.code as code',
                    'processes.name as name',
                    'processes.description as description',
                    'processes.type as type',
                    'processes.created_at as created_at'
                )->distinct()
                ->get();

            $processes2 = DB::table('processes')
                ->leftJoin('processes_employees', 'processes.id', '=', 'processes_employees.process_id')
                ->leftJoin('employees', 'processes_employees.employee_id', '=', 'employees.id')
                ->where('employees.id',$idEmployee)
                ->select('processes.id as id',
                    'processes.code as code',
                    'processes.name as name',
                    'processes.description as description',
                    'processes.type as type',
                    'processes.created_at as created_at'
                )->distinct()
                ->get();

            $processes3 = DB::table('processes')
                ->leftJoin('processes_departments', 'processes.id', '=', 'processes_departments.process_id')
                ->leftJoin('departments', 'processes_departments.department_id', '=', 'departments.id')
                ->leftJoin('employees', 'departments.id', '=', 'employees.department_id')
                ->where('employees.id',$idEmployee)
                ->select('processes.id as id',
                    'processes.code as code',
                    'processes.name as name',
                    'processes.description as description',
                    'processes.type as type',
                    'processes.created_at as created_at'
                )->distinct()
                ->get();

            $processes4 = DB::table('processes')
                ->leftJoin('processes_companies', 'processes.id', '=', 'processes_companies.process_id')
                ->leftJoin('companies', 'processes_companies.company_id', '=', 'companies.id')
                ->leftJoin('departments', 'companies.id', '=', 'departments.company_id')
                ->leftJoin('employees', 'departments.id', '=', 'employees.department_id')
                ->where('employees.id',$idEmployee)
                ->select('processes.id as id',
                    'processes.code as code',
                    'processes.name as name',
                    'processes.description as description',
                    'processes.type as type',
                    'processes.created_at as created_at'
                )->distinct()
                ->get();

        }catch (\Exception $e){
            return response()->json(["error" => $e->getMessage()],400);
        }
        return response()->json(
            [
                'message'=>'got all processes of a department of company',
                'processes1' => $processes1,
                'processes2' => $processes2,
                'processes3' => $processes3,
                'processes4' => $processes4,
                'employee' => $employee
            ],200);
    }

    public function getAllFields(Request $request){
        try{
            $fields = Fields::all();
        }catch (\Exception $e){
            return response()->json(["error" => $e->getMessage()],400);
        }
        return response()->json(['message'=>'Got all fields','fields'=> $fields],200);
    }

    public function getAllProcessesTemplate(Request $request){
        try{
            $data = [];
            $processes = ProcessesFields::all();
            foreach ($processes as $process){
                $process->field;
                $data []= $process;
            }
        }catch (\Exception $e){
            return response()->json(["error" => $e->getMessage()],400);
        }
        return response()->json(['message'=>'Got all processes template','processes'=> $data],200);
    }

    public function getAllProcessesTemplateOfField(Request $request){
        $idField = $request->idField;
        if(!$idField){
            return response()->json(['error' => true, 'message' => "idField is required"]);
        }
        try{
            $processes = ProcessesFields::where('field_id',$idField)->get();
            $data = [];
            foreach ($processes as $process){
                $process->field;
                $data []= $process;
            }
        }catch (\Exception $e){
            return response()->json(["error" => $e->getMessage()],400);
        }
        return response()->json(['message'=>'Got all processes template of field','processes'=> $data],200);
    }

    public function getProcessTempalateWithId(Request $request){
        $idProcess = $request->idProcess;
        if(!$idProcess){
            return response()->json(['error' => true, 'message' => "idProcess is required"]);
        }
        try{
            $process = ProcessesFields::find($idProcess);
        }catch (\Exception $e){
            return response()->json(["error" => $e->getMessage()],400);
        }
        return response()->json(['message'=>'Got process template with id','process'=> $process],200);
    }

    public function removeProcessCompany(Request $request){
        $idProcess = $request->idProcess;
        $token = $request->token;
        if(!$idProcess){
            return response()->json(['error' => true, 'message' => "idProcess is required"]);
        }
        if(!$token){
            return response()->json(['error' => true, 'message' => "token is required"]);
        }
        try{
            $deleteProcess = Processes::find($idProcess)->delete();
            $admin = Admins::where('auth_token', $token)->first();
            $company_id = $admin->company_id;
            $processes1 = DB::table('processes')
                ->leftJoin('processes_employees', 'processes.id', '=', 'processes_employees.process_id')
                ->leftJoin('employees', 'processes_employees.employee_id', '=', 'employees.id')
                ->leftJoin('departments', 'employees.department_id', '=', 'departments.id')
                ->leftJoin('companies', 'departments.company_id', '=', 'companies.id')
                ->where('companies.id',$company_id)
                ->select('processes.id as id',
                    'processes.name as name',
                    'processes.code as code',
                    'processes.description as description',
                    'processes.type as type',
                    'processes.created_at as created_at'
                )->distinct()
                ->get();
            $processes2 = DB::table('processes')
                ->leftJoin('processes_roles', 'processes.id', '=', 'processes_roles.process_id')
                ->leftJoin('roles', 'processes_roles.role_id', '=', 'roles.id')
                ->leftJoin('departments', 'roles.department_id', '=', 'departments.id')
                ->leftJoin('companies', 'departments.company_id', '=', 'companies.id')
                ->where('companies.id',$company_id)
                ->select('processes.id as id',
                    'processes.code as code',
                    'processes.name as name',
                    'processes.description as description',
                    'processes.type as type',
                    'processes.created_at as created_at'
                )->distinct()
                ->get();
            $processes3 = DB::table('processes')
                ->leftJoin('processes_departments', 'processes.id', '=', 'processes_departments.process_id')
                ->leftJoin('departments', 'processes_departments.department_id', '=', 'departments.id')
                ->leftJoin('companies', 'departments.company_id', '=', 'companies.id')
                ->where('companies.id',$company_id)
                ->select('processes.id as id',
                    'processes.code as code',
                    'processes.name as name',
                    'processes.description as description',
                    'processes.type as type',
                    'processes.created_at as created_at'
                )->distinct()
                ->get();
            $processes4 = DB::table('processes')
                ->leftJoin('processes_companies', 'processes.id', '=', 'processes_companies.process_id')
                ->leftJoin('companies', 'processes_companies.company_id', '=', 'companies.id')
                ->where('companies.id',$company_id)
                ->select('processes.id as id',
                    'processes.code as code',
                    'processes.name as name',
                    'processes.description as description',
                    'processes.type as type',
                    'processes.created_at as created_at'
                )->distinct()
                ->get();
        }catch (\Exception $e){
            return response()->json(["error" => $e->getMessage()],400);
        }
        return response()->json(
            [
                'message'=>'Xóa quy trình thành công',
                'processes1' => $processes1,
                'processes2' => $processes2,
                'processes3' => $processes3,
                'processes4' => $processes4,
            ],200);
    }

    public function checkTokenOfCompany (Request $request){
        $token = $request->token;
        if(!isset($token)){
            return response()->json(['error' => 1, 'message' => "token is required"], 400);
        }
        try{
            $isAdminLoggedIn = false;
            $admin = Admins::where('auth_token', $token);
            if($admin){
                $isAdminLoggedIn = true;
            }
        }catch (\Exception $e){
            return response()->json(['error' => 1, 'message' => $e->getMessage()], 400);
        }
        return response()->json([
            'success' => true,
            'message' => "Kiểm tra token thành công",
            "adminLoggedIn" => $isAdminLoggedIn],
            200);
    }

    public function getAccountOfCompanyInformation(Request $request){
        $token = $request->token;
        if(!isset($token)){
            return response()->json(['error' => 1, 'message' => "token is required"], 400);
        }
        try{
            $admin = Admins::where('auth_token', $token)->first();
        }catch (\Exception $e){
            return response()->json(['error' => 1, 'message' => $e->getMessage()], 400);
        }
        return response()->json([
            'success' => true,
            'message' => "Lấy thông tin thành công",
            'username' => $admin->username
            ], 200);
    }

    public function updateAccountOfCompany(Request $request){
        $username = $request->username;
        $password = $request->password;
        $newPassword = $request->newPassword;
        $token = $request->tokenData;

        try{
            $admin = Admins::where('auth_token', $token)->first();
            if(!$admin){
                return response()->json(['error' => 1, 'message' => "xảy ra lỗi với token"], 201);
            }
            if(Hash::check($password, $admin->password)){
                $admin->username = $username;
                $admin->password = Hash::make($newPassword);
                $admin->update();
            }else{
                return response()->json(['error' => 1, 'message' => "Không đúng mật khẩu", "password" => true],  201);
            }
        }catch (\Exception $e){
            return response()->json(['error' => 1, 'message' => $e->getMessage()], 201);
        }
        return response()->json(['success'=>true, 'message' => 'Cập nhật tài khoản thành công', 'username' => $username]);
    }

    public function resetPasswordForCompany(Request $request){
        $emailData = $request->email;
        if(!$emailData){
            return response()->json(['error' => 1, 'message' => "Email is required"], 400);
        }
        try{
            $company = Companies::where('contact', $emailData)->first();
            if(!$company){
                return response()->json(['error' => 1, 'message' => "Email này không hợp lệ"], 201);
            }
            $admin = Admins::where('company_id', $company->id)->first();
        }catch (\Exception $e){
            return response()->json(['error' => 1, 'message' => $e->getMessage()], 400);
        }

        try{
            Mail::to($emailData)->send(new ResetPasswordCompany($admin));
        }catch (\Exception $e){
            $email = new Emails();
            $email->type = "Reset Password Admin";
            $email->to = $emailData;
            $email->status = 2;
            $email->response = $e->getMessage();
            $email->save();
            return response()->json(['error' => 1, 'message' => $e->getMessage()], 201);
        }

        $email = new Emails();
        $email->type = "Reset Password Admin";
        $email->to = $emailData;
        $email->response = "success";
        $email->save();
        return response()->json(['success' => true, 'message' => "Hệ thống đã gửi email cho email của bạn"], 200);
    }

    public function handleResetPasswordForCompany(Request $request){
        $id = $request->id;
        $newPassword = $request->newPassword;
        if(!$id){
            return response()->json(['error' => 1, 'message' => "id is required"], 400);
        }
        if(!$newPassword){
            return response()->json(['error' => 1, 'message' => "newPassword is required"], 400);
        }
        try{
            $admin = Admins::find($id);
            if(!$admin){
                return response()->json(['error' => 1, 'message' => "xảy ra lỗi với id"], 201);
            }
            $admin->password = Hash::make($newPassword);
            $admin->update();
        }catch (\Exception $e){
            return response()->json(['error' => 1, 'message' => $e->getMessage()], 201);
        }
        return response()->json(['success' => true, 'message' => "cập nhật mật khẩu thành công"], 200);
    }

    public function searchProcessesTemplateInCompany(Request $request){
        $search = $request->search;
        try{
            if(!$search){
                $processes = ProcessesFields::all();
            }else{
                $processes = ProcessesFields::where('name', 'LIKE', '%' . $search . '%')->get();
            }
            $data = [];
            foreach ($processes as $process){
                $process->field;
                $data []= $process;
            }
        }catch (\Exception $e){
            return response()->json(['error' => 1, 'message' => $e->getMessage()], 400);
        }
        return response()->json([
            'success' => true,
            'message' => "Tìm kiếm thành công",
            "processes" => $data],
            200);
    }

    public function searchProcessesTemplateOfFieldInCompany(Request $request){
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
            $data = [];
            foreach ($processes as $process){
                $process->field;
                $data []= $process;
            }
        }catch (\Exception $e){
            return response()->json(['error' => 1, 'message' => $e->getMessage()], 400);
        }
        return response()->json([
            'success' => true,
            'message' => "Tìm kiếm thành công",
            "processes" => $data],
            200);
    }

    public function searchEmployeesInCompany(Request $request){
        $search = $request->search;
        $token = $request->token;
            if(!$token){
                return response()->json(['error' => true, 'message' => "token is required"]);
            }
            try {
                $admin = Admins::where('auth_token', $token)->first();
                $idCompany = $admin->company_id;
            if(!$search){
                $employees = DB::table('companies')
                    ->join('departments', 'companies.id', '=', 'departments.company_id')
                    ->join('employees', 'departments.id', '=', 'employees.department_id')
                    ->join('roles', 'employees.role_id', '=', 'roles.id')
                    ->where('companies.id', $idCompany)
                    ->select('employees.id as id_employee',
                        'employees.name as name',
                        'employees.email as email',
                        'employees.phone as phone',
                        'employees.address as address',
                        'employees.birth as birth',
                        'employees.avatar as avatar',
                        'employees.gender as gender',
                        'departments.id as id_department',
                        'departments.name as department_name',
                        'roles.name as role_name')
                    ->get();
            }else{
                $employees = DB::table('companies')
                    ->join('departments', 'companies.id', '=', 'departments.company_id')
                    ->join('employees', 'departments.id', '=', 'employees.department_id')
                    ->join('roles', 'employees.role_id', '=', 'roles.id')
                    ->where('companies.id', $idCompany)
                    ->where(function($query) use ($search) {
                        $query->where('employees.name', 'LIKE', '%' . $search . '%')
                            ->orWhere('employees.email', 'LIKE', '%' . $search . '%')
                            ->orWhere('employees.phone', 'LIKE', '%' . $search . '%');
                    })
                    ->select('employees.id as id_employee',
                        'employees.name as name',
                        'employees.email as email',
                        'employees.phone as phone',
                        'employees.address as address',
                        'employees.birth as birth',
                        'employees.avatar as avatar',
                        'employees.gender as gender',
                        'departments.id as id_department',
                        'departments.name as department_name',
                        'roles.name as role_name')
                    ->get();
            }
        }catch (\Exception $e){
            return response()->json(['error' => 1, 'message' => $e->getMessage()], 400);
        }
        return response()->json([
            'success' => true,
            'message' => "Tìm kiếm thành công",
            "employees" => $employees],
            200);
    }

    public function searchEmployeesInDepartmentInCompany(Request $request){
        $search = $request->search;
        $DepartmentId = $request->idDepartment;
        try{
            if(!$DepartmentId){
                return response()->json(['error' => 1, 'message' => "Xảy ra lỗi với lĩnh vực"], 201);
            }
            if(!$search){
                $employees = Employees::where('department_id',$DepartmentId)->get();
            }else{
                $employees = Employees::where('department_id', $DepartmentId)
                    ->where('name', 'LIKE', '%' . $search . '%')->get();
            }
            $data = [];
            foreach ($employees as $employee){
                $employee->role;
                $employee->department;
                $employee->department_name = $employee->department->name;
                $employee->role_name = $employee->role->name;
                $employee->id_employee = $employee->id;
                $data []= $employee;
            }
        }catch (\Exception $e){
            return response()->json(['error' => 1, 'message' => $e->getMessage()], 400);
        }
        return response()->json([
            'success' => true,
            'message' => "Tìm kiếm thành công",
            "employees" => $data],
            200);

    }

    public function searchProcessesOfEmployeeInCompany(Request $request){
        $search = $request->search;
        $employeeId = $request->idEmployee;
        try{
            if(!$employeeId){
                return response()->json(['error' => 1, 'message' => "Xảy ra lỗi với Id của nhân viên"], 201);
            }
            $processes1 = DB::table('processes')
                ->leftJoin('processes_roles', 'processes.id', '=', 'processes_roles.process_id')
                ->leftJoin('roles', 'processes_roles.role_id', '=', 'roles.id')
                ->leftJoin('employees', 'roles.id', '=', 'employees.role_id')
                ->where('employees.id',$employeeId)
                ->where(function($query) use ($search) {
                    $query->where('processes.name', 'LIKE', '%' . $search . '%')
                        ->orWhere('processes.code', 'LIKE', '%' . $search . '%');
                })
                ->select('processes.id as id',
                    'processes.code as code',
                    'processes.name as name',
                    'processes.description as description',
                    'processes.type as type',
                    'processes.created_at as created_at'
                )->distinct()
                ->get();

            $processes2 = DB::table('processes')
                ->leftJoin('processes_employees', 'processes.id', '=', 'processes_employees.process_id')
                ->leftJoin('employees', 'processes_employees.employee_id', '=', 'employees.id')
                ->where('employees.id',$employeeId)
                ->where(function($query) use ($search) {
                    $query->where('processes.name', 'LIKE', '%' . $search . '%')
                        ->orWhere('processes.code', 'LIKE', '%' . $search . '%');
                })
                ->select('processes.id as id',
                    'processes.code as code',
                    'processes.name as name',
                    'processes.description as description',
                    'processes.type as type',
                    'processes.created_at as created_at'
                )->distinct()
                ->get();

        }catch (\Exception $e){
            return response()->json(['error' => 1, 'message' => $e->getMessage()], 400);
        }

        return response()->json([
            'success' => true,
            'message' => "Tìm kiếm thành công",
            "processes1" => $processes1,
            "processes2" => $processes2],
            200);

    }

    public function searchProcessesInCompany(Request $request){
        $search = $request->search;
        $token = $request->token;
        if(!$token){
            return response()->json(['error' => true, 'message' => "Xảy ra lỗi với token"],201);
        }
        try {
            $admin = Admins::where('auth_token', $token)->first();
            $idCompany = $admin->company_id;
            if(!$search){
                $processes1 = DB::table('processes')
                    ->leftJoin('processes_employees', 'processes.id', '=', 'processes_employees.process_id')
                    ->leftJoin('employees', 'processes_employees.employee_id', '=', 'employees.id')
                    ->leftJoin('departments', 'employees.department_id', '=', 'departments.id')
                    ->leftJoin('companies', 'departments.company_id', '=', 'companies.id')
                    ->where('companies.id',$idCompany)
                    ->select('processes.id as id',
                        'processes.name as name',
                        'processes.code as code',
                        'processes.description as description',
                        'processes.type as type',
                        'processes.created_at as created_at'
                    )->distinct()
                    ->get();
                $processes2 = DB::table('processes')
                    ->leftJoin('processes_roles', 'processes.id', '=', 'processes_roles.process_id')
                    ->leftJoin('roles', 'processes_roles.role_id', '=', 'roles.id')
                    ->leftJoin('departments', 'roles.department_id', '=', 'departments.id')
                    ->leftJoin('companies', 'departments.company_id', '=', 'companies.id')
                    ->where('companies.id',$idCompany)
                    ->select('processes.id as id',
                        'processes.code as code',
                        'processes.name as name',
                        'processes.description as description',
                        'processes.type as type',
                        'processes.created_at as created_at'
                    )->distinct()
                    ->get();
            }else{
                $processes1 = DB::table('processes')
                    ->leftJoin('processes_employees', 'processes.id', '=', 'processes_employees.process_id')
                    ->leftJoin('employees', 'processes_employees.employee_id', '=', 'employees.id')
                    ->leftJoin('departments', 'employees.department_id', '=', 'departments.id')
                    ->leftJoin('companies', 'departments.company_id', '=', 'companies.id')
                    ->where('companies.id',$idCompany)
                    ->where(function($query) use ($search) {
                        $query->where('processes.name', 'LIKE', '%' . $search . '%')
                            ->orWhere('processes.code', 'LIKE', '%' . $search . '%');
                    })
                    ->select('processes.id as id',
                        'processes.name as name',
                        'processes.code as code',
                        'processes.description as description',
                        'processes.type as type',
                        'processes.created_at as created_at'
                    )->distinct()
                    ->get();
                $processes2 = DB::table('processes')
                    ->leftJoin('processes_roles', 'processes.id', '=', 'processes_roles.process_id')
                    ->leftJoin('roles', 'processes_roles.role_id', '=', 'roles.id')
                    ->leftJoin('departments', 'roles.department_id', '=', 'departments.id')
                    ->leftJoin('companies', 'departments.company_id', '=', 'companies.id')
                    ->where('companies.id',$idCompany)
                    ->where(function($query) use ($search) {
                        $query->where('processes.name', 'LIKE', '%' . $search . '%')
                            ->orWhere('processes.code', 'LIKE', '%' . $search . '%');
                    })
                    ->select('processes.id as id',
                        'processes.code as code',
                        'processes.name as name',
                        'processes.description as description',
                        'processes.type as type',
                        'processes.created_at as created_at'
                    )->distinct()
                    ->get();
            }
        }catch (\Exception $e){
            return response()->json(['error' => 1, 'message' => $e->getMessage()], 400);
        }
        return response()->json([
            'success' => true,
            'message' => "Tìm kiếm thành công",
            "processes1" => $processes1,
            "processes2" => $processes2],
            200);
    }
}
