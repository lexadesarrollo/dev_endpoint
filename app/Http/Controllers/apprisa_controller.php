<?php

namespace App\Http\Controllers;

use App\Models\apprisa_credentials;
use App\Models\apprisa_drawing_modes;
use App\Models\apprisa_geofences;
use App\Models\apprisa_geofences_coords;
use App\Models\apprisa_geofences_view;
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
                    $validate_mail = apprisa_users::where('email', $request->email)->first();
                    if ($validate_mail == false || $validate_mail == null) {
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
                            'message' => "This email already exist."
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
                            "id" => $user->credential
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

    public function getAllGeofences()
    {
        try {
            $geofences = apprisa_geofences_view::all();

            $geovalla = [];

            $i = 0;
            while ($i < sizeof($geofences)) {
                $coords = apprisa_geofences_coords::select("latitude", "longitude")
                    ->where('geofence', $geofences[$i]->id_geofence)
                    ->get();

                $radio = apprisa_geofences_coords::select("radio")
                    ->where('geofence', $geofences[$i]->id_geofence)
                    ->get();

                $geovalla[$i] = [
                    "id" => $geofences[$i]->id_geofence,
                    "nombre" => $geofences[$i]->geofence_name,
                    "coordenadas" => $coords,
                    "radio" => $radio[0],
                    "figura" => $geofences[$i]->mode,
                    "color" => $geofences[$i]->geofence_color,
                    "id_status" => $geofences[$i]->id_status,
                    "status" => $geofences[$i]->status
                ];
                $i++;
            }

            return response()->json([
                'status' => true,
                'data' => $geovalla
            ], 200);
        } catch (Exception $th) {
            return response()->json([
                'status' => false,
                'message' => "An error ocurred, try again: " . $th
            ], 200);
        }
    }

    public function getActiveGeofences()
    {
        try {
            $active_geofences = apprisa_geofences_view::where('id_status', 1)->get();

            $geovalla = [];

            $i = 0;
            while ($i < sizeof($active_geofences)) {
                $coords = apprisa_geofences_coords::select("latitude", "longitude")
                    ->where('geofence', $active_geofences[$i]->id_geofence)
                    ->get();

                $radio = apprisa_geofences_coords::select("radio")
                    ->where('geofence', $active_geofences[$i]->id_geofence)
                    ->get();

                $geovalla[$i] = [
                    "id" => $active_geofences[$i]->id_geofence,
                    "nombre" => $active_geofences[$i]->geofence_name,
                    "coordenadas" => $coords,
                    "radio" => $radio[0],
                    "figura" => $active_geofences[$i]->mode,
                    "color" => $active_geofences[$i]->geofence_color
                ];
                $i++;
            }

            return response()->json([
                'status' => true,
                'data' => $geovalla
            ], 200);
        } catch (Exception $th) {
            return response()->json([
                'status' => false,
                'message' => "An error ocurred, try again: " . $th
            ], 200);
        }
    }

    public function create_geofence(Request $request)
    {
        try {
            $rules = [
                'type' => 'required',
                'name' => 'required',
                'lat' => 'required',
                'lng' => 'required',
                'color' => 'required',
            ];
            $validator = Validator::make($request->input(), $rules);
            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => $validator->errors()->all()
                ], 200);
            } else {
                $geofence_validate = apprisa_geofences::where("geofence_name", $request->name)->first();

                if ($geofence_validate == false || $geofence_validate = null) {
                    switch ($request->type) {
                        case 'Circle':
                            $drawing = apprisa_drawing_modes::where('mode', $request->type)->first();
                            $create_geofence = apprisa_geofences::insert([
                                "geofence_name" => $request->name,
                                "geofence_color" => $request->color,
                                "drawing_mode" => $drawing->id_draw,
                                "status" => 1
                            ]);

                            if ($create_geofence == true) {
                                $geofence = apprisa_geofences::where("geofence_name", $request->name)->first();
                                apprisa_geofences_coords::insert([
                                    "latitude" => $request->lat,
                                    "longitude" => $request->lng,
                                    "radio" => $request->radio,
                                    "geofence" => $geofence->id_geofence
                                ]);
                            } else {
                                return response()->json([
                                    'status' => false,
                                    'message' => "An error ocurred, try again."
                                ], 200);
                            }
                            break;

                        case 'Polygon':
                            # code...
                            break;
                    }
                    return response()->json([
                        'status' => true,
                        'message' => "Geofence created sucessfully."
                    ], 200);
                } else {
                    return response()->json([
                        'status' => false,
                        'message' => "This geofence already exist."
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
