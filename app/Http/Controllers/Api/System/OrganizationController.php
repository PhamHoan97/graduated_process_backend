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

    public function getDetailDepartment(Request $request,$idDepartment){
        try {
            $department = DB::table('departments')->where('id',$idDepartment)->first();
            return response()->json(['message'=>'get detail department','department'=>$department],200);
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
                ->join('roles', 'employees.role_id', '=', 'roles.id')
                ->where('companies.id', $idCompany)
                ->select('employees.id as id_employee',
                    'employees.name as name',
                    'employees.email as email',
                    'employees.phone as phone',
                    'employees.address as address',
                    'roles.name as role',
                    'roles.id as id_role',
                    'departments.id as id_department',
                    'departments.name as department_name')
                ->get();
            return response()->json(['message'=>'Get all users in company ','employees'=>$employees],200);
        }catch(\Exception $e) {
            return response()->json(["error" => $e->getMessage()],400);
        }
    }

    public function checkAddInputEmail($email){
        $allEmails =  DB::table('employees')->get('email');
        foreach ($allEmails as $value){
            if($value->email == $email){
                return true;
            }
        }
        return false;
    }

    // New item employee
    public function addEmployee(Request $request){
        $name = $request->newNameEmployee;
        $email = $request->newEmailEmployee;
        // check email unique
        $phone = $request->newPhoneEmployee;
        $idChooseRole = $request->newRoleEmployee;
        $idChooseDepartment = $request->newDepartmentEmployee;
        try {
            if($this->checkAddInputEmail($email)){
                return response()->json(['error'=>'Add fail new employee'],200);
            }
            DB::table('employees')->insert(
                ['name' => $name, 'email' => $email,'phone' => $phone,'role_id'=>$idChooseRole,'department_id' => $idChooseDepartment]
            );
            return response()->json(['message'=>'Add success new employee'],200);
        }catch(\Exception $e) {
            return response()->json(["error" => $e->getMessage()],400);
        }
    }

    // delete item employee
    public function deleteEmployee(Request $request){
        $idDeleteEmployee= $request->idDeleteEmployee;
        try {
            DB::table('employees')
                ->where('id', $idDeleteEmployee)
                ->delete();
            return response()->json(['message'=>'delete success user'],200);
        }catch(\Exception $e) {
            return response()->json(["error" => $e->getMessage()],400);
        }
    }

    public function checkEditInputEmail($email,$idEmployee){
        $allEmailCheck =  DB::table('employees')->where('id','!=',$idEmployee)->get('email');
        foreach ($allEmailCheck as $value){
            if($value->email == $email){
                return true;
            }
        }
        return false;
    }
    // update item employee
    public function updateEmployee(Request $request){
        $newName = $request->editNameEmployee;
        $newPhone = $request->editPhoneEmployee;
        $newEmail = $request->editEmailEmployee;
        $idChooseRole= $request->idChooseRole;
        $idChooseEmployee = $request->idChooseEmployee;
        $idChooseDepartment = $request->idChooseDepartment;
        try {
            if($this->checkEditInputEmail($newEmail,$idChooseEmployee)){
                return response()->json(['error'=>'update fail user'],200);
            }else{
                DB::table('employees')
                    ->Where('id', '=', $idChooseEmployee)
                    ->update(['name' => $newName,'email'=>$newEmail,'phone'=>$newPhone,'role_id'=>$idChooseRole,'department_id'=>$idChooseDepartment]);
                return response()->json(['message'=>'update success user'],200);
            }
        }catch(\Exception $e) {
            return response()->json(["error" => $e->getMessage()],400);
        }
    }

    public function getDetailEmployee(Request $request,$idEmployee){
        try {
            $employee = DB::table('companies')
                ->join('departments', 'companies.id', '=', 'departments.company_id')
                ->join('employees', 'departments.id', '=', 'employees.department_id')
                ->join('roles', 'employees.role_id', '=', 'roles.id')
                ->where('employees.id', $idEmployee)
                ->select('employees.id as id',
                    'employees.name as name',
//                    'employees.email as email',
                    'employees.phone as phone',
                    'employees.role_id as role_id',
                    'employees.department_id as department_id',
                    'roles.name as role_name',
                    'departments.name as department_name')
                ->first();
            return response()->json(['message'=>'get detail employee','employee'=>$employee],200);
        }catch(\Exception $e) {
            return response()->json(["error" => $e->getMessage()],400);
        }

    }

    // New item role
    public function addRole(Request $request){
        $name = $request->newNameRole;
        $isProcess = $request->newIsProcessRole;
        $idChooseDepartment = $request->newDepartmentRole;
        try {
            DB::table('roles')->insert(
                ['name' => $name,'is_process' => $isProcess,'department_id' => $idChooseDepartment]
            );
            return response()->json(['message'=>'Add success new role'],200);
        }catch(\Exception $e) {
            return response()->json(["error" => $e->getMessage()],400);
        }
    }

    // delete item role
    public function deleteRole(Request $request){
        $idDeleteRole= $request->idDeleteRole;
        try {
            DB::table('roles')
                ->where('id', $idDeleteRole)
                ->delete();
            return response()->json(['message'=>'delete success roles'],200);
        }catch(\Exception $e) {
            return response()->json(["error" => $e->getMessage()],400);
        }
    }

    // update item role
    public function updateRole(Request $request){
        $newName = $request->editNameRole;
        $newIsProcess = $request->editIsProcessRole;
        $idChooseRole = $request->idChooseRole;
        $idChooseDepartment = $request->idChooseDepartment;
        try {
            DB::table('roles')
                ->Where('id', '=', $idChooseRole)
                ->update([
                    'name' => $newName,
                    'is_process' => $newIsProcess,
                    'department_id'=>$idChooseDepartment
                ]);
            return response()->json(['message'=>'update success role'],200);
        }catch(\Exception $e) {
            return response()->json(["error" => $e->getMessage()],400);
        }
    }

    // get detail information role
    public function getDetailRole(Request $request,$idRole){
        try {
            $role = DB::table('roles')->where('id',$idRole)->first();
            return response()->json(['message'=>'get detail employee','role'=>$role],200);
        }catch(\Exception $e) {
            return response()->json(["error" => $e->getMessage()],400);
        }

    }

    // get all  role of company
    public function getAllRoles(Request $request,$idCompany){
        try {
            $roles = DB::table('roles')
                ->join('departments', 'departments.id', '=', 'roles.department_id')
                ->join('companies', 'companies.id', '=', 'departments.company_id')
                ->where('companies.id',$idCompany)
                ->select(
                    'roles.id as id',
                    'roles.name as name',
                    'companies.name as company_name',
                    'departments.name as department_name')
                ->get();
            return response()->json(['message'=>'get detail employee','roles'=>$roles],200);
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

    // get all role in a department
    public function getRolesDepartment(Request $request,$idDepartment){
        try {
            $roles = \App\Departments::where('id', '=', $idDepartment)->first()->roles;
            return response()->json(['message'=>'Get success all roles in department','roleDepartment'=>$roles],200);
        }catch(\Exception $e) {
            return response()->json(["error" => $e->getMessage()],400);
        }
    }


}
