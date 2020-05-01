<?php

namespace App\Http\Controllers\Api;

use App\Admins;
use App\ElementComments;
use App\ElementNotes;
use App\Elements;
use App\Processes;
use App\ProcessesEmployees;
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

    public function getAllEmployeeDepartment(Request $request){
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
                    'employees.phone as phone',
                    'employees.address as address',
                    'roles.name as role',
                    'roles.id as id_role',
                    'departments.id as id_department',
                    'departments.name as department_name')
                ->get();
        }catch (\Exception $e){
            return response()->json(['error'=>true, 'message'=> $e->getMessage()], 400);
        }
        return response()->json(['success'=>true, 'message'=> "got employees from department", "employees" => $employees]);
    }

    public function newProcessCompany(Request $request){
        $tọken = $request->token;
        $information = $request->information;
        $xml = $request->xml;
        $elements = $request->elements;

        try{
            $admin = Admins::where('auth_token',$tọken)->first();
            if(!$admin){
                return response()->json(['error' => true, 'message' => "Something was wrong with the token"]);
            }
            //create process
            $process = new Processes();
            $process->name = $information['name'];
            $process->description = $information['description'];
            $process->update_at = $information['time'];
            $process->xml = $xml;
            $process->admin_id = $admin->id;
            $process->save();
            //save processes_employees
            $assign = $information['assign'];
            foreach ($assign as $value){
                $link = new ProcessesEmployees();
                $link->process_id = $process->id;
                $link->employee_id = $value['value'];
                $link->save();
            }
            //save elements
            foreach ($elements as $value){
                if($value['note'] || $value['comments']){
                    $element = new Elements();
                    $element->element = $value['id'];
                    $element->type = $value['type'];
                    $element->process_id = $process->id;
                    $element->save();
                    //save note element
                    if($value['note']){
                        $note = new ElementNotes();
                        $note->element_id = $element->id;
                        $note->admin_id = $admin->id;
                        $note->content = $value['note'];
                        $note->save();
                    }
                    //save comment
                    if($value['comments']){
                        foreach ($value['comments'] as $index){
                            $comment =  new ElementComments();
                            $comment->element_id = $element->id;
                            $comment->admin_id = $index['admin_id'];
                            $comment->comment = $index['content'];
                            $comment->update_at = $index['time'];
                            $comment->save();
                        }
                    }
                }
            }
            //save iso

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
        $tọken = $request->token;
        $information = $request->information;
        $xml = $request->xml;
        $elements = $request->elements;

        try{
            $admin = Admins::where('auth_token',$tọken)->first();
            if(!$admin){
                return response()->json(['error' => true, 'message' => "Something was wrong with the token"]);
            }
            $processId = $information['id'];
            if(!$processId){
                return response()->json(['error' => true, 'message' => "Something was wrong with process id"]);
            }
            $process = Processes::find($processId);
            if(!$process){
                return response()->json(['error' => true, 'message' => "Something was wrong with process"]);
            }
            //update process
            $process->name = $information['name'];
            $process->description = $information['description'];
            $process->update_at = $information['time'];
            $process->xml = $xml;
            $process->admin_id = $admin->id;
            $process->save();
            //remove assign employee
            $deleteAssigns = ProcessesEmployees::where('process_id', $processId)->delete();
            //remove old elements
            $deletedElements = Elements::where('process_id', $processId)->delete();
            //remove iso
            //update processes_employees
            $assign = $information['assign'];
            foreach ($assign as $value){
                $link = new ProcessesEmployees();
                $link->process_id = $process->id;
                $link->employee_id = $value['value'];
                $link->save();
            }
            //update elements
            foreach ($elements as $value){
                if($value['note'] || $value['comments']){
                    $element = new Elements();
                    $element->element = $value['id'];
                    $element->type = $value['type'];
                    $element->process_id = $process->id;
                    $element->save();
                    //update note element
                    if($value['note']){
                        $note = new ElementNotes();
                        $note->element_id = $element->id;
                        $note->admin_id = $admin->id;
                        $note->content = $value['note'];
                        $note->save();
                    }
                    //update comment
                    if($value['comments']){
                        foreach ($value['comments'] as $index){
                            $comment =  new ElementComments();
                            $comment->element_id = $element->id;
                            $comment->admin_id = $index['admin_id'];
                            $comment->comment = $index['content'];
                            $comment->update_at = $index['time'];
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

}
