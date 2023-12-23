<?php

namespace App\Http\Controllers;

use App\Models\apprisa_credentials;
use App\Models\apprisa_tokens;
use App\Models\apprisa_user_credential;
use App\Models\apprisa_users;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

use function PHPSTORM_META\map;

class apprisa_controller extends Controller
{
    // Funciones de creación de usuarios 

    public function create_user(Request $request)
    {
        try {
            $rules = [
                'name' => 'required',
                'last_name' => 'required',
                'mother_last_name' => 'required',
                'email' => 'required',
                'role' => 'required'
            ];
            $validator = Validator::make($request->input(), $rules);
            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => $validator->errors()->all()
                ], 200);
            } else {
                $validate_user = apprisa_users::where('name', $request->name)
                    ->where('last_name', $request->last_name)
                    ->where('mother_last_name', $request->mother_last_name)
                    ->first();

                if ($validate_user == false || $validate_user == null) {
                    $create = apprisa_users::insert([
                        "name" => $request->name,
                        "last_name" => $request->last_name,
                        "mother_last_name" => $request->mother_last_name,
                        "email" => $request->email,
                        "role" => $request->role,
                        "status" => 1
                    ]);
                    if ($create == true) {

                        $credentials = credentials_global::created_credentials($request->name);

                        $data = [
                            'email'    => $request->email,
                            'name_complete' => $request->name . " " . $request->last_name . " " . $request->mother_last_name,
                            'username' => $credentials['user_name'],
                            'password' => $credentials['password']
                        ];

                        send_email_global::$empresa = 'Apprisa';
                        $email = send_email_global::send_email_credentials($data);

                        if ($email["status"] === true) {
                            $new_user = apprisa_users::where('email', $request->email)->first();

                            apprisa_credentials::insert([
                                "user" => $new_user->id_user,
                                "username" => $credentials['user_name'],
                                "password" => $credentials['password_token']
                            ]);

                            return response()->json([
                                'status' => true,
                                'message' => "User created."
                            ], 200);
                        } else {
                            return response()->json([
                                'status' => false,
                                'message' => "An error ocurred."
                            ], 200);
                        }
                    } else {
                        return response()->json([
                            'status' => false,
                            'message' => "An error ocurred."
                        ], 200);
                    }
                } else {
                    return response()->json([
                        'status' => false,
                        'message' => "This user already exist."
                    ], 200);
                }
            }
        } catch (Exception $th) {
            return response()->json([
                'status' => false,
                'message' => "An error ocurred, try again: " . $th
            ], 200);
        }
    }
    /*  Función de autenticación de dos factores */

    public function TwoFA_auth_code(Request $request)
    {
        try {
            $rules = [
                'email' => 'required',
                'password' => 'required'
            ];
            $validator = Validator::make($request->input(), $rules);
            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => $validator->errors()->all()
                ], 200);
            } else {
                $user = apprisa_user_credential::where('email', $request->email)->where('role', 1)->first();
                if ($user != null || $user != false) {
                    $pass = Hash::check($request->password, $user->password);
                    if ($pass != false) {
                        send_email_global::$empresa = "Alba";
                        $data = [
                            "email" => $user->email,
                            "id" => $user->id_user
                        ];
                        $auth = send_email_global::twoFA_email($data);
                        if ($auth["status"] === true) {
                            return response()->json([
                                'status' => true,
                                'message' => "Email sent"
                            ], 200);
                        } else {
                            return response()->json([
                                'status' => false,
                                'message' => "Email cannot sent: " . $auth["message"]
                            ], 200);
                        }
                    } else {
                        return response()->json([
                            'status' => false,
                            'message' => "Password incorrect",
                            'case' => 'password'
                        ], 200);
                    }
                } else {
                    return response()->json([
                        'status' => false,
                        'message' => "Email incorrect",
                        'case' => 'email'
                    ], 200);
                }
            }
        } catch (Exception $th) {
            return response()->json([
                'status' => false,
                'message' => "An error ocurred, try again: " . $th
            ], 200);
        }
    }

    public function autorize_TwoFA(Request $request)
    {
        try {
            $rules = [
                'email' => 'required',
                'code' => 'required'
            ];
            $validator = Validator::make($request->input(), $rules);
            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => $validator->errors()->all()
                ], 200);
            } else {
                $user = apprisa_user_credential::where('email', $request->email)->where('role', 1)->first();
                if ($user != null || $user != false) {
                    $token = apprisa_tokens::where('credential', $user->credential)->first();

                    $isToken = Hash::check($request->code, $token->token);

                    if ($isToken != false) {
                        apprisa_tokens::where('credential', $user->credential)->delete();

                        return response()->json([
                            'status' => true,
                            'message' => "User verificated"
                        ], 200);
                    } else {
                        return response()->json([
                            'status' => false,
                            'message' => "User verificated failed, try again"
                        ], 200);
                    }
                } else {
                    return response()->json([
                        'status' => false,
                        'message' => "User invalid token."
                    ], 200);
                }
            }
        } catch (Exception $th) {
            return response()->json([
                'status' => false,
                'message' => "An error ocurred, try again: " . $th
            ], 200);
        }
    }
}
