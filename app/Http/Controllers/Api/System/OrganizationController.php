<?php

namespace App\Http\Controllers\Api\System;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
class OrganizationController extends Controller
{
    // Get idCompany when know idEmployee
    public function getIdCompanyByIdUser(Request $request,$idEmployee){
        try {
            $idCompany = DB::table('companies')
                ->join('departments', 'companies.id', '=', 'departments.company_id')
                ->join('employees', 'departments.id', '=', 'employees.department_id')
                ->where('employees.id', $idEmployee)
                ->select('companies.id as id_company')
                ->get();
            return response()->json(['message'=>'Get success id company by id user','idCompany'=>$idCompany],200);
        }catch(\Exception $e) {
            return response()->json(["error" => $e->getMessage()],400);
        }
    }

    // Get All department in the company
    public function getAllDepartmentCompany(Request $request,$idCompany){
        try {
            $departments = \App\Companies::where('id', '=', $idCompany)->first()->departments;
            return response()->json(['message'=>'Get success detail company by id','departmentCompany'=>$departments],200);
        }catch(\Exception $e) {
            return response()->json(["error" => $e->getMessage()],400);
        }
    }

    public function getDetailCompany(Request $request,$idCompany){
        try {
            $company = DB::table('companies')->where('id',$idCompany)->first();
            return response()->json(['message'=>'get detail department','company'=>$company],200);
        }catch(\Exception $e) {
            return response()->json(["error" => $e->getMessage()],400);
        }

    }

    // New item department
    public function addDepartment(Request $request){
        $name = $request->newNameDepartment;
        $description = $request->newDescriptionDepartment;
        $role = $request->newRoleDepartment;
        $idCompany = $request->idCompany;
        try {
            DB::table('departments')->insert(
                ['name' => $name, 'description' => $description,'role'=>$role,'company_id' => $idCompany]
            );
            return response()->json(['message'=>'Add success new department'],200);
        }catch(\Exception $e) {
            return response()->json(["error" => $e->getMessage()],400);
        }
    }

    // Delete item department
    public function deleteDepartment(Request $request){
        $idDeleteDepartment = $request->idDeleteDepartment;
        try {
            DB::table('departments')
                ->where('id', $idDeleteDepartment)
                ->delete();
            return response()->json(['message'=>'delete success department'],200);
        }catch(\Exception $e) {
            return response()->json(["error" => $e->getMessage()],400);
        }
    }

    // Update item department
    public function updateDepartment(Request $request){
        $newName = $request->editNameDepartment;
        $newDescription = $request->editDescriptionDepartment;
        $newRole = $request->editRoleDepartment;
        $idDepartment = $request->idDepartment;
        try {
            DB::table('departments')
                ->Where('id', '=', $idDepartment)
                ->update(['name' => $newName,'description'=>$newDescription,'role'=>$newRole]);
            return response()->json(['message'=>'update success department'],200);
        }catch(\Exception $e) {
            return response()->json(["error" => $e->getMessage()],400);
        }

    }

    // Get All User in the company
    public function getAllEmployeeCompany(Request $request,$idCompany){
        try {
            $employees = DB::table('companies')
                ->join('departments', 'companies.id', '=', 'departments.company_id')
                ->join('employees', 'departments.id', '=', 'employees.department_id')
                ->where('companies.id', $idCompany)
                ->select('employees.id',
                    'employees.name as employee_name',
                    'employees.address as employee_address',
                    'departments.id as id_department',
                    'departments.name as department_name')
                ->get();
            return response()->json(['message'=>'Get all users in company ','employees'=>$employees],200);
        }catch(\Exception $e) {
            return response()->json(["error" => $e->getMessage()],400);
        }
    }

    // New item employee
    public function addEmployee(Request $request){
        $name = $request->newNameUser;
        $phone = $request->newPhoneUser;
        $role = $request->newRoleUser;
        $idChooseDepartment = $request->idChooseDepartment;
        try {
            DB::table('employees')->insert(
                ['name' => $name, 'phone' => $phone,'role'=>$role,'department_id' => $idChooseDepartment]
            );
            return response()->json(['message'=>'Add success new employee'],200);
        }catch(\Exception $e) {
            return response()->json(["error" => $e->getMessage()],400);
        }
    }

    // delete item employee
    public function deleteEmployee(Request $request){
        $idDeleteUser = $request->idDeleteUser;
        try {
            DB::table('employees')
                ->where('id', $idDeleteUser)
                ->delete();
            return response()->json(['message'=>'delete success user'],200);
        }catch(\Exception $e) {
            return response()->json(["error" => $e->getMessage()],400);
        }
    }

    // update item employee
    public function updateEmployee(Request $request){
        $newName = $request->editNameUser;
        $newPhone = $request->editPhoneUser;
        $newRole = $request->editRoleUser;
        $idChooseUser = $request->idChooseUser;
        $idChooseDepartment = $request->idChooseDepartment;
        try {
            DB::table('employees')
                ->Where('id', '=', $idChooseUser)
                ->update(['name' => $newName,'phone'=>$newPhone,'role'=>$newRole,'department_id'=>$idChooseDepartment]);
            return response()->json(['message'=>'update success user'],200);
        }catch(\Exception $e) {
            return response()->json(["error" => $e->getMessage()],400);
        }
    }

    // Get JSON which support to display chart organization
    public function getJsonOrganization(Request $request){
        try {
            $idCompany = $request->idCompany;
            $dataOrganization = [];
            $company = DB::table('companies')->where('id',$idCompany)->first();
            $organizationCompany =  array(
                "id"=>1,
                "tags"=>array(
                    'Company'
                ),
                "name"=>$company->name
            );
            array_push($dataOrganization, $organizationCompany);
            $departments = \App\Companies::where('id', '=', $idCompany)->first()->departments()->get(['name','id']);
            $id = 2;
            if($departments !==null){
                foreach($departments as $keyDepartment=>$department) {
                    $organizationDepartment =  array(
                        "id"=>$id,
                        "pid"=>1,
                        "tags"=>array(
                            'Department'
                        ),
                        "name"=>$department->name
                    );
                    array_push($dataOrganization, $organizationDepartment);
                    $idDepartment = $id;
                    $id++;
                    $roles = \App\Departments::where('id', '=', $department->id)->first()->roles()->get(['name','id']);
                    if($roles !==null){
                        foreach ($roles as $keyRole =>$role){
                            $organizationRole = array(
                                "id"=>$id,
                                "pid"=>$idDepartment,
                                "tags"=>array(
                                    'Role'
                                ),
                                "name"=>$role->name
                            );
                            array_push($dataOrganization, $organizationRole);
                            $idRole = $id;
                            $id++;
                            $employees = \App\Roles::where('id', '=', $role->id)->first()->employees()->get(['name','id']);
                            if($employees !==null){
                                foreach ($employees as $keyEmployee => $employee){
                                    $organizationEmployee = array(
                                        "id"=>$id,
                                        "pid"=>$idRole,
                                        "tags"=>array(
                                            'Staff'
                                        ),
                                        "name"=>$employee->name
                                    );
                                    array_push($dataOrganization, $organizationEmployee);
                                    $id++;
                                }
                            }
                        }
                    }
                }
            }
            return response()->json(['message'=>'Get success all data json organization','dataOrganization'=>$dataOrganization],200);
        }catch(\Exception $e) {
            return response()->json(["error" => $e->getMessage()],400);
        }
    }
}
