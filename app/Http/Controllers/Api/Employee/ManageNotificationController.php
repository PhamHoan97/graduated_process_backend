<?php

namespace App\Http\Controllers\Api\Employee;

use App\Accounts;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class ManageNotificationController extends Controller
{
    // get idAccount when know token
    public function getIdAccountByToken($token){
        $account = Accounts::where('auth_token',$token)->first();
        if(!$account){
            return false;
        }
        return $account->id;
    }
    // get all notifications of employees from system
    public function listEmployeeNotificationSystem(Request $request){
        $token = $request->token;
        $idAccount = $this->getIdAccountByToken($token);
        if(!$idAccount){
            return response()->json(["error" => 'Error get id account with token'],400);
        }else{
            try {
                $notificationEmployeesSystem = DB::table('system_user_notifications')
                    ->join('system_notifications', 'system_notifications.id', '=', 'system_user_notifications.notification_id')
                    ->join('forms', 'system_notifications.form_id', '=', 'forms.id')
                    ->join('templates', 'templates.id', '=', 'forms.template_id')
                    ->where('system_user_notifications.account_id',$idAccount)
                    ->select('system_user_notifications.id as id',
                        'system_notifications.name as name',
                        'system_notifications.description as description',
                        'system_user_notifications.status as status',
                        'system_user_notifications.update_at as update_at',
                        'templates.name as template_name')
                    ->get();
                return response()->json(['message'=>'Get list admin notification success','notificationEmployeesSystem'=>$notificationEmployeesSystem],200);
            }catch(\Exception $e) {
                return response()->json(["error" => $e->getMessage()],400);
            }
        }
    }
    // get all notifications of employees from company
    public function listEmployeeNotificationCompany(Request $request){
        $token = $request->token;
        $idAccount = $this->getIdAccountByToken($token);
        if(!$idAccount){
            return response()->json(["error" => 'Error get id account with token'],400);
        }else{
            try {
                $idAccount = $request->idAccount;
                $notificationFromCompany = DB::table('company_user_notifications')
                    ->join('company_notifications', 'company_notifications.id', '=', 'company_user_notifications.notification_id')
                    ->where('company_user_notifications.account_id',$idAccount)
                    ->select('company_user_notifications.id as id',
                        'company_notifications.name as name',
                        'company_notifications.description as description',
                        'company_user_notifications.status as status',
                        'company_user_notifications.update_at as update_at'
                    )->get();
                return response()->json(['message'=>'Get list notification success','notificationFromCompany'=>$notificationFromCompany],200);
            }catch(\Exception $e) {
                return response()->json(["error" => $e->getMessage()],400);
            }
        }
    }

    public function getInformationTemplateNotificationSystemEmployee(Request $request){
        $idNotificationSystemEmployee = $request->idNotificationSystemEmployee;
        try {
            $notificationEmployee = DB::table('system_user_notifications')
                ->join('system_notifications', 'system_notifications.id', '=', 'system_user_notifications.notification_id')
                ->join('forms', 'forms.id', '=', 'system_notifications.form_id')
                ->join('templates', 'templates.id', '=', 'forms.template_id')
                ->where('system_user_notifications.id',$idNotificationSystemEmployee)
                ->select('system_user_notifications.id as id',
                    'system_user_notifications.update_at as date',
                    'system_notifications.name as name',
                    'system_notifications.description as description',
                    'system_notifications.file as file',
                    'forms.description as description_form',
                    'forms.description as name_form',
                    'templates.content as template_content')
                ->first();
            $data[]= array(
                'id'=>$notificationEmployee->id,
                'date'=>$notificationEmployee->date,
                'name'=>$notificationEmployee->name,
                'file'=>$notificationEmployee->file,
                'description'=>$notificationEmployee->description,
                'name_form'=>$notificationEmployee->name_form,
                'description_form'=>$notificationEmployee->description_form,
                'template_content'=>json_decode($notificationEmployee->template_content),
            );
            return response()->json(['message'=>'Get response employee notification success','notificationEmployee'=>$data],200);
        }catch(\Exception $e) {
            return response()->json(["error" => $e->getMessage()],400);
        }
    }

    // add response employee notification
    public function addResponseEmployee (Request $request){
        $token = $request->token;
        $idAccount = $this->getIdAccountByToken($token);
        if(!$idAccount){
            return response()->json(["error" => 'Error get id account with token'],400);
        }else{
            try {
                $date = date('Y-m-d H:i:s');
                $idNotificationEmployee = $request->idNotificationEmployee;
                $content = \GuzzleHttp\json_encode($request->contentResponse);
                DB::table('user_responses')->insert(
                    [
                        'content' => $content,
                        'update_at'=>$date,
                        'account_id'=>$idAccount,
                        'notification_id'=>$idNotificationEmployee
                    ]
                );
                // update status in admin_notifications
                try {
                    DB::table('system_user_notifications')
                        ->where('id', '=', $idNotificationEmployee)
                        ->update(['status' => 1]);
                }catch (\Exception $e) {
                    return response()->json(["error" => $e->getMessage()],400);
                }
                return response()->json(['message'=>'Add success admin responses'],200);
            }catch(\Exception $e) {
                return response()->json(["error" => $e->getMessage()],400);
            }
        }
    }

    // get detail notification employee from company
    public function getDetailNotificationFromCompany (Request $request){
        try {
            $idNotificationFromCompany = $request->idNotificationFromCompany;
            $detailNotificationCompanyEmployee = DB::table('company_user_notifications')
                ->join('company_notifications', 'company_notifications.id', '=', 'company_user_notifications.notification_id')
                ->where('company_user_notifications.id',$idNotificationFromCompany)
                ->select('company_user_notifications.id as id',
                    'company_notifications.name as name',
                    'company_notifications.file as file',
                    'company_notifications.description as description',
                    'company_notifications.status as status',
                    'company_notifications.update_at as update_at')
                ->first();
            return response()->json(['message'=>'Get response employee notification success','detailNotificationCompanyEmployee'=>$detailNotificationCompanyEmployee],200);
        }catch(\Exception $e) {
            return response()->json(["error" => $e->getMessage()],400);
        }
    }

    // get detail notification employee from company
    public function updateStatusNotificationFormCompany (Request $request){
        try {
            $idNotificationFromCompany = $request->idNotificationFromCompany;
            try {
                DB::table('company_user_notifications')
                    ->Where('id', '=', $idNotificationFromCompany)
                    ->update([
                        'status' => 1,
                    ]);
                return response()->json(['message'=>'update success status notification from company'],200);
            }catch(\Exception $e) {
                return response()->json(["error" => $e->getMessage()],400);
            }
        }catch(\Exception $e) {
            return response()->json(["error" => $e->getMessage()],400);
        }
    }
    // get detail notification employee from company
    public function updateStatusNotificationFormSystem (Request $request){
        try {
            $idNotificationFromSystem = $request->idNotificationFromSystem;
            try {
                DB::table('system_user_notifications')
                    ->where('id', '=', $idNotificationFromSystem)
                    ->update([
                        'status' => 1,
                    ]);
                return response()->json(['message'=>'update success status notification from system'],200);
            }catch(\Exception $e) {
                return response()->json(["error" => $e->getMessage()],400);
            }
        }catch(\Exception $e) {
            return response()->json(["error" => $e->getMessage()],400);
        }
    }

    // delete notification from system
    public function deleteNotificationFormSystem(Request $request){
        try {
            $idNotificationFromSystem = $request->idNotificationFromSystem;
            try {
                DB::table('system_user_notifications')
                    ->where('id', '=', $idNotificationFromSystem)
                    ->delete();
                return response()->json(['message'=>" Delete notification which system sent"],200);
            }catch(\Exception $e) {
                return response()->json(["error" => $e->getMessage()],400);
            }
        }catch(\Exception $e) {
            return response()->json(["error" => $e->getMessage()],400);
        }
    }
    // delete notification from company
    public function deleteNotificationFormCompany(Request $request){
        try {
            $idNotificationFromCompany = $request->idNotificationFromCompany;
            try {
                DB::table('company_user_notifications')
                    ->where('id', '=', $idNotificationFromCompany)
                    ->delete();
                return response()->json(['message'=>' Delete notification which company sent'],200);
            }catch(\Exception $e) {
                return response()->json(["error" => $e->getMessage()],400);
            }
        }catch(\Exception $e) {
            return response()->json(["error" => $e->getMessage()],400);
        }
    }

}
