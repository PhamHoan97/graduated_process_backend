<?php

namespace App\Http\Controllers\Api;

use App\Fields;
use App\ProcessesFields;
use App\Systems;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Config;

class FieldController extends Controller
{
    public function __construct()
    {
        Config::set('jwt.user', Systems::class);
        Config::set('auth.providers', ['users' => [
            'driver' => 'eloquent',
            'model' => Systems::class,
        ]]);
    }

    public function newField(Request $request){
        $name = $request->name;
        $description = $request->description;
        if(!$name){
            return response()->json(['error' => true, 'message' => "Yêu cầu biến name"]);
        }
        if(!$description){
            return response()->json(['error' => true, 'message' => "Yêu cầu biến description"]);
        }
        try{
            $field = new Fields();
            $field->name = $name;
            $field->description = $description;
            $field->save();
            $fields = Fields::all();
        }catch (\Exception $e){
            return response()->json(['error' => true, 'message' => $e->getMessage()],400);
        }
        return response()->json(['success' => true, 'message' => "Lưu lĩnh vực thành công", "fields" => $fields]);
    }

    public function getAllFields(Request $request){
        try{
            $fields = Fields::all();
        }catch ( \Exception $e){
            return response()->json(['error' => true, 'message' => $e->getMessage()],400);
        }
        return response()->json(['success' => true, 'message' => "got all fields", "fields" => $fields]);
    }

    public function newTemplate(Request $request){
        $name = $request->name;
        $description = $request->description;
        $xml = $request->xml;
        $field_id = $request->field_id;
        if(!$name){
            return response()->json(['error' => true, 'message' => "Yêu cầu biến name"]);
        }
        if(!$description){
            return response()->json(['error' => true, 'message' => "Yêu cầu biến description"]);
        }
        if(!$xml){
            return response()->json(['error' => true, 'message' => "Yêu cầu biến xml"]);
        }
        if(!$field_id){
            return response()->json(['error' => true, 'message' => "Yêu cầu biến field_id"]);
        }
        try{
            $template = new ProcessesFields();
            $template->name = $name;
            $template->description = $description;
            $template->xml = $xml;
            $template->field_id = $field_id;
            $template->save();
        }catch (\Exception $e){
            return response()->json(['error' => true, 'message' => $e->getMessage()],400);
        }
        return response()->json(['success' => true, 'message' => "Lưu mẫu quy trình thành công", "process" => $template]);
    }

    public function getProcessTemplateOfField(Request $request){
        $idProcess = $request->idProcess;
        if(!$idProcess){
            return response()->json(['error' => true, 'message' => "idProcess is required"]);
        }
        try{
            $process = ProcessesFields::find($idProcess);
        }catch (\Exception $e){
            return response()->json(['error' => true, 'message' => $e->getMessage()],400);
        }
        return response()->json(['success' => true, 'message' => "got process template by id", "process" => $process]);
    }

    public function editTemplate(Request $request){
        $id = $request->id;
        $name = $request->name;
        $description = $request->description;
        $xml = $request->xml;
        if(!$id){
            return response()->json(['error' => true, 'message' => "id is required"]);
        }
        if(!$name){
            return response()->json(['error' => true, 'message' => "name is required"]);
        }
        if(!$description){
            return response()->json(['error' => true, 'message' => "description is required"]);
        }
        if(!$xml){
            return response()->json(['error' => true, 'message' => "xml is required"]);
        }
        try{
            $template = ProcessesFields::find($id);
            $template->name = $name;
            $template->description = $description;
            $template->xml = $xml;
            $template->update();
        }catch (\Exception $e){
            return response()->json(['error' => true, 'message' => $e->getMessage()],400);
        }
        return response()->json(['success' => true, 'message' => "edited process template", "process" => $template]);
    }

    public function getAllTemplateOfField(Request $request){
        $idField = $request->idField;
        if(!$idField){
            return response()->json(['error' => true, 'message' => "idField is required"]);
        }
        try{
            $field = Fields::find($idField);
            $templates = ProcessesFields::where('field_id', $idField)->get();
        }catch (\Exception $e){
            return response()->json(['error' => true, 'message' => $e->getMessage()],400);
        }
        return response()->json([
            'success' => true,
            'message' => "edited process template",
            'processes' => $templates,
            'field' => $field,
        ]);
    }

    public function deleteTemplate(Request $request){
        $idProcess = $request->idProcess;
        $idField = $request->idField;
        if(!$idProcess){
            return response()->json(['error' => true, 'message' => "idProcess is required"]);
        }
        if(!$idField){
            return response()->json(['error' => true, 'message' => "idField is required"]);
        }
        try{
            $template = ProcessesFields::find($idProcess)->delete();
            $templates = ProcessesFields::where('field_id', $idField)->get();
        }catch (\Exception $e){
            return response()->json(['error' => true, 'message' => $e->getMessage()],400);
        }
        return response()->json([
            'success' => true,
            'message' => "deleted process template",
            'processes' => $templates,
        ]);
    }

    public function updateField(Request $request){
        $id = $request->id;
        $name = $request->name;
        $description = $request->description;
        if(!$id){
            return response()->json(['error' => true, 'message' => "Yêu cầu biến id"]);
        }
        if(!$name){
            return response()->json(['error' => true, 'message' => "Yêu cầu biến name"]);
        }
        if(!$description){
            return response()->json(['error' => true, 'message' => "Yêu cầu biến description"]);
        }
        try{
            $field = Fields::find($id);
            $field->name = $name;
            $field->description = $description;
            $field->update();

            $fields = Fields::all();
        }catch (\Exception $e){
            return response()->json(['error' => true, 'message' => $e->getMessage()],400);
        }
        return response()->json(['success' => true, 'message' => "Cập nhật lĩnh vực thành công" , "fields"=> $fields]);
    }
}
