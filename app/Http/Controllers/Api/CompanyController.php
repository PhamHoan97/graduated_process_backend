<?php

namespace App\Http\Controllers\Api;

use App\Admins;
use App\ElementComments;
use App\ElementNotes;
use App\Elements;
use App\Processes;
use App\ProcessesEmployees;
use App\ProcessesRoles;
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
        $adminUserName = Admins::where('username', $request->username)->first();
        if($adminUserName && Hash::check($request->password, $adminUserName->password)){
            $credentials = ["username" => $request->username, "password" => $request->password];
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

    public function getAllEmployeeAndRoleOfDepartment(Request $request){
        $idDepartment = $request->idDepartment;
        if(!isset($idDepartment)){
            return response()->json(['error' => 1, 'message' => "idDepartment is required"], 400);
        }
        try{
            $employees = DB::table('departments')
                ->join('employees', 'departments.id', '=', 'employees.department_id')
                ->where('departments.id', $idDepartment)
                ->select('employees.id as id_employee',
                    'employees.name as name',
                    'employees.phone as phone',
                    'employees.address as address',
                    'departments.id as id_department',
                    'departments.name as department_name')
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
                return response()->json(['error' => true, 'message' => "Something was wrong with the token"]);
            }
            //create process
            $process = new Processes();
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
            }else{
                $assign = $information->assign;
                foreach ($assign as $value){
                    $link = new ProcessesRoles();
                    $link->process_id = $process->id;
                    $link->role_id = $value->value;
                    $link->save();
                }
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
                            $comment->admin_id = $index->admin_id;
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
        return response()->json(['success' => true, 'message' => "saved process", "process" => $process]);
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
                $process->elementComments;
                $process->elements;
                $process->roles;
            }catch (\Exception $e){
                return response()->json(['error' => true, 'message' =>$e->getMessage()]);
            }
            return response()->json(
                [
                    'success' => true, 'message' => "saved process",
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
                return response()->json(['error' => true, 'message' => "Something was wrong with the token"]);
            }
            $processId = $information->id;
            if(!$processId){
                return response()->json(['error' => true, 'message' => "Something was wrong with process id"]);
            }
            $process = Processes::find($processId);
            if(!$process){
                return response()->json(['error' => true, 'message' => "Something was wrong with process"]);
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
            //remove old elements
            $deletedElements = Elements::where('process_id', $processId)->delete();
            //remove iso
            //update processes_employees
            if($information->type === 1){
                $assign = $information->assign;
                foreach ($assign as $value){
                    $link = new ProcessesEmployees();
                    $link->process_id = $process->id;
                    $link->employee_id = $value->value;
                    $link->save();
                }
            }else{
                $assign = $information->assign;
                foreach ($assign as $value){
                    $link = new ProcessesRoles();
                    $link->process_id = $process->id;
                    $link->role_id = $value->value;
                    $link->save();
                }
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
                            $comment->admin_id = $index->admin_id;
                            $comment->comment = $index->content;
                            $comment->update_at = $index->time;
                            $comment->save();
                        }
                    }
                }
            }
            //update iso

        }catch (\Exception $e){
            return response()->json(['error' => true, 'message' =>$e->getMessage()]);
        }
        return response()->json(['success' => true, 'message' => "edited process", "process" => $process]);
    }

    public function getAllCompanyAndRoleOfCompany(Request $request){
        $idCompany = $request->idCompany;
        if(!$idCompany){
            return response()->json(['error' => true, 'message' => "idCompany is required"]);
        }
        try {
            $employees = DB::table('companies')
                ->join('departments', 'companies.id', '=', 'departments.company_id')
                ->join('employees', 'departments.id', '=', 'employees.department_id')
                ->where('companies.id', $idCompany)
                ->select('employees.id as id_employee',
                    'employees.name as name',
                    'employees.email as email',
                    'employees.phone as phone',
                    'employees.address as address',
                    'departments.id as id_department',
                    'departments.name as department_name')
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
            return response()->json(['message'=>'Get all users and roles in company ','employees'=>$employees, 'roles' => $roles],200);
        }catch(\Exception $e) {
            return response()->json(["error" => $e->getMessage()],400);
        }
    }
}
