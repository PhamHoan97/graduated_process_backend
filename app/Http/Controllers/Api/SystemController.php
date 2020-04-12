<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use App\Systems;

class SystemController extends Controller
{

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

    public function loginSystem(Request $request){
        $system = Systems::where('email', $request->email)->get()->first();
        if ($system && Hash::check($request->password, $system->password))
        {
            $token = self::getToken('systems', $request->email, $request->password);
            $system->auth_token = $token;
            $system->save();

            $response = [
                'success'=>true,
                'message' => 'Login system successful',
                'system'=> $system
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
    public function logoutSystem()
    {
        auth()->logout();

        return response()->json(['success'=>true,'message' => 'Logged out']);
    }

}
