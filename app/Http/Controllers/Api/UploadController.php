<?php

namespace App\Http\Controllers\Api;

use App\Admins;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class UploadController extends Controller
{
    public function uploadDocumentForElement(Request $request){
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
            $link = "element/";
            $file->move($link,$photo_name.".".$type);
            $url = $link.$photo_name.".".$type;
        }catch (\Exception $e){
            return response()->json(['error' => 1, 'message' => $e->getMessage()], 400);
        }
        return response()->json(['success' => 1, 'message' => "Upload tài liệu thành công", "url" => $url], 200);
    }

    public function uploadDocumentForProcess(Request $request){
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
            $link = "process/";
            $file->move($link,$photo_name.".".$type);
            $url = $link.$photo_name.".".$type;
        }catch (\Exception $e){
            return response()->json(['error' => 1, 'message' => $e->getMessage()], 400);
        }
        return response()->json(['success' => 1, 'message' => "Upload tài liệu thành công", "url" => $url], 200);
    }
}
