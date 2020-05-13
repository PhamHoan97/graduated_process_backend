<?php

namespace App\Http\Controllers\Api\System;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class ManageNotificationController extends Controller
{
    // new type
    public function addType(Request $request){
        $name = $request->newNameTypeTemplate;
        $description = $request->newDescriptionTypeTemplate;
        $idAdmin = $request->idAdmin;
        try {
            DB::table('types')->insert(
                [
                    'name' => $name,
                    'description' => $description,
                    'admin_id' => $idAdmin,
                ]
            );
            return response()->json(['message'=>'Add success new type'],200);
        }catch(\Exception $e) {
            return response()->json(["error" => $e->getMessage()],400);
        }
    }
    // get list type
    public function listType(Request $request){
        try {
            $idAdmin = $request->idAdmin;
            $types = DB::table('types')->where('admin_id',$idAdmin)->get();
            return response()->json(['message'=>'Get list type success','types'=>$types],200);
        }catch(\Exception $e) {
            return response()->json(["error" => $e->getMessage()],400);
        }
    }
    // get list form
    public function listForm(Request $request){
        try {
            $idAdmin = $request->idAdmin;
            $forms = DB::table('forms')
                ->join('templates', 'forms.template_id', '=', 'templates.id')
                ->join('admins', 'forms.admin_id', '=', 'admins.id')
                ->where('forms.admin_id',$idAdmin)
                ->select('forms.id as id',
                    'forms.name as name',
                    'forms.description as description',
                    'forms.update_at as date',
                    'templates.name as template_name',
                    'admins.username as admin_username')
                ->get();
            return response()->json(['message'=>'Get list type success','forms'=>$forms],200);
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
            DB::table('notifications')->insert(
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
            $idCompany = $request->idCompany;
            $notifications = DB::table('notifications')
                ->join('forms', 'notifications.form_id', '=', 'forms.id')
                ->join('templates', 'templates.id', '=', 'forms.template_id')
                ->join('admins', 'admins.id', '=', 'forms.admin_id')
                ->join('companies', 'companies.id', '=', 'admins.company_id')
                ->where('companies.id',$idCompany)
                ->select('notifications.id as id',
                    'notifications.name as name',
                    'notifications.status as status',
                    'notifications.description as description',
                    'notifications.update_at as date',
                    'templates.name as template_name')
                ->get();
            return response()->json(['message'=>'Get list notification success','notifications'=>$notifications],200);
        }catch(\Exception $e) {
            return response()->json(["error" => $e->getMessage()],400);
        }
    }

    // Create notification user or admin
    public function addNotificationUser(Request $request){
        try {
            $idNotification = $request->idNotification;
            try {
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
                DB::table('user_notifications')->insert(
                    $records
                );
                DB::table('notifications')
                    ->Where('id', '=', $idNotification)
                    ->update(['status' => 1]);
            }catch (\Exception $e) {
                return response()->json(["error" => $e->getMessage()],400);
            }
            return response()->json(['message'=>'Add success notification user'],200);
        }catch(\Exception $e) {
            return response()->json(["error" => $e->getMessage()],400);
        }
    }

    // get information statistic
    public function getInformationStatistic(Request $request){
        try {
            $data = array();
            $idNotification = $request->idNotification;
            $notification = DB::table('notifications')->where('id',$idNotification)->first();
            $notificationUser = DB::table('user_notifications')->where('notification_id',$idNotification)->count();
            $responseUser = DB::table('user_notifications')
                ->where('status',1)
                ->where('notification_id',$idNotification)
                ->count();
            $data = [
                'notificationName'=>$notification->name,
                'notificationUser'=>$notificationUser,
                'responseUser'=>$responseUser,
            ];
            return response()->json(['message'=>'Get success template','statisticNotification'=>$data],200);
        }catch(\Exception $e) {
            return response()->json(["error" => $e->getMessage()],400);
        }
    }

    public function listAdminNotification(Request $request){
        $idAdmin = $request->idAdmin;
        try {
            $notificationAdmins = DB::table('admin_notifications')
                ->join('notifications', 'notifications.id', '=', 'admin_notifications.notification_id')
                ->join('templates', 'templates.id', '=', 'notifications.template_id')
                ->where('admin_notifications.admin_id',$idAdmin)
                ->select('admin_notifications.id as id',
                    'notifications.name as name',
                    'admin_notifications.status as status',
                    'admin_notifications.update_at as date',
                    'templates.name as template_name')
                ->get();
            return response()->json(['message'=>'Get list type success','notificationAdmins'=>$notificationAdmins],200);
        }catch(\Exception $e) {
            return response()->json(["error" => $e->getMessage()],400);
        }
    }

    public function getInformationTemplateNotification(Request $request){
        $idNotificationAdmin = $request->idNotificationAdmin;
        try {
            $notificationAdmin = DB::table('admin_notifications')
                ->join('notifications', 'notifications.id', '=', 'admin_notifications.notification_id')
                ->join('templates', 'templates.id', '=', 'notifications.template_id')
                ->where('admin_notifications.id',$idNotificationAdmin)
                ->select('admin_notifications.id as id',
                    'admin_notifications.update_at as date',
                    'notifications.name as name',
                    'notifications.description as description',
                    'templates.content as template_content')
                ->first();
            $data[]= array(
                'id'=>$notificationAdmin->id,
                'date'=>$notificationAdmin->date,
                'name'=>$notificationAdmin->name,
                'description'=>$notificationAdmin->description,
                'template_content'=>json_decode($notificationAdmin->template_content),
            );
            return response()->json(['message'=>'Get response admin notification success','notificationAdmin'=>$data],200);
        }catch(\Exception $e) {
            return response()->json(["error" => $e->getMessage()],400);
        }
    }

    // add response admin notification
    public function addResponseAdmin (Request $request){
        try {
            $date = date('Y-m-d H:i:s');
            $idAdmin = $request->idAdmin;
            $idNotificationAdmin = $request->idNotificationAdmin;
            $content = \GuzzleHttp\json_encode($request->contentResponse);
            DB::table('admin_responses')->insert(
                ['content' => $content,'update_at'=>$date,'admin_id'=>$idAdmin,'notification_id'=>$idNotificationAdmin]
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

    // Add notification
    public function addForm(Request $request){
        $name = $request->newNameForm;
        $description = $request->newDescriptionForm;
        $idTemplate = $request->newTemplateForm;
        $idAdmin = $request->idAdmin;
        try {
            DB::table('forms')->insert(
                [
                    'name' => $name,
                    'description' => $description,
                    'update_at' => date('Y-m-d H:i:s'),
                    'template_id' => $idTemplate,
                    'admin_id' => $idAdmin,
                ]
            );
            return response()->json(['message'=>'Add success forms'],200);
        }catch(\Exception $e) {
            return response()->json(["error" => $e->getMessage()],400);
        }
    }
}
