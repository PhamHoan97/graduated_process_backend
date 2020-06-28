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
use App\ProcessesTemplates;
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
        $templates = json_decode($request->templates);

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
            if($information->type === 5){
                $process->collabration =  $information->collabration;
            }
            if($request->file){
                $url = $request->file;
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
            } else if($information->type === 5){
                $assign = $information->assign;
                $employeesAssign = $assign->employees;
                foreach ($employeesAssign as $value){
                    $link = new ProcessesEmployees();
                    $link->process_id = $process->id;
                    $link->employee_id = $value->value;
                    $link->save();
                }
                if(isset($assign->roles)){
                    $rolesAssign = $assign->roles;
                    foreach ($rolesAssign as $value){
                        $link = new ProcessesRoles();
                        $link->process_id = $process->id;
                        $link->role_id = $value->value;
                        $link->save();
                    }
                }else{
                    $departmentsAssign = $assign->departments;
                    foreach ($departmentsAssign as $value){
                        $link = new ProcessesDepartments();
                        $link->process_id = $process->id;
                        $link->department_id = $value->value;
                        $link->save();
                    }
                }
            }
            //save elements
            foreach ($elements as $value){
                if($value->note || $value->comments){
                    $element = new Elements();
                    $element->element = $value->id;
                    $element->type = $value->type;
                    $element->name = $value->name;
                    $element->process_id = $process->id;
                    $element->save();
                    //save note element
                    if($value->note){
                        $note = new ElementNotes();
                        $note->element_id = $element->id;
                        $note->admin_id = $admin->id;
                        $note->content = $value->note;
                        if($value->file){
                            $note->document = json_encode($value->file, JSON_UNESCAPED_UNICODE);
                        }else{
                            $note->document = null;
                        }
                        if($value->assign){
                            $note->assign = json_encode($value->assign, JSON_UNESCAPED_UNICODE);
                        }else{
                            $note->assign = null;
                        }

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
            //save templates for process
            if($templates){
                foreach ($templates as $value) {
                    $template = new ProcessesTemplates();
                    $template->name = $value->name;
                    $template->link = $value->link;
                    $template->process_id = $process->id;
                    $template->save();
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
            return response()->json(['error' => 1, 'message' => "idProcess is required"], 201);
        }else{
            try{
                $process = Processes::find($idProcess);
                $employees = $process->employees;
                $process->elementNotes;
                $comments = $process->elementComments;
                $process->elements;
                $roles = $process->roles;
                $process->departments;
                $process->templates;
                $newEmployees = [];
                $newRoles = [];

                foreach ($employees as $item){
                    $employee = DB::table('employees')
                        ->join('departments', 'employees.department_id', '=', 'departments.id')
                        ->join('roles', 'employees.role_id', '=', 'roles.id')
                        ->where('employees.id', $item->id)
                        ->select('employees.id as id_employee',
                            'employees.name as name',
                            'departments.id as id_department',
                            'departments.name as department_name',
                            'roles.name as role_name')
                        ->first();
                    $newEmployees[] = ["id" => $employee->id_employee, "name" => $employee->name.' ('.$employee->department_name.'-'.$employee->role_name.')'];
                }

                foreach ($roles as $item){
                    $role = DB::table('roles')
                        ->join('departments', 'roles.department_id', '=', 'departments.id')
                        ->where('roles.id', $item->id)
                        ->select('roles.id as id_role',
                            'departments.id as id_department',
                            'departments.name as department_name',
                            'roles.name as role_name')
                        ->first();
                    $newRoles[] = ["id" => $role->id_role, "name" => $role->role_name.' ('.$role->department_name.')'];
                }

                $newComments = [];
                foreach ($comments as $comment){
                    if($comment->employee_id){
                        $employee = Employees::find($comment->employee_id);
                        $comment->employee_name = $employee->name;
                    }
                    $newComments[] = $comment;
                }
                $process->element_comments = $newComments;
                $process->employeesDetail = $newEmployees;
                $process->rolesDetail = $newRoles;
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
        $templates =  json_decode($request->templates);
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
            if($information->type === 5){
                $process->collabration =  $information->collabration;
            }
            if($request->file){
                $url = $request->file;
                $process->document = $url;
            }
            $process->update();
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
             //remove old templates
            $deletedTemplates = ProcessesTemplates::where('process_id', $processId)->delete();
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
            }else if($information->type === 5){
                $assign = $information->assign;
                $employeesAssign = $assign->employees;
                foreach ($employeesAssign as $value){
                    $link = new ProcessesEmployees();
                    $link->process_id = $process->id;
                    $link->employee_id = $value->value;
                    $link->save();
                }
                if(isset($assign->roles)){
                    $rolesAssign = $assign->roles;
                    foreach ($rolesAssign as $value){
                        $link = new ProcessesRoles();
                        $link->process_id = $process->id;
                        $link->role_id = $value->value;
                        $link->save();
                    }
                }else{
                    $departmentsAssign = $assign->departments;
                    foreach ($departmentsAssign as $value){
                        $link = new ProcessesDepartments();
                        $link->process_id = $process->id;
                        $link->department_id = $value->value;
                        $link->save();
                    }
                }
            }
            //update elements
            foreach ($elements as $value){
                if($value->note || $value->comments){
                    $element = new Elements();
                    $element->element = $value->id;
                    $element->type = $value->type;
                    $element->name = $value->name;
                    $element->process_id = $process->id;
                    $element->save();
                    //update note element
                    if($value->note){
                        $note = new ElementNotes();
                        $note->element_id = $element->id;
                        $note->admin_id = $admin->id;
                        $note->content = $value->note;
                        $note->document = $value->file;
                        if($value->file){
                            $note->document = json_encode($value->file, JSON_UNESCAPED_UNICODE);
                        }else{
                            $note->document = null;
                        }
                        if($value->assign){
                            $note->assign = json_encode($value->assign, JSON_UNESCAPED_UNICODE);
                        }else{
                            $note->assign = null;
                        }
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
            // update Templates
            if($templates){
                foreach ($templates as $value){
                   $template = new ProcessesTemplates();
                   $template->name = $value->name;
                   $template->link = $value->link;
                   $template->process_id = $process->id;
                   $template->save();
                }
            }
        }catch (\Exception $e){
            return response()->json(['error' => true, 'message' =>$e->getMessage()]);
        }
        return response()->json(['success' => true, 'message' => "Sửa quy trình thành công", "process" => $process]);
    }

    public function getAllEmployeeRoleAndDepartmentOfCompany(Request $request){
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
                ->where('processes.is_delete', 1)
                ->select('processes.id as id',
                    'processes.name as name',
                    'processes.code as code',
                    'processes.description as description',
                    'processes.type as type',
                    'processes.created_at as created_at'
                )->distinct()
                ->get();
            $processesDuplicate = DB::table('processes')
                ->leftJoin('processes_employees', 'processes.id', '=', 'processes_employees.process_id')
                ->leftJoin('employees', 'processes_employees.employee_id', '=', 'employees.id')
                ->leftJoin('departments', 'employees.department_id', '=', 'departments.id')
                ->leftJoin('companies', 'departments.company_id', '=', 'companies.id')
                ->where('companies.id',$company_id)
                ->where('processes.is_delete', 1)
                ->where('processes.type',5)
                ->select('processes.id as id')->distinct()
                ->get();
            $idDuplicate = [];
            foreach ($processesDuplicate as $item){
                $idDuplicate []= $item->id;
            }
            $processes2 = DB::table('processes')
                ->leftJoin('processes_roles', 'processes.id', '=', 'processes_roles.process_id')
                ->leftJoin('roles', 'processes_roles.role_id', '=', 'roles.id')
                ->leftJoin('departments', 'roles.department_id', '=', 'departments.id')
                ->leftJoin('companies', 'departments.company_id', '=', 'companies.id')
                ->where('companies.id', $company_id)
                ->where('processes.is_delete', 1)
                ->whereNotIn('processes.id', $idDuplicate)
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
                ->where('processes.is_delete', 1)
                ->whereNotIn('processes.id', $idDuplicate)
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
                ->where('processes.is_delete', 1)
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
            $processes2 = DB::table('processes')
                ->leftJoin('processes_employees', 'processes.id', '=', 'processes_employees.process_id')
                ->leftJoin('employees', 'processes_employees.employee_id', '=', 'employees.id')
                ->leftJoin('departments', 'employees.department_id', '=', 'departments.id')
                ->where('departments.id',$idDepartment)
                ->where('processes.is_delete', 1)
                ->select('processes.id as id',
                    'processes.code as code',
                    'processes.name as name',
                    'processes.description as description',
                    'processes.type as type',
                    'processes.created_at as created_at'
                )->distinct()
                ->get();
            $processesDuplicate = DB::table('processes')
                ->leftJoin('processes_employees', 'processes.id', '=', 'processes_employees.process_id')
                ->leftJoin('employees', 'processes_employees.employee_id', '=', 'employees.id')
                ->leftJoin('departments', 'employees.department_id', '=', 'departments.id')
                ->where('departments.id',$idDepartment)
                ->where('processes.is_delete', 1)
                ->where('processes.type',5)
                ->select('processes.id as id')->distinct()
                ->get();
            $idDuplicate = [];
            foreach ($processesDuplicate as $item){
                $idDuplicate []= $item->id;
            }
            $processes1 = DB::table('processes')
                ->leftJoin('processes_roles', 'processes.id', '=', 'processes_roles.process_id')
                ->leftJoin('roles', 'processes_roles.role_id', '=', 'roles.id')
                ->leftJoin('departments', 'roles.department_id', '=', 'departments.id')
                ->where('departments.id',$idDepartment)
                ->where('processes.is_delete', 1)
                ->whereNotIn('processes.id',$idDuplicate)
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
                ->where('departments.id',$idDepartment)
                ->where('processes.is_delete', 1)
                ->whereNotIn('processes.id',$idDuplicate)
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
                ->where('departments.id',$idDepartment)
                ->where('processes.is_delete', 1)
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
            ],200);
    }

    public function getAllProcessesOfAEmployeeOfCompany(Request $request){
        $idEmployee = $request->idEmployee ;
        if(!$idEmployee){
            return response()->json(['error' => true, 'message' => "idEmployee is required"]);
        }
        try{
            $employee = Employees::find($idEmployee);
            $processes2 = DB::table('processes')
                ->leftJoin('processes_employees', 'processes.id', '=', 'processes_employees.process_id')
                ->leftJoin('employees', 'processes_employees.employee_id', '=', 'employees.id')
                ->where('employees.id',$idEmployee)
                ->where('processes.is_delete', 1)
                ->select('processes.id as id',
                    'processes.code as code',
                    'processes.name as name',
                    'processes.description as description',
                    'processes.type as type',
                    'processes.created_at as created_at'
                )->distinct()
                ->get();
            $processesDuplicate = DB::table('processes')
                ->leftJoin('processes_employees', 'processes.id', '=', 'processes_employees.process_id')
                ->leftJoin('employees', 'processes_employees.employee_id', '=', 'employees.id')
                ->where('employees.id',$idEmployee)
                ->where('processes.is_delete', 1)
                ->where('processes.id',5)
                ->select('processes.id as id')->distinct()
                ->get();
            $idDuplicate = [];
            foreach ($processesDuplicate as $item){
                $idDuplicate []= $item->id;
            }
            $processes1 = DB::table('processes')
                ->leftJoin('processes_roles', 'processes.id', '=', 'processes_roles.process_id')
                ->leftJoin('roles', 'processes_roles.role_id', '=', 'roles.id')
                ->leftJoin('employees', 'roles.id', '=', 'employees.role_id')
                ->where('employees.id',$idEmployee)
                ->where('processes.is_delete', 1)
                ->whereNotIn('processes.id',$idDuplicate)
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
                ->where('processes.is_delete', 1)
                ->whereNotIn('processes.id',$idDuplicate)
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
                ->where('processes.is_delete', 1)
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
            return response()->json(['error' => true, 'message' => "idProcess không được trống"]);
        }
        if(!$token){
            return response()->json(['error' => true, 'message' => "token không được trống"]);
        }
        try{
            $deleteProcess = Processes::find($idProcess);
            $deleteProcess->is_delete = 2;
            $deleteProcess->update();
            $admin = Admins::where('auth_token', $token)->first();
            $company_id = $admin->company_id;
            $processes1 = DB::table('processes')
                ->leftJoin('processes_employees', 'processes.id', '=', 'processes_employees.process_id')
                ->leftJoin('employees', 'processes_employees.employee_id', '=', 'employees.id')
                ->leftJoin('departments', 'employees.department_id', '=', 'departments.id')
                ->leftJoin('companies', 'departments.company_id', '=', 'companies.id')
                ->where('companies.id',$company_id)
                ->where('processes.is_delete', 1)
                ->select('processes.id as id',
                    'processes.name as name',
                    'processes.code as code',
                    'processes.description as description',
                    'processes.type as type',
                    'processes.created_at as created_at'
                )->distinct()
                ->get();
            $processesDuplicate = DB::table('processes')
                ->leftJoin('processes_employees', 'processes.id', '=', 'processes_employees.process_id')
                ->leftJoin('employees', 'processes_employees.employee_id', '=', 'employees.id')
                ->leftJoin('departments', 'employees.department_id', '=', 'departments.id')
                ->leftJoin('companies', 'departments.company_id', '=', 'companies.id')
                ->where('companies.id',$company_id)
                ->where('processes.is_delete', 1)
                ->where('processes.type',5)
                ->select('processes.id as id')->distinct()
                ->get();
            $idDuplicate = [];
            foreach ($processesDuplicate as $item){
                $idDuplicate []= $item->id;
            }
            $processes2 = DB::table('processes')
                ->leftJoin('processes_roles', 'processes.id', '=', 'processes_roles.process_id')
                ->leftJoin('roles', 'processes_roles.role_id', '=', 'roles.id')
                ->leftJoin('departments', 'roles.department_id', '=', 'departments.id')
                ->leftJoin('companies', 'departments.company_id', '=', 'companies.id')
                ->where('companies.id',$company_id)
                ->where('processes.is_delete', 1)
                ->whereNotIn('processes.id',$idDuplicate)
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
                ->where('processes.is_delete', 1)
                ->whereNotIn('processes.id',$idDuplicate)
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
                ->where('processes.is_delete', 1)
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
            $processes2 = DB::table('processes')
                ->leftJoin('processes_employees', 'processes.id', '=', 'processes_employees.process_id')
                ->leftJoin('employees', 'processes_employees.employee_id', '=', 'employees.id')
                ->where('employees.id',$employeeId)
                ->where('processes.is_delete', 1)
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
            $processesDuplicate = DB::table('processes')
                ->leftJoin('processes_employees', 'processes.id', '=', 'processes_employees.process_id')
                ->leftJoin('employees', 'processes_employees.employee_id', '=', 'employees.id')
                ->where('employees.id',$employeeId)
                ->where('processes.is_delete', 1)
                ->where(function($query) use ($search) {
                    $query->where('processes.name', 'LIKE', '%' . $search . '%')
                        ->orWhere('processes.code', 'LIKE', '%' . $search . '%');
                })
                ->where('processes.type',5)
                ->select('processes.id as id')->distinct()
                ->get();

            $idDuplicate = [];
            foreach ($processesDuplicate as $item){
                $idDuplicate []= $item->id;
            }
            $processes1 = DB::table('processes')
                ->leftJoin('processes_roles', 'processes.id', '=', 'processes_roles.process_id')
                ->leftJoin('roles', 'processes_roles.role_id', '=', 'roles.id')
                ->leftJoin('employees', 'roles.id', '=', 'employees.role_id')
                ->where('employees.id',$employeeId)
                ->where('processes.is_delete', 1)
                ->where(function($query) use ($search) {
                    $query->where('processes.name', 'LIKE', '%' . $search . '%')
                        ->orWhere('processes.code', 'LIKE', '%' . $search . '%');
                })
                ->whereNotIn('processes.id',$idDuplicate)
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
                ->where('employees.id',$employeeId)
                ->where('processes.is_delete', 1)
                ->where(function($query) use ($search) {
                    $query->where('processes.name', 'LIKE', '%' . $search . '%')
                        ->orWhere('processes.code', 'LIKE', '%' . $search . '%');
                })
                ->whereNotIn('processes.id',$idDuplicate)
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
                ->where('employees.id',$employeeId)
                ->where('processes.is_delete', 1)
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
            "processes2" => $processes2,
            "processes3" => $processes3,
            "processes4" => $processes4
        ],
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
            if($search === "all"){
                $processes1 = DB::table('processes')
                    ->leftJoin('processes_employees', 'processes.id', '=', 'processes_employees.process_id')
                    ->leftJoin('employees', 'processes_employees.employee_id', '=', 'employees.id')
                    ->leftJoin('departments', 'employees.department_id', '=', 'departments.id')
                    ->leftJoin('companies', 'departments.company_id', '=', 'companies.id')
                    ->where('companies.id',$idCompany)
                    ->where('processes.is_delete', 1)
                    ->select('processes.id as id',
                        'processes.name as name',
                        'processes.code as code',
                        'processes.description as description',
                        'processes.type as type',
                        'processes.created_at as created_at'
                    )->distinct()
                    ->get();
                $processesDuplicate = DB::table('processes')
                    ->leftJoin('processes_employees', 'processes.id', '=', 'processes_employees.process_id')
                    ->leftJoin('employees', 'processes_employees.employee_id', '=', 'employees.id')
                    ->leftJoin('departments', 'employees.department_id', '=', 'departments.id')
                    ->leftJoin('companies', 'departments.company_id', '=', 'companies.id')
                    ->where('companies.id',$idCompany)
                    ->where('processes.is_delete', 1)
                    ->where('processes.type',5)
                    ->select('processes.id as id')->distinct()
                    ->get();
                $idDuplicate = [];
                foreach ($processesDuplicate as $item){
                    $idDuplicate []= $item->id;
                }
                $processes2 = DB::table('processes')
                    ->leftJoin('processes_roles', 'processes.id', '=', 'processes_roles.process_id')
                    ->leftJoin('roles', 'processes_roles.role_id', '=', 'roles.id')
                    ->leftJoin('departments', 'roles.department_id', '=', 'departments.id')
                    ->leftJoin('companies', 'departments.company_id', '=', 'companies.id')
                    ->where('companies.id',$idCompany)
                    ->where('processes.is_delete', 1)
                    ->whereNotIn('processes.id',$idDuplicate)
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
                    ->where('companies.id',$idCompany)
                    ->where('processes.is_delete', 1)
                    ->whereNotIn('processes.id',$idDuplicate)
                    ->select('processes.id as id',
                        'processes.name as name',
                        'processes.code as code',
                        'processes.description as description',
                        'processes.type as type',
                        'processes.created_at as created_at'
                    )->distinct()
                    ->get();
                $processes4 = DB::table('processes')
                    ->leftJoin('processes_companies', 'processes.id', '=', 'processes_companies.process_id')
                    ->leftJoin('companies', 'processes_companies.company_id', '=', 'companies.id')
                    ->where('companies.id',$idCompany)
                    ->where('processes.is_delete', 1)
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
                    ->where('processes.is_delete', 1)
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
                $processesDuplicate = DB::table('processes')
                    ->leftJoin('processes_employees', 'processes.id', '=', 'processes_employees.process_id')
                    ->leftJoin('employees', 'processes_employees.employee_id', '=', 'employees.id')
                    ->leftJoin('departments', 'employees.department_id', '=', 'departments.id')
                    ->leftJoin('companies', 'departments.company_id', '=', 'companies.id')
                    ->where('companies.id',$idCompany)
                    ->where('processes.is_delete', 1)
                    ->where(function($query) use ($search) {
                        $query->where('processes.name', 'LIKE', '%' . $search . '%')
                            ->orWhere('processes.code', 'LIKE', '%' . $search . '%');
                    })
                    ->where('processes.type', 5)
                    ->select('processes.id as id',
                        'processes.name as name',
                        'processes.code as code',
                        'processes.description as description',
                        'processes.type as type',
                        'processes.created_at as created_at'
                    )->distinct()
                    ->get();
                $idDuplicate = [];
                foreach ($processesDuplicate as $item){
                    $idDuplicate []= $item->id;
                }
                $processes2 = DB::table('processes')
                    ->leftJoin('processes_roles', 'processes.id', '=', 'processes_roles.process_id')
                    ->leftJoin('roles', 'processes_roles.role_id', '=', 'roles.id')
                    ->leftJoin('departments', 'roles.department_id', '=', 'departments.id')
                    ->leftJoin('companies', 'departments.company_id', '=', 'companies.id')
                    ->where('companies.id',$idCompany)
                    ->where('processes.is_delete', 1)
                    ->whereNotIn('processes.id',$idDuplicate)
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
                $processes3 = DB::table('processes')
                    ->leftJoin('processes_departments', 'processes.id', '=', 'processes_departments.process_id')
                    ->leftJoin('departments', 'processes_departments.department_id', '=', 'departments.id')
                    ->leftJoin('companies', 'departments.company_id', '=', 'companies.id')
                    ->where('companies.id',$idCompany)
                    ->where('processes.is_delete', 1)
                    ->where(function($query) use ($search) {
                        $query->where('processes.name', 'LIKE', '%' . $search . '%')
                            ->orWhere('processes.code', 'LIKE', '%' . $search . '%');
                    })
                    ->whereNotIn('processes.id',$idDuplicate)
                    ->select('processes.id as id',
                        'processes.name as name',
                        'processes.code as code',
                        'processes.description as description',
                        'processes.type as type',
                        'processes.created_at as created_at'
                    )->distinct()
                    ->get();
                $processes4 = DB::table('processes')
                    ->leftJoin('processes_companies', 'processes.id', '=', 'processes_companies.process_id')
                    ->leftJoin('companies', 'processes_companies.company_id', '=', 'companies.id')
                    ->where('companies.id',$idCompany)
                    ->where('processes.is_delete', 1)
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
            "processes2" => $processes2,
            "processes3" => $processes3,
            "processes4" => $processes4
        ],
            200);
    }

    public function getAllEmployeesAssignedInProcess(Request $request){
        $token = $request->token;
        $type = $request->type;
        $assign = $request->assign;
        if(!$token){
            return response()->json(['error' => true, 'message' => "Xảy ra lỗi với token"],201);
        }
        if(!$type || $type < 2 || $type >5){
            return response()->json(['error' => true, 'message' => "Xảy ra lỗi với type"],201);
        }
        try{
            $admin = Admins::where('auth_token', $token)->first();
            $idCompany = $admin->company_id;
            if(!$idCompany){
                return response()->json(['error' => true, 'message' => "Xảy ra lỗi với idCompany"],201);
            }
            $idRole = [];
            $idDepartment = [];
            switch ($type) {
                case 2:
                    foreach ($assign as $item){
                        $idRole[] = $item['value'];
                    }
                    $employees = DB::table('companies')
                        ->join('departments', 'companies.id', '=', 'departments.company_id')
                        ->join('employees', 'departments.id', '=', 'employees.department_id')
                        ->join('roles', 'employees.role_id', '=', 'roles.id')
                        ->whereIn('roles.id', $idRole)
                        ->select('employees.id as id',
                            'employees.name as name',
                            'departments.name as department_name',
                            'roles.name as role_name'
                        )
                        ->get();
                    break;
                case 3:
                    foreach ($assign as $item){
                        $idDepartment[] = $item['value'];
                    }
                    $employees = DB::table('companies')
                        ->join('departments', 'companies.id', '=', 'departments.company_id')
                        ->join('employees', 'departments.id', '=', 'employees.department_id')
                        ->join('roles', 'employees.role_id', '=', 'roles.id')
                        ->whereIn('departments.id', $idDepartment)
                        ->select('employees.id as id',
                            'employees.name as name',
                            'departments.name as department_name',
                            'roles.name as role_name'
                        )
                        ->get();
                    break;
                case 4:
                    $employees = DB::table('companies')
                        ->join('departments', 'companies.id', '=', 'departments.company_id')
                        ->join('employees', 'departments.id', '=', 'employees.department_id')
                        ->join('roles', 'employees.role_id', '=', 'roles.id')
                        ->where('companies.id', $idCompany)
                        ->select('employees.id as id',
                            'employees.name as name',
                            'departments.name as department_name',
                            'roles.name as role_name'
                        )
                        ->get();
                    break;
                case 5:
                    $employees = [];
                    $idEmployeesCollab = [];
                    $idRolesCollab = [];
                    $idDepartmentsCollab = [];
                    $assignEmployees = $assign['employees'];
                    foreach ($assignEmployees as $item){
                        $idEmployeesCollab[] = $item['value'];
                    }
                    $employees1 = DB::table('companies')
                        ->join('departments', 'companies.id', '=', 'departments.company_id')
                        ->join('employees', 'departments.id', '=', 'employees.department_id')
                        ->join('roles', 'employees.role_id', '=', 'roles.id')
                        ->where('companies.id', $idCompany)
                        ->whereIn('employees.id', $idEmployeesCollab)
                        ->select('employees.id as id',
                            'employees.name as name',
                            'departments.name as department_name',
                            'roles.name as role_name'
                        )
                        ->get();
                    if(isset($assign['roles'])){
                        $assignCollab = $assign['roles'];
                        foreach ($assignCollab as $item){
                            $idRolesCollab[] = $item['value'];
                        }
                        $employees2 = DB::table('companies')
                            ->join('departments', 'companies.id', '=', 'departments.company_id')
                            ->join('employees', 'departments.id', '=', 'employees.department_id')
                            ->join('roles', 'employees.role_id', '=', 'roles.id')
                            ->whereIn('roles.id', $idRolesCollab)
                            ->select('employees.id as id',
                                'employees.name as name',
                                'departments.name as department_name',
                                'roles.name as role_name'
                            )
                            ->get();
                        foreach ($employees1 as $item){
                            $employees[] = $item;
                        }
                        foreach ($employees2 as $item){
                            $employees[] = $item;
                        }
                    }else{
                        $assignCollab = $assign['departments'];
                        foreach ($assignCollab as $item){
                            $idDepartmentsCollab[] = $item['value'];
                        }
                        $employees3 = DB::table('companies')
                            ->join('departments', 'companies.id', '=', 'departments.company_id')
                            ->join('employees', 'departments.id', '=', 'employees.department_id')
                            ->join('roles', 'employees.role_id', '=', 'roles.id')
                            ->whereIn('departments.id', $idDepartmentsCollab)
                            ->select('employees.id as id',
                                'employees.name as name',
                                'departments.name as department_name',
                                'roles.name as role_name'
                            )
                            ->get();
                        foreach ($employees1 as $item){
                            $employees[] = $item;
                        }
                        foreach ($employees3 as $item){
                            $employees[] = $item;
                        }
                    }

                    break;
            }
        }catch (\Exception $e){
            return response()->json(['error' => 1, 'message' => $e->getMessage()], 400);
        }
        return response()->json([
            'success' => true,
            'message' => "Lấy dữ liệu thành công",
            "employees" => $employees,
        ],
            200);
    }

    public function getRolesAndDepartmentsWhichEmployeesAreNotBellongTo(Request $request){
        $listEmployees = $request->listEmployees;
        $token = $request->token;
        if(!$token){
            return response()->json(['error' => true, 'message' => "Xảy ra lỗi với token"],201);
        }
        try {
            $admin = Admins::where('auth_token', $token)->first();
            $idCompany = $admin->company_id;
            if(!$idCompany){
                return response()->json(['error' => true, 'message' => "Xảy ra lỗi với idCompany"],201);
            }
            if(!$listEmployees){
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
            }else{
                $idEmployees = [];
                $idRolesNotBelong = [];
                $idDepartmentsNotBelong = [];
                foreach ($listEmployees as $employee){
                    $idEmployees[]= $employee['value'];
                }
                //departments is different from employees department
                $employeeDepartments = DB::table('companies')
                    ->join('departments', 'companies.id', '=', 'departments.company_id')
                    ->join('employees', 'departments.id', '=', 'employees.department_id')
                    ->where('companies.id', $idCompany)
                    ->whereIn('employees.id', $idEmployees)
                    ->select(
                        'departments.id as id_department',
                        'departments.name as department_name')
                    ->distinct()
                    ->get();

                foreach ($employeeDepartments as $department){
                    $idDepartmentsNotBelong[]= $department->id_department;
                }

                //roles is different from employees role
                $employeeRoles = DB::table('companies')
                    ->join('departments', 'companies.id', '=', 'departments.company_id')
                    ->join('roles', 'departments.id', '=', 'roles.department_id')
                    ->join('employees', 'roles.id', '=', 'employees.role_id')
                    ->where('companies.id', $idCompany)
                    ->whereIn('employees.id', $idEmployees)
                    ->select(
                        'roles.name as role',
                        'roles.id as id_role',
                        'departments.id as id_department',
                        'departments.name as department_name')
                    ->distinct()
                    ->get();

                foreach ($employeeRoles as $role){
                    $idRolesNotBelong[]= $role->id_role;
                }

                //employees can't collabrates with their colleages (same role or department)
                $roles = DB::table('companies')
                    ->join('departments', 'companies.id', '=', 'departments.company_id')
                    ->join('roles', 'departments.id', '=', 'roles.department_id')
                    ->where('companies.id', $idCompany)
                    ->whereNotIn('departments.id', $idDepartmentsNotBelong)
                    ->whereNotIn('roles.id', $idRolesNotBelong)
                    ->select(
                        'roles.name as role',
                        'roles.id as id_role',
                        'departments.id as id_department',
                        'departments.name as department_name')
                    ->distinct()
                    ->get();

                //employees can't collabrates with their colleages (same department)
                $departments = DB::table('companies')
                    ->join('departments', 'companies.id', '=', 'departments.company_id')
                    ->where('companies.id', $idCompany)
                    ->whereNotIn('departments.id', $idDepartmentsNotBelong)
                    ->select(
                        'departments.id as id_department',
                        'departments.name as department_name')
                    ->distinct()
                    ->get();

            }
        }catch (\Exception $e){
            return response()->json(['error' => 1, 'message' => $e->getMessage()], 400);
        }
        return response()->json([
            'message'=>'Got all roles and departments in company which employees are not belong to',
            'roles' => $roles,
            'departments' => $departments,
        ],200);
    }
}
