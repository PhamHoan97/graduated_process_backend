<?php

namespace App\Http\Controllers\Api\System;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Employees;
use App\Roles;
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

    // get edit information department
    public function getEditDepartment(Request $request,$idDepartment){
        try {
            $department = DB::table('departments')->where('id',$idDepartment)->first();
            return response()->json(['message'=>'get detail department','department'=>$department],200);
        }catch(\Exception $e) {
            return response()->json(["error" => $e->getMessage()],400);
        }

    }
    // get detail information department
    public function getDetailDepartment(Request $request,$idDepartment){
        try {
            $department = DB::table('departments')->where('id',$idDepartment)->first();
            $dataDetailDepartment = array(
                'name'=>$department->name,
                'signature'=>$department->signature,
                'description'=>$department->description,
            );
            $roles =  DB::table('roles')->where('department_id',$idDepartment)->get();
            $dataRoles =  array();
            foreach ($roles as $role){
                $countEmployees = DB::table('employees')->where('role_id',$role->id)->count();
                $dataRole = array(
                    'name'=>$role->name,
                    'id'=>$role->id,
                    'description'=>$role->description,
                    'employees'=>$countEmployees
                );
                array_push($dataRoles,$dataRole);

            }
            $dataDetailDepartment['role'] = $dataRoles;
            return response()->json(['message'=>'get detail department','detailDepartment'=>$dataDetailDepartment],200);
        }catch(\Exception $e) {
            return response()->json(["error" => $e->getMessage()],400);
        }

    }

    // New item department
    public function addDepartment(Request $request){
        $name = $request->newNameDepartment;
        $description = $request->newDescriptionDepartment;
        $signature = $request->newSignatureDepartment;
        $idCompany = $request->idCompany;
        try {
            DB::table('departments')->insert(
                ['name' => $name, 'description' => $description,'signature'=>$signature,'company_id' => $idCompany]
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
        $newSignture = $request->editSignatureDepartment;
        $idDepartment = $request->idDepartment;
        try {
            DB::table('departments')
                ->Where('id', '=', $idDepartment)
                ->update(['name' => $newName,'description'=>$newDescription,'signature'=>$newSignture]);
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
                    'employees.avatar as avatar',
                    'employees.email as email',
                    'employees.gender as gender',
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
        $gender = $request->newIsMale;
        // check email unique
        $phone = $request->newPhoneEmployee;
        $idChooseRole = $request->newRoleEmployee;
        $idChooseDepartment = $request->newDepartmentEmployee;
        try {
            if($this->checkAddInputEmail($email)){
                return response()->json(['error'=>'Add fail new employee'],200);
            }
            DB::table('employees')->insert(
                [
                    'name' => $name,
                    'email' => $email,
                    'gender' => $gender,
                    'phone' => $phone,
                    'role_id'=>$idChooseRole,
                    'department_id' => $idChooseDepartment
                ]
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
        $newGender = $request->editIsMale;
        $idChooseRole= $request->idChooseRole;
        $idChooseEmployee = $request->idChooseEmployee;
        $idChooseDepartment = $request->idChooseDepartment;
        try {
            if($this->checkEditInputEmail($newEmail,$idChooseEmployee)){
                return response()->json(['error'=>'update fail user'],200);
            }else{
                DB::table('employees')
                    ->Where('id', '=', $idChooseEmployee)
                    ->update([
                        'name' => $newName,
                        'email'=>$newEmail,
                        'phone'=>$newPhone,
                        'gender' => $newGender,
                        'role_id'=>$idChooseRole,
                        'department_id'=>$idChooseDepartment]);
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
                    'employees.email as email',
                    'employees.birth as birth',
                    'employees.avatar as avatar',
                    'employees.gender as gender',
                    'employees.phone as phone',
                    'employees.address as address',
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
        $description = $request->newDescriptionRole;
        $idChooseDepartment = $request->newDepartmentRole;
        try {
            DB::table('roles')->insert(
                [
                    'name' => $name,
                    'description'=>$description,
                    'is_process' => $isProcess,
                    'department_id' => $idChooseDepartment
                ]
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
        $newDescription = $request->editDescriptionRole;
        $idChooseRole = $request->idChooseRole;
        $idChooseDepartment = $request->idChooseDepartment;
        try {
            DB::table('roles')
                ->Where('id', '=', $idChooseRole)
                ->update([
                    'name' => $newName,
                    'description' => $newDescription,
                    'is_process' => $newIsProcess,
                    'department_id'=>$idChooseDepartment
                ]);
            return response()->json(['message'=>'update success role'],200);
        }catch(\Exception $e) {
            return response()->json(["error" => $e->getMessage()],400);
        }
    }

    // get edit information role
    public function getEditRole(Request $request,$idRole){
        try {
            $role = DB::table('roles') ->where('id',$idRole)->first();
            return response()->json(['message'=>'get detail employee','role'=>$role],200);
        }catch(\Exception $e) {
            return response()->json(["error" => $e->getMessage()],400);
        }
    }

    // get detail information role
    public function getDetailRole(Request $request){
        $idRole = $request->idRole;
        $idDepartment = $request->idDepartment;
        try {
            $role = DB::table('roles') ->where('id',$idRole)->first();
            $employees = DB::table('employees')
                ->join('departments', 'departments.id', '=', 'employees.department_id')
                ->join('roles', 'roles.id', '=', 'employees.role_id')
                ->where('employees.role_id',$idRole)
                ->where('employees.department_id',$idDepartment)
                ->select('employees.id as id',
                    'employees.name as name',
                    'employees.email as email',
                    'employees.gender as gender',
                    'employees.phone as phone',
                    'employees.avatar as avatar',
                    'employees.role_id as role_id',
                    'employees.department_id as department_id',
                    'roles.name as role_name',
                    'departments.name as department_name')
                ->get();
            $dataDetailRole = array(
                'name'=>$role->name,
                'description'=>$role->description,
                'employees'=>$employees
            );
            return response()->json(['message'=>'get detail role','detailRole'=>$dataDetailRole],200);
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
                    'roles.description as description',
                    'roles.is_process as is_process',
                    'roles.department_id as department_id',
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
                "email"=>$company->contact,
                "tags"=>array(
                    'Company'
                ),
                "title"=>"Công ty",
                "img" => "https://cdn.balkan.app/shared/1.jpg",
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
                        "name"=>$department->name,
                        'title'=>"Department"
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
                                "name"=>$role->name,
                                "title"=>"Role"
                            );
                            array_push($dataOrganization, $organizationRole);
                            $idRole = $id;
                            $id++;
                            $employees = \App\Roles::where('id', '=', $role->id)->first()->employees()->get(['name','email','id']);
                            if($employees !==null){
                                foreach ($employees as $keyEmployee => $employee){
                                    $detailRole = DB::table('roles')->where('id',$role->id)->first();
                                    $organizationEmployee = array(
                                        "id"=>$id,
                                        "pid"=>$idRole,
                                        "tags"=>array(
                                            'Employee'
                                        ),
                                        "name"=>$employee->name,
                                        "title"=>$detailRole->name,
                                        "email"=>$employee->email,
                                        "img"=>"https://cdn.balkan.app/shared/8.jpg"
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

    // search employee in company
    public function searchEmployeeCompany(Request $request){
        $textNameSearch = $request->textNameSearch;
        $textEmailSearch = $request->textEmailSearch;
        $idDepartmentSearch = $request->idDepartmentSearch;
        $idCompany = $request->idCompany;
        try {
            $employees = Employees::query()
                ->join('roles', 'employees.role_id', '=', 'roles.id')
                ->join('departments', 'employees.department_id', '=', 'departments.id')
                ->join('companies', 'companies.id', '=', 'departments.company_id')
                ->where('companies.id',$idCompany)
                ->name($textNameSearch)
                ->email($textEmailSearch)
                ->department($idDepartmentSearch)
                ->select('employees.id as id_employee',
                    'employees.name as name',
                    'employees.avatar as avatar',
                    'employees.email as email',
                    'employees.gender as gender',
                    'employees.phone as phone',
                    'employees.address as address',
                    'roles.name as role',
                    'roles.id as id_role',
                    'departments.id as id_department',
                    'departments.name as department_name')
                ->get();
            return response()->json(['message'=>'Get success all roles in department','employees'=>$employees],200);
        }catch(\Exception $e) {
            return response()->json(["error" => $e->getMessage()],400);
        }
    }

    // search employee in company
    public function searchRoleCompany(Request $request){
        $textNameSearch = $request->textNameSearch;
        $idDepartmentSearch = $request->idDepartmentSearch;
        $idCompany = $request->idCompany;
        try {
            $roles = Roles::query()
                ->join('departments', 'roles.department_id', '=', 'departments.id')
                ->join('companies', 'companies.id', '=', 'departments.company_id')
                ->where('companies.id',$idCompany)
                ->name($textNameSearch)
                ->department($idDepartmentSearch)
                ->select(
                    'roles.id as id',
                    'roles.name as name',
                    'roles.description as description',
                    'roles.is_process as is_process',
                    'roles.department_id as department_id',
                    'companies.name as company_name',
                    'departments.name as department_name')
                ->get();
            return response()->json(['message'=>'Get success all roles in company','roles'=>$roles],200);
        }catch(\Exception $e) {
            return response()->json(["error" => $e->getMessage()],400);
        }
    }


}
