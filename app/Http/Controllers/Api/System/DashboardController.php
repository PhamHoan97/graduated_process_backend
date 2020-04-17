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
                $processes = DB::table('companies')
                    ->join('departments', 'companies.id', '=', 'departments.company_id')
                    ->join('employees', 'departments.id', '=', 'employees.department_id')
                    ->join('processes', 'employees.id', '=', 'processes.employee_id')
                    ->where('processes.name','LIKE','%'.$textSearch.'%')
                    ->select('processes.id',
                        'processes.name as process_name',
                        'departments.name as department_name',
                        'companies.name as company_name',
                        'processes.description',
                        'processes.update_at')
                    ->get();
            }else{
                $processes = DB::table('companies')
                    ->join('departments', 'companies.id', '=', 'departments.company_id')
                    ->join('employees', 'departments.id', '=', 'employees.department_id')
                    ->join('processes', 'employees.id', '=', 'processes.employee_id')
                    ->select('processes.id',
                        'processes.name as process_name',
                        'departments.name as department_name',
                        'companies.name as company_name',
                        'processes.description',
                        'processes.update_at')
                    ->get();
            }

            return response()->json(['message'=>'Get success all process','companies'=>$processes],200);
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
            $processes = DB::table('companies')
                ->join('departments', 'companies.id', '=', 'departments.company_id')
                ->join('employees', 'departments.id', '=', 'employees.department_id')
                ->join('processes', 'employees.id', '=', 'processes.employee_id')
                ->where('departments.company_id', $idCompany)
                ->orderBy('processes.id', 'ASC')
                ->select('processes.id',
                    'processes.name',
                    'processes.description',
                    'processes.update_at',
                    'companies.id as company_id',
                    'employees.name as employee_name')
                ->get();
            return response()->json(['message'=>'Get success all processes of a company','processes'=>$processes],200);
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
            $processes = DB::table('companies')
                ->join('departments', 'companies.id', '=', 'departments.company_id')
                ->join('employees', 'departments.id', '=', 'employees.department_id')
                ->join('processes', 'employees.id', '=', 'processes.employee_id')
                ->where([
                    ['departments.company_id', '=', $idCompany],
                    ['departments.id', '=', $idDepartment],
                ])
                ->orderBy('processes.id', 'ASC')
                ->select('processes.id','processes.name','processes.description','processes.update_at','departments.id as department_id','employees.name as employee_name')
                ->get();
            return response()->json(['message'=>'Get success all processes of a company','processes'=>$processes],200);
        }catch(\Exception $e) {
            return response()->json(["error" => $e->getMessage()],400);
        }
    }

}
