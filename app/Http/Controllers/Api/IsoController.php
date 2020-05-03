<?php

namespace App\Http\Controllers\Api;

use App\Isos;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class IsoController extends Controller
{
    public function createIso(Request $request){
        $name = $request->name;
        $year = $request->year;
        $content = $request->isoContent;
        try{
            $iso = new Isos();
            $iso->name = $name;
            $iso->year = $year;
            $iso->content = $content;
            if($request->hasFile('document')){
                $file = $request->file('document');
                if(in_array($file->getClientOriginalExtension(),['pdf','doc','docx','odt','txt'])){
                    $photo_name = mt_rand();
                    $type = $file->getClientOriginalExtension();
                    $link = "file/";
                    $file->move($link,$photo_name.".".$type);
                    $url = $link.$photo_name.".".$type;
                    $iso->name_download = $photo_name.".".$type;
                    $iso->download = $url;
                }else{
                    $error = "invalid file format!!";
                    return response()->json(['error' =>1, 'message'=> $error]);
                }
            }
            $iso->save();
        }catch (\Exception $e){
            return response()->json(['error' =>1, 'message'=> $e->getMessage()]);
        }
        return response()->json(['success' => true, 'message' => 'created standard']);
    }

    public function getIsos(Request $request){
        try{
            $isos = Isos::all();
        }catch (\Exception $e){
            return response()->json(['error' =>1, 'message'=> $e->getMessage()]);
        }
        return response()->json(['success' => true, 'message' => 'get standards', 'isos' => $isos]);
    }

    public function downloadDocumentIso(Request $request){
        $name = $request->name;
        if(!isset($name)){
            return response()->json(['error' => 1, 'message' => "name is required"], 400);
        }
        $url = './file/'.$name;
        try{
            if(!is_file($url)){
                return response()->json(['error' => 1, 'message' => "file does not exist"], 400);
            }
            $headers = ['Content-Type: application/octet-stream'];
        }catch (\Exception $e){
            return response()->json(['error' => 1, 'message' => $e->getMessage()], 400);
        }
        ob_clean();
        return response()->download($url,$name,$headers);
    }

    public function deleteIso(Request $request){
        $id = $request->id;
        if(!isset($id)){
            return response()->json(['error' => 1, 'message' => "id is required"], 400);
        }else{
            try{
                $iso = Isos::find($id);
                $iso->delete();
                $isos = Isos::all();
                return response()->json(['success' => true, 'message' => 'delete standard', 'isos' => $isos]);
            }catch (\Exception $e){
                return response()->json(['error' => 1, 'message' => $e->getMessage()], 400);
            }
        }
    }
}
