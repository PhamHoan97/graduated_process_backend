<?php

namespace App\Http\Controllers\Api\System;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class ManageCompanyController extends Controller
{
    public function getDetailCompany(Request $request){
        $idCompany = $request->idCompany;
        try {
            $company = DB::table('companies')->where('id',$idCompany)->first();
            return response()->json(['message'=>'get success detail company ','company'=>$company],200);
        }catch(\Exception $e) {
            return response()->json(["error" => $e->getMessage()],400);
        }
    }

    public function getStatisticOrganization(Request $request){
        $idCompany = $request->idCompany;
        try {
            $employees =  DB::table('employees')
                ->join('departments', 'departments.id', '=', 'employees.department_id')
                ->join('companies', 'companies.id', '=', 'departments.company_id')
                ->where('companies.id',$idCompany)->distinct()->count();
            $departments =  DB::table('departments')
                ->where('departments.company_id',$idCompany)->distinct()->count();
            $notifications = DB::table('admin_notifications')
                ->join('admins', 'admins.id', '=', 'admin_notifications.admin_id')
                ->join('companies', 'companies.id', '=', 'admins.company_id')
                ->where('companies.id',$idCompany)->distinct()->count();
            $statistics = array(
                'employees'=>$employees,
                'departments'=>$departments,
                'notifications'=>$notifications
            );
            return response()->json(['message'=>'get success detail company ','statisticsCompany'=>$statistics],200);
        }catch(\Exception $e) {
            return response()->json(["error" => $e->getMessage()],400);
        }
    }

    public function updateInformation(Request $request){
        $idCompany = $request->idCompany;
        $nameCompany = $request->name;
        $ceoCompany = $request->ceo;
        $signatureCompany = $request->signature;
        $addressCompany = $request->address;
        $fieldCompany = $request->field;
        $contactCompany = $request->contact;
        $image = $request->get('file');
        if($image === null){
            try {
                DB::table('companies')
                    ->Where('id', '=', $idCompany)
                    ->update([
                        'name' => $nameCompany,
                        'signature' => $signatureCompany,
                        'address' => $addressCompany,
                        'field' => $fieldCompany,
                        'ceo' => $ceoCompany,
                        'contact' => $contactCompany]);
                return response()->json(['message' => 'update success user'], 200);
            } catch (\Exception $e) {
                return response()->json(["error" => $e->getMessage()], 400);
            }
        }else {
            $nameImage = time() . '.' . explode('/', explode(':', substr($image, 0, strpos($image, ';')))[1])[1];
            try {
                DB::table('companies')
                    ->Where('id', '=', $idCompany)
                    ->update([
                        'name' => $nameCompany,
                        'signature' => $signatureCompany,
                        'address' => $addressCompany,
                        'field' => $fieldCompany,
                        'ceo' => $ceoCompany,
                        'contact' => $contactCompany,
                        'avatar' => '/avatar/company/' . $nameImage]);
                \Image::make($request->get('file'))->save(public_path('avatar/company/') . $nameImage);
                return response()->json(['message' => 'update success user'], 200);
            } catch (\Exception $e) {
                return response()->json(["error" => $e->getMessage()], 400);
            }
        }
    }
}
