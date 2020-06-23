<?php

namespace App\Http\Controllers\Api\System;

use App\Systems;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class ManageNotificationController extends Controller
{
    // get idCompany when know token
    public function getIdSystemByToken($token){
        $system = Systems::where('auth_token',$token)->first();
        if(!$system){
            return false;
        }
        return $system->id;
    }
    // new type
    public function addType(Request $request){
        $name = $request->newNameTypeTemplate;
        $description = $request->newDescriptionTypeTemplate;
        $token = $request->token;
        $idSystem = $this->getIdSystemByToken($token);
        if(!$idSystem){
            return response()->json(["error" => 'Error get id system with token '],400);
        }else{
            try {
                DB::table('types')->insert(
                    [
                        'name' => $name,
                        'description' => $description,
                        'system_id' => $idSystem,
                    ]
                );
                return response()->json(['message'=>'Add success new type'],200);
            }catch(\Exception $e) {
                return response()->json(["error" => $e->getMessage()],400);
            }
        }
    }
    // get list type
    public function listType(Request $request){
        try {
            $types = DB::table('types')->get();
            return response()->json(['message'=>'Get list type success','types'=>$types],200);
        }catch(\Exception $e) {
            return response()->json(["error" => $e->getMessage()],400);
        }
    }
    // get list form
    public function listForm(Request $request){
        try {
            $forms = DB::table('forms')
                ->join('templates', 'forms.template_id', '=', 'templates.id')
                ->join('systems', 'forms.system_id', '=', 'systems.id')
                ->select('forms.id as id',
                    'forms.name as name',
                    'forms.description as description',
                    'forms.update_at as date',
                    'templates.name as template_name',
                    'systems.username as system_username')
                ->get();
            return response()->json(['message'=>'Get list type success','forms'=>$forms],200);
        }catch(\Exception $e) {
            return response()->json(["error" => $e->getMessage()],400);
        }
    }

    // get list form
    public function deleteForm(Request $request){
        try {
            $idForm = $request->idForm;
            DB::table('forms')->where('id',$idForm)->delete();
            return response()->json(['message'=>'Delete form success '],200);
        }catch(\Exception $e) {
            return response()->json(["error" => $e->getMessage()],400);
        }
    }

    // get list templates
    public function listAllTemplate(Request $request){
        try {
            $data = array();
            $templates = DB::table('templates')->get();
            foreach ($templates as $template){
                $data[]= array(
                    'id'=>$template->id,
                    'name'=>$template->name,
                    'content'=>json_decode($template->content),
                    'type_id'=>$template->type_id,
                );
            }
            return response()->json(['message'=>'Get list type success','templates'=>$data],200);
        }catch(\Exception $e) {
            return response()->json(["error" => $e->getMessage()],400);
        }
    }

    // get list templates by type
    public function listTemplateType(Request $request){
        $typeId = $request->idType;
        $data = array();
        try {
            $templates = DB::table('templates')->where('type_id',$typeId)->get();
            foreach ($templates as $template){
                $data[]= array(
                    'id'=>$template->id,
                    'name'=>$template->name,
                    'content'=>json_decode($template->content),
                    'type_id'=>$template->type_id,
                );
            }
            return response()->json(['message'=>'Get list type success','templates'=>$data],200);
        }catch(\Exception $e) {
            return response()->json(["error" => $e->getMessage()],400);
        }
    }

    public function addTemplate(Request $request){
        try {
            $name = $request->newNameTemplate;
            $content = \GuzzleHttp\json_encode($request->contentTemplate);
            $typeId = $request->newTypeTemplate;
            DB::table('templates')->insert(
                ['name' => $name,'content'=>$content,'type_id'=>$typeId]
            );
            return response()->json(['message'=>'Add success template'],200);
        }catch(\Exception $e) {
            return response()->json(["error" => $e->getMessage()],400);
        }
    }

    // Add notification
    public function addNotification(Request $request){
        $name = $request->newNameNotification;
        $description = $request->newDescriptionNotification;
        $file = $request->newFileNotification;
        $idForm = $request->newFormNotification;
        if($file === null){
            if($idForm === 0){
                $dataNotification = [
                    'name' => $name,
                    'description' => $description,
                    'status' => 0,
                    'update_at' => date('Y-m-d H:i:s')
                ];
            }else{
                $dataNotification = [
                    'name' => $name,
                    'description' => $description,
                    'status' => 0,
                    'update_at' => date('Y-m-d H:i:s'),
                    'form_id' => $idForm
                ];
            }
        }else{
            if(in_array($file->getClientOriginalExtension(),['pdf','doc','docx','odt','txt'])){
                $file_name = mt_rand();
                $type = $file->getClientOriginalExtension();
                $link = "notification/";
                $file->move($link,$file_name.".".$type);
                $url = $link.$file_name.".".$type;
                if($idForm === 0){
                    $dataNotification = [
                        'name' => $name,
                        'description' => $description,
                        'file' => $url,
                        'status' => 0,
                        'update_at' => date('Y-m-d H:i:s')
                    ];
                }else{
                    $dataNotification = [
                        'name' => $name,
                        'description' => $description,
                        'update_at' => date('Y-m-d H:i:s'),
                        'file' => $url,
                        'status' => 0,
                        'form_id' => $idForm
                    ];
                }

            }else{
                $error = "invalid file format!!";
                return response()->json(['error' =>1, 'message'=> $error]);
            }

        }

        try {
            DB::table('system_notifications')->insert(
                [
                    $dataNotification
                ]
            );
            return response()->json(['message'=>'Add success notifications'],200);
        }catch(\Exception $e) {
            return response()->json(["error" => $e->getMessage()],400);
        }
    }

    // get list notification
    public function getListNotifications(Request $request){
        try {
            $notifications = DB::table('system_notifications')
                ->join('forms', 'system_notifications.form_id', '=', 'forms.id')
                ->join('templates', 'templates.id', '=', 'forms.template_id')
                ->join('systems', 'systems.id', '=', 'forms.system_id')
                ->select('system_notifications.id as id',
                    'system_notifications.name as name',
                    'system_notifications.status as status',
                    'system_notifications.description as description',
                    'system_notifications.update_at as date',
                    'templates.name as template_name')
                ->get();
            return response()->json(['message'=>'Get list notification success','notifications'=>$notifications],200);
        }catch(\Exception $e) {
            return response()->json(["error" => $e->getMessage()],400);
        }
    }

    // get list notification
    public function deleteNotification(Request $request){
        try {
            $idNotificationSystem = $request->idNotificationSystem;
            DB::table('system_notifications')->where('id',$idNotificationSystem)->delete();
            return response()->json(['message'=>'delete success notification'],200);
        }catch(\Exception $e) {
            return response()->json(["error" => $e->getMessage()],400);
        }
    }

    // Create notification user or admin
    public function sendNotificationAdminUser(Request $request){
        try {
            $typeChooses = $request->selectedOptions;
            $idNotification = $request->idNotification;
            foreach ($typeChooses as $typeChoose){
                if($typeChoose['value'] == 1){
                    $idAdmins = DB::table('admins')->get('id');
                    $records = [];
                    foreach ($idAdmins as $idAdmin){
                        $records[] = [
                            'status' => 0,
                            'update_at' => date('Y-m-d H:i:s'),
                            'notification_id' => $idNotification,
                            'admin_id' => $idAdmin->id,
                        ];
                    }
                    DB::table('admin_notifications')->insert(
                        $records
                    );
                }else{
                    $idAccounts = DB::table('accounts')->get('id');
                    $records = [];
                    foreach ($idAccounts as $idAccount){
                        $records[] = [
                            'status' => 0,
                            'update_at' => date('Y-m-d H:i:s'),
                            'notification_id' => $idNotification,
                            'account_id' => $idAccount->id,
                        ];
                    }
                    DB::table('system_user_notifications')->insert(
                        $records
                    );
                }
            }
            try {
                DB::table('system_notifications')
                    ->Where('id', '=', $idNotification)
                    ->update(['status' => 1]);
            }catch (\Exception $e) {
                return response()->json(["error" => $e->getMessage()],400);
            }
            return response()->json(['message'=>'Add success notification user and admin'],200);
        }catch(\Exception $e) {
            return response()->json(["error" => $e->getMessage()],400);
        }
    }

    // get information statistic
    public function getInformationStatistic(Request $request){
        try {
            $idNotification = $request->idNotification;
            $notification = DB::table('system_notifications')->where('id',$idNotification)->first();
            $notificationUser = DB::table('system_user_notifications')->where('notification_id',$idNotification)->count();
            $responseUser = DB::table('user_responses')
                ->join('system_user_notifications', 'user_responses.notification_id', '=', 'system_user_notifications.id')
                ->join('system_notifications', 'system_notifications.id', '=', 'system_user_notifications.notification_id')
                ->where('system_notifications.id',$idNotification)
                ->count();
            $notificationAdmin = DB::table('admin_notifications')->where('notification_id',$idNotification)->count();
            $responseAdmin = DB::table('admin_responses')
                ->join('admin_notifications', 'admin_notifications.id', '=', 'admin_responses.notification_id')
                ->join('system_notifications', 'system_notifications.id', '=', 'admin_notifications.notification_id')
                ->where('system_notifications.id',$idNotification)
                ->count();
            $data = [
                'notificationName'=>$notification->name,
                'notificationUser'=>$notificationUser,
                'notificationAdmin'=>$notificationAdmin,
                'responseUserAdmin'=>$responseUser+$responseAdmin,
            ];
            return response()->json(['message'=>'Get success template','statisticNotification'=>$data],200);
        }catch(\Exception $e) {
            return response()->json(["error" => $e->getMessage()],400);
        }
    }

    // Add notification
    public function addForm(Request $request){
        $name = $request->newNameForm;
        $description = $request->newDescriptionForm;
        $idTemplate = $request->newTemplateForm;
        $token = $request->token;
        $idSystem = $this->getIdSystemByToken($token);
        if(!$idSystem){
            return response()->json(["error" => 'Error get id system with token'],400);
        }else{
            try {
                DB::table('forms')->insert(
                    [
                        'name' => $name,
                        'description' => $description,
                        'update_at' => date('Y-m-d H:i:s'),
                        'template_id' => $idTemplate,
                        'system_id' => $idSystem,
                    ]
                );
                return response()->json(['message'=>'Add success forms'],200);
            }catch(\Exception $e) {
                return response()->json(["error" => $e->getMessage()],400);
            }
        }
    }

    // Get information response in notification system
    public function getInformationResponses (Request $request,$idNotificationFromSystem){
        $resultExcel = array();
        $resultChart = array();
        try {
            $responseCompanies = DB::table('admin_responses')
                ->join('admin_notifications', 'admin_notifications.id', '=', 'admin_responses.notification_id')
                ->join('system_notifications', 'system_notifications.id', '=', 'admin_notifications.notification_id')
                ->join('admins', 'admins.id', '=', 'admin_notifications.admin_id')
                ->join('companies', 'companies.id', '=', 'admins.company_id')
                ->where('system_notifications.id', '=', $idNotificationFromSystem)
                ->select(
                    'admin_responses.content as content',
                    'admins.username as username',
                    'companies.contact as email',
                    'admin_responses.update_at as update_at')
                ->get();
            foreach ($responseCompanies as $responseCompany){
                $dataCompanyResponses = json_decode($responseCompany->content);
                $resultChart[] = json_decode($responseCompany->content);
                $dataCompanyResponses->{"Email"} = $responseCompany->email;
                $dataCompanyResponses->{"Tài khoản công ty"} = $responseCompany->username;
                $dataCompanyResponses->{"Tài khoản nhân viên"} = 'Không';
                $dataCompanyResponses->{"Ngày Gửi"} = $responseCompany->update_at;
                $resultExcel[]=$dataCompanyResponses;
            }
        }catch(\Exception $e) {
            return response()->json(["error" => $e->getMessage()],400);
        }

        try {
            $responseEmployees = DB::table('user_responses')
                ->join('system_user_notifications', 'system_user_notifications.id', '=', 'user_responses.notification_id')
                ->join('system_notifications', 'system_notifications.id', '=', 'system_user_notifications.notification_id')
                ->join('accounts', 'accounts.id', '=', 'system_user_notifications.account_id')
                ->join('employees', 'employees.id', '=', 'accounts.employee_id')
                ->where('system_notifications.id', '=', $idNotificationFromSystem)
                ->select(
                    'user_responses.content',
                    'accounts.username as username',
                    'employees.email as email',
                    'user_responses.update_at as update_at')
                ->get();
            foreach ($responseEmployees as $responseEmployee){
                $dataEmployeeResponses = json_decode($responseEmployee->content);
                $resultChart[] = json_decode($responseEmployee->content);
                $dataEmployeeResponses->{"Email"} = $responseEmployee->email;
                $dataEmployeeResponses->{"Tài khoản công ty"} = 'Không';
                $dataEmployeeResponses->{"Tài khoản nhân viên"} = $responseEmployee->username;
                $dataEmployeeResponses->{"Ngày Gửi"} = $responseEmployee->update_at;
                $resultExcel[]=$dataEmployeeResponses;
            }
            $template = DB::table('system_notifications')
                ->join('forms', 'forms.id', '=', 'system_notifications.form_id')
                ->join('templates', 'templates.id', '=', 'forms.template_id')
                ->where('system_notifications.id', '=', $idNotificationFromSystem)
                ->select(
                    'templates.content as content')
                ->first();
        }catch(\Exception $e) {
            return response()->json(["error" => $e->getMessage()],400);
        }
        return response()->json([
            'message'=>'get success all response of system notification',
            'responseNotificationSystem'=>$resultExcel,
            'responseDataChartNotification'=>$resultChart,
            'templateContentNotification'=>json_decode($template->content)
        ],200);
    }

}
