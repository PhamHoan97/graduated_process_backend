<?php

namespace App\Http\Controllers\Api;

use App\Admins;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class TemplateController extends Controller
{
    public function uploadTemplatesForProcess(Request $request){
        $tọken = $request->token;
        if(!isset($tọken)){
            return response()->json(['error' => 1, 'message' => "Xảy ra lỗi với token"], 201);
        }
        if(!$request->hasFile('file')) {
            return response()->json(['error' => 1, 'message' => "Không tồn tại file"], 201);
        }
        try{
            $admin = Admins::where('auth_token', $tọken)->first();
            if(!$admin){
                return response()->json(['error' => 1, 'message' => "Xảy ra lỗi với xác thực người dùng"], 201);
            }
            $file = $request->file('file');
            $photo_name = mt_rand();
            $type = $file->getClientOriginalExtension();
            $link = "template/";
            $file->move($link,$photo_name.".".$type);
            $url = $link.$photo_name.".".$type;
        }catch (\Exception $e){
            return response()->json(['error' => 1, 'message' => $e->getMessage()], 400);
        }
        return response()->json(['success' => 1, 'message' => "Upload biểu mẫu thành công", "link" => $url], 200);
    }
}
