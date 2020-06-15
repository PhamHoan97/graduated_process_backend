<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ElementController extends Controller
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
}
