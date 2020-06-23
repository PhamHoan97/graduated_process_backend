<?php

namespace App\Http\Controllers\Api\Company;

use App\Admins;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class ManageNotificationController extends Controller
{
    // get idCompany when know token
    public function getIdCompanyByToken($token){
        $admin = Admins::where('auth_token',$token)->first();
        if(!$admin){
            return false;
        }
        return $admin->company_id;
    }
    // get idAccount when know token
    public function getIdAccountByToken($token){
        $admin = Admins::where('auth_token',$token)->first();
        if(!$admin){
            return false;
        }
        return $admin->id;
    }
    public function listAdminNotification(Request $request){
        $token = $request->token;
        $idAdmin = $this->getIdAccountByToken($token);
        if(!$idAdmin){
            return response()->json(["error" => 'Error get id account with token'],400);
        }else{
            try {
                $notificationAdmins = DB::table('admin_notifications')
                    ->join('system_notifications', 'system_notifications.id', '=', 'admin_notifications.notification_id')
                    ->join('forms', 'system_notifications.form_id', '=', 'forms.id')
                    ->join('templates', 'templates.id', '=', 'forms.template_id')
                    ->where('admin_notifications.admin_id',$idAdmin)
                    ->select('admin_notifications.id as id',
                        'system_notifications.name as name',
                        'system_notifications.description as description',
                        'admin_notifications.status as status',
                        'admin_notifications.update_at as date',
                        'templates.name as template_name')
                    ->get();return response()->json(['message'=>'Get list admin notification success','notificationAdmins'=>$notificationAdmins],200);
            }catch(\Exception $e) {
                return response()->json(["error" => $e->getMessage()],400);
            }
        }
    }
    public function getInformationTemplateNotification(Request $request){
        $idNotificationAdmin = $request->idNotificationAdmin;
        try {
            $notificationAdmin = DB::table('admin_notifications')
                ->join('system_notifications', 'system_notifications.id', '=', 'admin_notifications.notification_id')
                ->join('forms', 'forms.id', '=', 'system_notifications.form_id')
                ->join('templates', 'templates.id', '=', 'forms.template_id')
                ->where('admin_notifications.id',$idNotificationAdmin)
                ->select('admin_notifications.id as id',
                    'admin_notifications.update_at as date',
                    'system_notifications.name as name',
                    'system_notifications.description as description',
                    'system_notifications.file as file',
                    'forms.description as description_form',
                    'forms.name as name_form',
                    'templates.content as template_content')
                ->first();
            $data[]= array(
                'id'=>$notificationAdmin->id,
                'date'=>$notificationAdmin->date,
                'name'=>$notificationAdmin->name,
                'file'=>$notificationAdmin->file,
                'description'=>$notificationAdmin->description,
                'name_form'=>$notificationAdmin->name_form,
                'description_form'=>$notificationAdmin->description_form,
                'template_content'=>json_decode($notificationAdmin->template_content),
            );
            return response()->json(['message'=>'Get response admin notification success','notificationAdmin'=>$data],200);
        }catch(\Exception $e) {
            return response()->json(["error" => $e->getMessage()],400);
        }
    }
    // add response admin notification
    public function addResponseAdmin (Request $request){
        $token = $request->token;
        $idAdmin = $this->getIdAccountByToken($token);
        if(!$idAdmin){
            return response()->json(["error" => 'Error get id account with token'],400);
        }else{
            try {
                $date = date('Y-m-d H:i:s');
                $idNotificationAdmin = $request->idNotificationAdmin;
                $content = \GuzzleHttp\json_encode($request->contentResponse);
                DB::table('admin_responses')->insert(
                    [
                        'content' => $content,
                        'update_at'=>$date,
                        'admin_id'=>$idAdmin,
                        'notification_id'=>$idNotificationAdmin
                    ]
                );
                // update status in admin_notifications
                try {
                    DB::table('admin_notifications')
                        ->Where('id', '=', $idNotificationAdmin)
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
    // get all account employee
    public function getAllAccountEmployee(Request $request,$token){
        $idCompany = $this->getIdCompanyByToken($token);
        if(!$idCompany){
            return response()->json(["error" =>'Error get id company with token'],400);
        }else{
            try {
                $idEmployeeAccount = DB::table('accounts')->pluck('employee_id')->toArray();
                $employees =  DB::table('companies')
                    ->join('departments', 'companies.id', '=', 'departments.company_id')
                    ->join('employees', 'departments.id', '=', 'employees.department_id')
                    ->join('accounts', 'employees.id', '=', 'accounts.employee_id')
                    ->where('companies.id',$idCompany)
                    ->whereIn('employees.id',$idEmployeeAccount)
                    ->select(
                        'employees.id as id',
                        'accounts.id as account_id',
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
    // send notification to employee
    public function sendNotificationEmployee(Request $request){
        try {
            $typeChooses = $request->selectedOptions;
            $idNotification = $request->idNotification;
            foreach ($typeChooses as $typeChoose){
                $records = array();
                $records[] = [
                    'status' => 0,
                    'update_at' => date('Y-m-d H:i:s'),
                    'notification_id' => $idNotification,
                    'account_id' => $typeChoose['value'],
                ];
                DB::table('company_user_notifications')->insert(
                    $records
                );
            }
            try {
                DB::table('company_notifications')
                    ->Where('id', '=', $idNotification)
                    ->update(['status' => 1]);
            }catch (\Exception $e) {
                return response()->json(["error" => $e->getMessage()],400);
            }
            return response()->json(['message'=>'Add success notification company to user'],200);
        }catch(\Exception $e) {
            return response()->json(["error" => $e->getMessage()],400);
        }
    }
    // get list all notifications which company created
    public function getListCreateNotifications(Request $request,$token){
        $idCompany = $this->getIdCompanyByToken($token);
        if (!$idCompany){
            return response()->json(["error" => 'Error get id company with token'],400);
        }else{
            try {
                $notifications = DB::table('company_notifications')->where('company_id',$idCompany)->get();
                return response()->json(['message'=>'Get list notification create company success','notifications'=>$notifications],200);
            }catch(\Exception $e) {
                return response()->json(["error" => $e->getMessage()],400);
            }
        }

    }
    // Create notification company
    public function addNotificationCompany(Request $request){
        $token = $request->token;
        $name = $request->newNameNotification;
        $description = $request->newDescriptionNotification;
        $file = $request->newFileNotification;
        $idCompany = $this->getIdCompanyByToken($token);
        if(!$idCompany){
            return response()->json(["error" => 'Error get id company with token'],400);
        }else{
            if($file === null){
                $dataNotification = [
                    'name' => $name,
                    'description' => $description,
                    'status' => 0,
                    'update_at' => date('Y-m-d H:i:s'),
                    'company_id' => $idCompany
                ];

            }else{
                if(in_array($file->getClientOriginalExtension(),['pdf','doc','docx','odt','txt'])){
                    $file_name = mt_rand();
                    $type = $file->getClientOriginalExtension();
                    $link = "company/notification/";
                    $file->move($link,$file_name.".".$type);
                    $url = $link.$file_name.".".$type;
                    $dataNotification = [
                        'name' => $name,
                        'description' => $description,
                        'file' => $url,
                        'status' => 0,
                        'update_at' => date('Y-m-d H:i:s'),
                        'company_id' => $idCompany
                    ];
                }else{
                    $error = "invalid file format !!";
                    return response()->json(['error' =>1, 'message'=> $error]);
                }

            }

            try {
                DB::table('company_notifications')->insert(
                    [
                        $dataNotification
                    ]
                );
                return response()->json(['message'=>'Add success notifications company '],200);
            }catch(\Exception $e) {
                return response()->json(["error" => $e->getMessage()],400);
            }
        }

    }

    // delete notification created by company
    public function deleteNotificationCreate(Request $request){
        $idNotificateCreate = $request->idNotificateCreate;
        try {
            DB::table('company_notifications')->where('id',$idNotificateCreate)->delete();
            return response()->json(['message'=>'Delete notification created by company '],200);
        }catch(\Exception $e) {
            return response()->json(["error" => $e->getMessage()],400);
        }
    }

    // get all notification no see
    public function getNotificationNoSee(Request $request){
        $token = $request->token;
        $idAdmin = $this->getIdAccountByToken($token);
        if(!$idAdmin){
            return response()->json(["error" => 'Error get id admin with token'],400);
        }else{
            try {
                $notifications = DB::table('admin_notifications')
                    ->join('system_notifications', 'system_notifications.id', '=', 'admin_notifications.notification_id')
                    ->where('admin_notifications.admin_id',$idAdmin)
                    ->where('admin_notifications.status',0)
                    ->select(
                        'admin_notifications.id as id',
                        'system_notifications.name as name',
                        'admin_notifications.update_at as date'
                    )
                    ->get();
                return response()->json([
                    'message'=>'Lấy thông công chi tiết thông báo chưa xem',
                    'notifications'=>$notifications]
                    ,200);
            }catch(\Exception $e) {
                return response()->json(["error" => $e->getMessage()],400);
            }
        }
    }
}
