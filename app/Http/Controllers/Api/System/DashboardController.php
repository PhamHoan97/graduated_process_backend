<?php

namespace App\Http\Controllers\Api\System;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
class DashboardController extends Controller
{

    public function  getAllCompanies(Request $request){
        try {
            $companies =  DB::table('companies')->get();
            return response()->json(['message'=>'Get success all companies','companies'=>$companies],200);
        }catch(\Exception $e) {
            return response()->json(["error" => $e->getMessage()],400);
        }
    }

    public function  getAllProcessSearch(Request $request){
        $textSearch = $request->textSearch;
        try {
            if($textSearch !== 'all'){
                $processes1 = DB::table('companies')
                    ->join('departments', 'companies.id', '=', 'departments.company_id')
                    ->join('employees', 'departments.id', '=', 'employees.department_id')
                    ->join('processes_employees', 'employees.id', '=', 'processes_employees.employee_id')
                    ->join('processes', 'processes_employees.process_id', '=', 'processes.id')
                    ->where('processes.name','LIKE','%'.$textSearch.'%')
                    ->orwhere('processes.code','LIKE','%'.$textSearch.'%')
                    ->select('processes.id as id',
                        'processes.code as code',
                        'processes.type as type',
                        'processes.name as process_name',
                        'companies.name as company_name',
                        'processes.description as description',
                        'processes.update_at as date')->distinct()
                    ->get();
                $processesDuplicate = DB::table('companies')
                    ->join('departments', 'companies.id', '=', 'departments.company_id')
                    ->join('employees', 'departments.id', '=', 'employees.department_id')
                    ->join('processes_employees', 'employees.id', '=', 'processes_employees.employee_id')
                    ->join('processes', 'processes_employees.process_id', '=', 'processes.id')
                    ->where('processes.name','LIKE','%'.$textSearch.'%')
                    ->orwhere('processes.code','LIKE','%'.$textSearch.'%')
                    ->where('processes.type',5)
                    ->select('processes.id as id')->distinct()
                    ->get();
                $idDuplicate = [];
                foreach ($processesDuplicate as $item){
                    $idDuplicate []= $item->id;
                }
                $processes2 = DB::table('companies')
                    ->join('departments', 'companies.id', '=', 'departments.company_id')
                    ->join('roles', 'departments.id', '=', 'roles.department_id')
                    ->join('processes_roles', 'roles.id', '=', 'processes_roles.role_id')
                    ->join('processes', 'processes_roles.process_id', '=', 'processes.id')
                    ->where('processes.name','LIKE','%'.$textSearch.'%')
                    ->orwhere('processes.code','LIKE','%'.$textSearch.'%')
                    ->whereNotIn('processes.id',$idDuplicate)
                    ->select('processes.id as id',
                        'processes.code as code',
                        'processes.type as type',
                        'processes.name as process_name',
                        'companies.name as company_name',
                        'processes.description as description',
                        'processes.update_at as date')->distinct()
                    ->get();
                $processes3 = DB::table('companies')
                    ->join('departments', 'companies.id', '=', 'departments.company_id')
                    ->join('processes_departments', 'departments.id', '=', 'processes_departments.department_id')
                    ->join('processes', 'processes_departments.process_id', '=', 'processes.id')
                    ->where('processes.name','LIKE','%'.$textSearch.'%')
                    ->orwhere('processes.code','LIKE','%'.$textSearch.'%')
                    ->whereNotIn('processes.id',$idDuplicate)
                    ->select('processes.id as id',
                        'processes.code as code',
                        'processes.type as type',
                        'processes.name as process_name',
                        'companies.name as company_name',
                        'processes.description as description',
                        'processes.update_at as date')->distinct()
                    ->get();
                $processes4 = DB::table('companies')
                    ->join('processes_companies', 'companies.id', '=', 'processes_companies.company_id')
                    ->join('processes', 'processes_companies.process_id', '=', 'processes.id')
                    ->where('processes.name','LIKE','%'.$textSearch.'%')
                    ->orwhere('processes.code','LIKE','%'.$textSearch.'%')
                    ->select('processes.id as id',
                        'processes.code as code',
                        'processes.type as type',
                        'processes.name as process_name',
                        'companies.name as company_name',
                        'processes.description as description',
                        'processes.update_at as date')->distinct()
                    ->get();
            }else{
                $processes1 = DB::table('companies')
                    ->join('departments', 'companies.id', '=', 'departments.company_id')
                    ->join('employees', 'departments.id', '=', 'employees.department_id')
                    ->join('processes_employees', 'employees.id', '=', 'processes_employees.employee_id')
                    ->join('processes', 'processes_employees.process_id', '=', 'processes.id')
                    ->select('processes.id as id',
                        'processes.code as code',
                        'processes.type as type',
                        'processes.name as process_name',
                        'companies.name as company_name',
                        'processes.description as description',
                        'processes.update_at as date')->distinct()
                    ->get();
                $processesDuplicate = DB::table('companies')
                    ->join('departments', 'companies.id', '=', 'departments.company_id')
                    ->join('employees', 'departments.id', '=', 'employees.department_id')
                    ->join('processes_employees', 'employees.id', '=', 'processes_employees.employee_id')
                    ->join('processes', 'processes_employees.process_id', '=', 'processes.id')
                    ->where('processes.type',5)
                    ->select('processes.id as id')->distinct()
                    ->get();
                $idDuplicate = [];
                foreach ($processesDuplicate as $item){
                    $idDuplicate []= $item->id;
                }
                $processes2 = DB::table('companies')
                    ->join('departments', 'companies.id', '=', 'departments.company_id')
                    ->join('roles', 'departments.id', '=', 'roles.department_id')
                    ->join('processes_roles', 'roles.id', '=', 'processes_roles.role_id')
                    ->join('processes', 'processes_roles.process_id', '=', 'processes.id')
                    ->whereNotIn('processes.id',$idDuplicate)
                    ->select('processes.id as id',
                        'processes.code as code',
                        'processes.type as type',
                        'processes.name as process_name',
                        'companies.name as company_name',
                        'processes.description as description',
                        'processes.update_at as date')->distinct()
                    ->get();
                $processes3 = DB::table('companies')
                    ->join('departments', 'companies.id', '=', 'departments.company_id')
                    ->join('processes_departments', 'departments.id', '=', 'processes_departments.department_id')
                    ->join('processes', 'processes_departments.process_id', '=', 'processes.id')
                    ->whereNotIn('processes.id',$idDuplicate)
                    ->select('processes.id as id',
                        'processes.code as code',
                        'processes.type as type',
                        'processes.name as process_name',
                        'companies.name as company_name',
                        'processes.description as description',
                        'processes.update_at as date')->distinct()
                    ->get();
                $processes4 = DB::table('companies')
                    ->join('processes_companies', 'companies.id', '=', 'processes_companies.company_id')
                    ->join('processes', 'processes_companies.process_id', '=', 'processes.id')
                    ->select('processes.id as id',
                        'processes.code as code',
                        'processes.type as type',
                        'processes.name as process_name',
                        'companies.name as company_name',
                        'processes.description as description',
                        'processes.update_at as date')->distinct()
                    ->get();
            }

            return response()->json([
                'message'=>'Get success all process',
                'processes1'=>$processes1,
                'processes2'=>$processes2,
                'processes3'=>$processes3,
                'processes4'=>$processes4,
            ],200);
        }catch(\Exception $e) {
            return response()->json(["error" => $e->getMessage()],400);
        }
    }

    public function getDetailCompanyById(Request $request,$idCompany){
        try {
            $company = DB::table('companies')->where('id', $idCompany)->first();
            return response()->json(['message'=>'Get success detail company by id','company'=>$company],200);
        }catch(\Exception $e) {
            return response()->json(["error" => $e->getMessage()],400);
        }
    }

    public function getAllDepartmentCompany(Request $request,$idCompany){
        try {
            $departments = \App\Companies::where('id', '=', $idCompany)->first()->departments;
            return response()->json(['message'=>'Get success detail company by id','departmentCompany'=>$departments],200);
        }catch(\Exception $e) {
            return response()->json(["error" => $e->getMessage()],400);
        }
    }

    public function getAllProcessCompany(Request $request,$idCompany){
        try {
            $processes1 = DB::table('companies')
                ->join('departments', 'companies.id', '=', 'departments.company_id')
                ->join('employees', 'departments.id', '=', 'employees.department_id')
                ->join('processes_employees', 'employees.id', '=', 'processes_employees.employee_id')
                ->join('processes', 'processes_employees.process_id', '=', 'processes.id')
                ->where('companies.id', $idCompany)
                ->orderBy('processes.id', 'ASC')
                ->select('processes.id',
                    'processes.code as code',
                    'processes.type as type',
                    'processes.name as name',
                    'processes.description as description',
                    'processes.update_at as date',
                    'processes.deadline as deadline'
                )->distinct()
                ->get();
            $processesDuplicate = DB::table('companies')
                ->join('departments', 'companies.id', '=', 'departments.company_id')
                ->join('employees', 'departments.id', '=', 'employees.department_id')
                ->join('processes_employees', 'employees.id', '=', 'processes_employees.employee_id')
                ->join('processes', 'processes_employees.process_id', '=', 'processes.id')
                ->where('companies.id', $idCompany)
                ->where('processes.type',5)
                ->select('processes.id')->distinct()
                ->get();
            $idDuplicate = [];
            foreach ($processesDuplicate as $item){
                $idDuplicate []= $item->id;
            }
            $processes2 = DB::table('companies')
                ->join('departments', 'companies.id', '=', 'departments.company_id')
                ->join('roles', 'departments.id', '=', 'roles.department_id')
                ->join('processes_roles', 'roles.id', '=', 'processes_roles.role_id')
                ->join('processes', 'processes_roles.process_id', '=', 'processes.id')
                ->where('companies.id', $idCompany)
                ->whereNotIn('processes.id',$idDuplicate)
                ->orderBy('processes.id', 'ASC')
                ->select('processes.id',
                    'processes.code as code',
                    'processes.type as type',
                    'processes.name as name',
                    'processes.description as description',
                    'processes.update_at as date',
                    'processes.deadline as deadline'
                )->distinct()
                ->get();
            $processes3 = DB::table('companies')
                ->join('departments', 'companies.id', '=', 'departments.company_id')
                ->join('processes_departments', 'departments.id', '=', 'processes_departments.department_id')
                ->join('processes', 'processes_departments.process_id', '=', 'processes.id')
                ->where('companies.id', $idCompany)
                ->whereNotIn('processes.id',$idDuplicate)
                ->select('processes.id as id',
                    'processes.code as code',
                    'processes.type as type',
                    'processes.name as name',
                    'processes.description as description',
                    'processes.update_at as date',
                    'processes.deadline as deadline'
                )->distinct()
                ->get();
            $processes4 = DB::table('companies')
                ->join('processes_companies', 'companies.id', '=', 'processes_companies.company_id')
                ->join('processes', 'processes_companies.process_id', '=', 'processes.id')
                ->where('companies.id', $idCompany)
                ->whereNotIn('processes.id',$idDuplicate)
                ->select('processes.id as id',
                    'processes.code as code',
                    'processes.type as type',
                    'processes.name as name',
                    'processes.description as description',
                    'processes.update_at as date',
                    'processes.deadline as deadline'
                )->distinct()
                ->get();
            return response()->json([
                'message'=>'Get success all processes of a company',
                'processes1'=>$processes1,
                'processes2'=>$processes2,
                'processes3'=>$processes3,
                'processes4'=>$processes4,
            ],200);
        }catch(\Exception $e) {
            return response()->json(["error" => $e->getMessage()],400);
        }
    }

    public function getDetailProcessById(Request $request,$idProcess){
        try {
            $process = DB::table('processes')->where('id', $idProcess)->first();
            return response()->json(['message'=>'Get success detail process by id','company'=>$process],200);
        }catch(\Exception $e) {
            return response()->json(["error" => $e->getMessage()],400);
        }
    }

    public function getAllProcessDepartment(Request $request,$idDepartment,$idCompany){
        try {
            $processes1 = DB::table('companies')
                ->join('departments', 'companies.id', '=', 'departments.company_id')
                ->join('employees', 'departments.id', '=', 'employees.department_id')
                ->join('processes_employees', 'employees.id', '=', 'processes_employees.employee_id')
                ->join('processes', 'processes_employees.process_id', '=', 'processes.id')
                ->where([
                    ['companies.id', '=', $idCompany],
                    ['departments.id', '=', $idDepartment],
                ])
                ->orderBy('processes.id', 'ASC')
                ->select(
                    'processes.id as id',
                    'processes.code as code',
                    'processes.name as name',
                    'processes.description as description',
                    'processes.update_at as date',
                    'departments.id as department_id',
                    'processes.deadline as deadline',
                    'processes.type as type'
                )->distinct()
                ->get();
            $processes2 = DB::table('companies')
                ->join('departments', 'companies.id', '=', 'departments.company_id')
                ->join('roles', 'departments.id', '=', 'roles.department_id')
                ->join('processes_roles', 'roles.id', '=', 'processes_roles.role_id')
                ->join('processes', 'processes_roles.process_id', '=', 'processes.id')
                ->where([
                    ['companies.id', '=', $idCompany],
                    ['departments.id', '=', $idDepartment],
                ])
                ->orderBy('processes.id', 'ASC')
                ->select(
                    'processes.id as id',
                    'processes.code as code',
                    'processes.name as name',
                    'processes.description as description',
                    'processes.update_at as date',
                    'departments.id as department_id',
                    'processes.deadline as deadline',
                    'processes.type as type'
                )->distinct()
                ->get();
            $processes3 = DB::table('companies')
                ->join('departments', 'companies.id', '=', 'departments.company_id')
                ->join('processes_departments', 'departments.id', '=', 'processes_departments.department_id')
                ->join('processes', 'processes_departments.process_id', '=', 'processes.id')
                ->where([
                    ['companies.id', '=', $idCompany],
                    ['departments.id', '=', $idDepartment],
                ])
                ->orderBy('processes.id', 'ASC')
                ->select(
                    'processes.id as id',
                    'processes.code as code',
                    'processes.name as name',
                    'processes.description as description',
                    'processes.update_at as date',
                    'departments.id as department_id',
                    'processes.deadline as deadline',
                    'processes.type as type'
                )->distinct()
                ->get();
            $processes4 = DB::table('companies')
                ->join('processes_companies', 'companies.id', '=', 'processes_companies.company_id')
                ->join('processes', 'processes_companies.process_id', '=', 'processes.id')
                ->where([
                    ['companies.id', '=', $idCompany],
                ])
                ->orderBy('processes.id', 'ASC')
                ->select(
                    'processes.id as id',
                    'processes.code as code',
                    'processes.name as name',
                    'processes.description as description',
                    'processes.update_at as date',
                    'departments.id as department_id',
                    'processes.deadline as deadline',
                    'processes.type as type'
                )->distinct()
                ->get();
            return response()->json([
                'message'=>'Get success all processes of a company',
                'processes1'=>$processes1,
                'processes2'=>$processes2,
                'processes3'=>$processes3,
                'processes4'=>$processes4,
            ],200);
        }catch(\Exception $e) {
            return response()->json(["error" => $e->getMessage()],400);
        }
    }

    // filter process with type in detail company
    public function getAllProcessByType(Request $request){
        $idCompany = $request->idCompany;
        $type = $request->type;
        if($type == 0){
            try {
                // get all processes in company
                $processes = DB::table('processes')
                    ->join('admins', 'admins.id', '=', 'processes.admin_id')
                    ->join('companies', 'companies.id', '=', 'admins.company_id')
                    ->where('companies.id',$idCompany)
                    ->select('processes.id as id',
                        'processes.code as code',
                        'processes.type as type',
                        'processes.name as name',
                        'processes.description as description',
                        'processes.update_at as date',
                        'processes.deadline as deadline'
                    )
                    ->distinct()
                    ->get();
                return response()->json(['message'=>'get all process of company','processes'=>$processes],200);
            }catch(\Exception $e) {
                return response()->json(["error" => $e->getMessage()],400);
            }
        }else{
            try {
                // get all processes in company
                $processes = DB::table('processes')
                    ->join('admins', 'admins.id', '=', 'processes.admin_id')
                    ->join('companies', 'companies.id', '=', 'admins.company_id')
                    ->where('companies.id',$idCompany)
                    ->where('processes.type',$type)
                    ->select('processes.id as id',
                        'processes.code as code',
                        'processes.type as type',
                        'processes.name as name',
                        'processes.description as description',
                        'processes.update_at as date',
                        'processes.deadline as deadline'
                    )
                    ->distinct()
                    ->get();
                return response()->json(['message'=>'get processes with type','processes'=>$processes],200);
            }catch(\Exception $e) {
                return response()->json(["error" => $e->getMessage()],400);
            }
        }
    }

    public function searchProcessesInCompany(Request $request){
        $searchText = $request->searchText;
        $idCompany = $request->idCompany;
        try {
            $processes = DB::table('processes')
                ->join('admins', 'admins.id', '=', 'processes.admin_id')
                ->join('companies', 'companies.id', '=', 'admins.company_id')
                ->where('companies.id',$idCompany)
                ->where('processes.name','LIKE', '%' . $searchText . '%')
                ->orWhere('processes.code','LIKE', '%' . $searchText . '%')
                ->select('processes.id as id',
                    'processes.code as code',
                    'processes.type as type',
                    'processes.name as name',
                    'processes.description as description',
                    'processes.update_at as date',
                    'processes.deadline as deadline'
                )
                ->distinct()
                ->get();
            return response()->json(['message'=>'search processes with text','processes'=>$processes],200);
        }catch(\Exception $e) {
            return response()->json(["error" => $e->getMessage()],400);
        }
    }
}
