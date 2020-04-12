<?php

namespace App\Http\Controllers\Api;

use App\Admins;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use DB;
use Illuminate\Support\Facades\Hash;

class CompanyController extends Controller
{
    public function register(Request $request){
        $name = $request->name;
        $signature = $request->signature;
        $ceo = $request->ceo;
        $workforce = $request->workforce;
        $field = $request->field;
        $address = $request->address;
        $contact = $request->contact;

        $record = DB::table('waitings')->where('contact', $contact)->first();

        if($record){
            return response()->json(["error" => "This email contact is used by someone"], 400);
        }

        try{
            $waitings = new \App\Waitings();
            $waitings->name = $name;
            $waitings->signature = $signature;
            $waitings->ceo = $ceo;
            $waitings->workforce = $workforce;
            $waitings->field = $field;
            $waitings->address = $address;
            $waitings->contact = $contact;
            $waitings->save();

            return response()->json(["message" => "Sent to admin of system","company" => $waitings], 200);
        }catch (\Exception $e){
            return response()->json(["error" => "Something was wrong with information company"], 400);
        }
    }

    private function getToken($role,$email, $password){
        $token = null;
        try {
            if (!$token = auth($role)->attempt(['email'=>$email, 'password'=>$password])) {
                return response()->json([
                    'response' => 'error',
                    'message' => 'Password or email is invalid',
                ]);
            }
        } catch (JWTException $e) {
            return response()->json([
                'response' => 'error',
                'message' => 'Token creation failed',
            ]);
        }

        return $token;
    }

    public function loginCompany(Request $request){
        $admin = Admins::where('email', $request->email)->get()->first();
        if ($admin && Hash::check($request->password, $admin->password))
        {
            $token = self::getToken('admins', $request->email, $request->password);
            $admin->auth_token = $token;
            $admin->save();

            $response = [
                'success'=>true,
                'message' => 'Login company successful',
                'system'=> $admin
            ];
        }
        else{
            $response = ['error'=>true, 'message'=>'Record doesnt exists'];
        }

        return response()->json($response, 201);
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logoutCompany()
    {
        auth()->logout();

        return response()->json(['success'=>true,'message' => 'Logged out']);
    }
}
