<?php

namespace App\Http\Controllers\Api\Company;

use App\UserEmails;
use App\Accounts;
use App\Employees;
use App\Admins;
use App\Mail\SendEmployeeAccount;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class AccountEmployeeController extends Controller
{
    // get idCompany when know token
    public function getIdCompanyByToken($token){
        $admin = Admins::where('auth_token',$token)->first();
        if(!$admin){
            return false;
        }
        return $admin->company_id;
    }
    public function getAllEmployee(Request $request,$token){
        $idCompany = $this->getIdCompanyByToken($token);
        if(!$idCompany){
            return response()->json(['error'=>'Error get id company with token',],400);
        }else{
            try {
                $idEmployeeAccount = DB::table('accounts')->pluck('employee_id')->toArray();
                $employees =  DB::table('companies')
                    ->join('departments', 'companies.id', '=', 'departments.company_id')
                    ->join('employees', 'departments.id', '=', 'employees.department_id')
                    ->where('companies.id',$idCompany)
                    ->whereNotIn('employees.id',$idEmployeeAccount)
                    ->select('employees.id as id',
                        'employees.email as email',
                        'employees.name as name',
                        'departments.name as department_name'
                    )
                    ->get();
                return response()->json(['message'=>'get success all employee no account ','employees'=>$employees],200);
            }catch(\Exception $e) {
                return response()->json(["error" => $e->getMessage()],400);
            }
        }
    }

    public function createAccountEmployee(Request $request){
        try {
            // validate incoming request
            $validator = Validator::make($request->all(), [
                'username' => 'unique:accounts',
            ]);
            if ($validator->fails()) {
                return response()->json(['error' => 'Username existed in system'], 404);
            }else{
                $username = $request->username;
                $password = Hash::make($request->password);
                $initialPassword = $request->password;
                $employeeId = $request->idEmployee;
                try {
                    DB::table('accounts')->insert(
                        [
                            'username' => $username,
                            'password' => $password,
                            'initial_password'=>$initialPassword,
                            'employee_id'=>$employeeId
                        ]
                    );
                    return response()->json(['message'=>'Add success new account'],200);
                }catch(\Exception $e) {
                    return response()->json(["error" => $e->getMessage()],400);
                }
            }
        }catch(\Exception $e) {
            return response()->json(["error" => $e->getMessage()],400);
        }
    }
    public function getAllInformationAccount(Request $request,$token){
        $idCompany = $this->getIdCompanyByToken($token);
        if(!$idCompany){
            return response()->json(["error" => 'Error get id company with token'],400);
        }else{
            try {
                $accounts =  DB::table('companies')
                    ->join('departments', 'companies.id', '=', 'departments.company_id')
                    ->join('employees', 'departments.id', '=', 'employees.department_id')
                    ->join('accounts', 'employees.id', '=', 'accounts.employee_id')
                    ->where('companies.id',$idCompany)
                    ->select('accounts.id as id',
                        'employees.email as email',
                        'employees.id as employee_id',
                        'accounts.username as username',
                        'accounts.initial_password as initial_password',
                        'employees.name as name',
                        'employees.gender as gender',
                        'employees.avatar as avatar'
                    )
                    ->get();
                return response()->json(['message'=>'get success all accounts in company ','accounts'=>$accounts],200);
            }catch(\Exception $e) {
                return response()->json(["error" => $e->getMessage()],400);
            }
        }
    }

    public function deleteAccountEmployee(Request $request){
        $idDeleteAccount= $request->idDeleteAccount;
        try {
            DB::table('accounts')
                ->where('id', $idDeleteAccount)
                ->delete();
                return response()->json(['message'=>'delete success account'],200);
        }catch(\Exception $e) {
            return response()->json(["error" => $e->getMessage()],400);
        }
    }

    public function sendEmailAccountEmployee(Request $request){
        $idEmployeeAccount = $request->idEmployeeAccount;
        $idAccountEmployee = $request->idAccountEmployee;
        try{
            $admin = Admins::where('auth_token',$request->tokenData)->first();
            $account = Accounts::find($idAccountEmployee);
            if(!$admin){
                return response()->json(['error' => true, 'message' => "Something was wrong with the token"]);
            }
            if(!$account){
                return response()->json(['error' => true, 'message' => "Something was wrong with the employee account"]);
            }
            $employee = Employees::find($idEmployeeAccount);
        }catch (\Exception $e){
            return response()->json(['error' => true, 'message' => "Something was wrong with request data"]);
        }

        try{
            Mail::to($employee->email)->send(new SendEmployeeAccount($account,$admin,$employee));
        }catch (\Exception $e){
            $email = new UserEmails();
            $email->type = "Send Accounts";
            $email->to = $employee->email;
            $email->admin_id = $admin->id;
            $email->status = 2;
            $email->response = $e->getMessage();
            $email->save();
            return response()->json(['error' => true, 'message' => $e->getMessage()]);
        }
        $email = new UserEmails();
        $email->type = "Send Accounts";
        $email->to = $employee->email;
        $email->admin_id = $admin->id;
        $email->response = "success";
        $email->save();
        return response()->json(['success' => true, 'message' => 'sent account to employee ']);
    }
}
