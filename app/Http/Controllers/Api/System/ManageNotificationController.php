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
        $idSystem = $request->idSystem;
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
            $responseUser = DB::table('system_user_notifications')
                ->where('status',1)
                ->where('notification_id',$idNotification)
                ->count();
            $notificationAdmin = DB::table('admin_notifications')->where('notification_id',$idNotification)->count();
            $responseAdmin = DB::table('admin_notifications')
                ->where('status',1)
                ->where('notification_id',$idNotification)
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

    public function listAdminNotification(Request $request){
        $idAdmin = $request->idAdmin;
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
                    'forms.description as name_form',
                    'templates.content as template_content')
                ->first();
            $data[]= array(
                'id'=>$notificationAdmin->id,
                'date'=>$notificationAdmin->date,
                'name'=>$notificationAdmin->name,
                'file'=>$notificationAdmin->name,
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
        $idSystem = $request->idSystem;
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

    // get list all notifications which company created
    public function getListCreateNotifications(Request $request){
        try {
            $idCompany = $request->idCompany;
            $notifications = DB::table('company_notifications')->where('company_id',$idCompany)->get();
            return response()->json(['message'=>'Get list notification create company success','notifications'=>$notifications],200);
        }catch(\Exception $e) {
            return response()->json(["error" => $e->getMessage()],400);
        }
    }


    // Create notification company
    public function addNotificationCompany(Request $request){
        $name = $request->newNameNotification;
        $description = $request->newDescriptionNotification;
        $file = $request->newFileNotification;
        $idCompany = $request->idCompany;
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

    // get all account employee
    public function getAllAccountEmployee(Request $request,$idCompany){
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

    // get all notifications of employees from system
    public function listEmployeeNotificationSystem(Request $request){
        $idAccount = $request->idAccount;
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
    // get all notifications of employees from company
    public function listEmployeeNotificationCompany(Request $request){
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
                'file'=>$notificationEmployee->name,
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
        try {
            $date = date('Y-m-d H:i:s');
            $idAccount = $request->idAccount;
            $idNotificationSystemEmployee = $request->idNotificationSystemEmployee;
            $content = \GuzzleHttp\json_encode($request->contentResponse);
            DB::table('user_responses')->insert(
                [
                    'content' => $content,
                    'update_at'=>$date,
                    'account_id'=>$idAccount,
                    'notification_id'=>$idNotificationSystemEmployee
                ]
            );
            // update status in admin_notifications
            try {
                DB::table('system_user_notifications')
                    ->Where('id', '=', $idNotificationSystemEmployee)
                    ->update(['status' => 1]);
            }catch (\Exception $e) {
                return response()->json(["error" => $e->getMessage()],400);
            }
            return response()->json(['message'=>'Add success admin responses'],200);
        }catch(\Exception $e) {
            return response()->json(["error" => $e->getMessage()],400);
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
}
