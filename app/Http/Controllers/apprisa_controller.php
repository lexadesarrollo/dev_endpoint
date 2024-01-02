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
                            "name" => ucwords(strtolower($request->name)),
                            "last_name" => ucwords(strtolower($request->last_name)),
                            "mother_last_name" => ucwords(strtolower($request->mother_last_name)),
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

    public function getAllAdmins()
    {
        try {
            $users = apprisa_user_credential::where('id_role', 1)->get();

            if ($users) {
                return response()->json([
                    'status' => true,
                    'data' => $users
                ], 200);
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
                $user = apprisa_user_credential::where('email', $request->email)->where('id_role', 1)->where('id_status', 1)->first();
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
                $user = apprisa_user_credential::where('email', $request->email)->where('id_role', 1)->first();
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

    /*  Función de geocercas */

    public function getAllGeofences()
    {
        try {
            $active_geofences_circle = apprisa_geofences_view::where('mode', "Circle")->get();
            $active_geofences_polygon = apprisa_geofences_view::select('id_geofence')->distinct()->where('mode', "Polygon")->get();

            $circles = [];
            $polygons = [];

            $i = 0;
            while ($i < sizeof($active_geofences_circle)) {
                $coords = apprisa_geofences_coords::select("latitude", "longitude")
                    ->where('geofence', $active_geofences_circle[$i]->id_geofence)
                    ->get();

                $radio = apprisa_geofences_coords::select("radio")
                    ->where('geofence', $active_geofences_circle[$i]->id_geofence)
                    ->get();

                $circles[$i] = [
                    "id" => $active_geofences_circle[$i]->id_geofence,
                    "nombre" => $active_geofences_circle[$i]->geofence_name,
                    "coordenadas" => $coords,
                    "radio" => $radio[0]->radio,
                    "figura" => $active_geofences_circle[$i]->mode,
                    "color" => $active_geofences_circle[$i]->geofence_color,
                    "status" => $active_geofences_circle[$i]->status
                ];
                $i++;
            }

            $a = 0;
            while ($a < sizeof($active_geofences_polygon)) {
                $coords = apprisa_geofences_coords::select("latitude", "longitude")
                    ->where('geofence', $active_geofences_polygon[$a]->id_geofence)
                    ->get();

                $radio = apprisa_geofences_coords::select("radio")
                    ->where('geofence', $active_geofences_polygon[$a]->id_geofence)
                    ->get();

                $geo =  apprisa_geofences_view::where('id_geofence', $active_geofences_polygon[$a]->id_geofence)->get();

                $polygons[$a] = [
                    "id" => $geo[$a]->id_geofence,
                    "nombre" => $geo[$a]->geofence_name,
                    "coordenadas" => $coords,
                    "figura" => $geo[$a]->mode,
                    "color" => $geo[$a]->geofence_color,
                    "status" => $geo[$a]->status
                ];
                $a++;
            }

            return response()->json([
                'status' => true,
                'circles' => $circles,
                'polygons' => $polygons
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
            $active_geofences_circle = apprisa_geofences_view::where('id_status', 1)->where('mode', "Circle")->get();
            $active_geofences_polygon = apprisa_geofences_view::select('id_geofence')->distinct()->where('id_status', 1)->where('mode', "Polygon")->get();

            $circles = [];
            $polygons = [];

            $i = 0;
            while ($i < sizeof($active_geofences_circle)) {
                $coords = apprisa_geofences_coords::select("latitude", "longitude")
                    ->where('geofence', $active_geofences_circle[$i]->id_geofence)
                    ->get();

                $radio = apprisa_geofences_coords::select("radio")
                    ->where('geofence', $active_geofences_circle[$i]->id_geofence)
                    ->get();

                $circles[$i] = [
                    "id" => $active_geofences_circle[$i]->id_geofence,
                    "nombre" => $active_geofences_circle[$i]->geofence_name,
                    "coordenadas" => $coords,
                    "radio" => $radio[0]->radio,
                    "figura" => $active_geofences_circle[$i]->mode,
                    "color" => $active_geofences_circle[$i]->geofence_color
                ];
                $i++;
            }

            $a = 0;
            while ($a < sizeof($active_geofences_polygon)) {
                $coords = apprisa_geofences_coords::select("latitude", "longitude")
                    ->where('geofence', $active_geofences_polygon[$a]->id_geofence)
                    ->get();

                $radio = apprisa_geofences_coords::select("radio")
                    ->where('geofence', $active_geofences_polygon[$a]->id_geofence)
                    ->get();

                $geo =  apprisa_geofences_view::where('id_status', 1)->where('id_geofence', $active_geofences_polygon[$a]->id_geofence)->get();

                $polygons[$a] = [
                    "id" => $geo[$a]->id_geofence,
                    "nombre" => $geo[$a]->geofence_name,
                    "coordenadas" => $coords,
                    "figura" => $geo[$a]->mode,
                    "color" => $geo[$a]->geofence_color
                ];
                $a++;
            }

            return response()->json([
                'status' => true,
                'circles' => $circles,
                'polygons' => $polygons
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
                            $drawing = apprisa_drawing_modes::where('mode', $request->type)->first();
                            $create_geofence = apprisa_geofences::insert([
                                "geofence_name" => $request->name,
                                "geofence_color" => $request->color,
                                "drawing_mode" => $drawing->id_draw,
                                "status" => 1
                            ]);

                            if ($create_geofence == true) {
                                $geofence = apprisa_geofences::where("geofence_name", $request->name)->first();

                                $i = 0;
                                while ($i < sizeof($request->lat)) {
                                    apprisa_geofences_coords::insert([
                                        "latitude" => $request->lat[$i],
                                        "longitude" => $request->lng[$i],
                                        "geofence" => $geofence->id_geofence
                                    ]);

                                    $i++;
                                }
                            } else {
                                return response()->json([
                                    'status' => false,
                                    'message' => "An error ocurred, try again."
                                ], 200);
                            }
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
                'message' => "An error ocurred, try again"
            ], 200);
        }
    }

    public function status_geofence(Request $request)
    {
        try {
            $rules = [
                "geofence" => "required",
            ];
            $validator = Validator::make($request->input(), $rules);
            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => $validator->errors()->all()
                ], 200);
            } else {
                $geofence = apprisa_geofences::where('id_geofence', $request->geofence)->first();
                if ($geofence != null || $geofence == "") {
                    switch ($geofence->status) {
                        case 1:
                            apprisa_geofences::where('id_geofence', $request->geofence)->update([
                                "status" => 2
                            ]);

                            return response()->json([
                                'status' => true,
                                'message' => "Se ha inhabilitado la geocerca " . $geofence->geofence_name
                            ], 200);
                            break;

                        case 2:
                            apprisa_geofences::where('id_geofence', $request->geofence)->update([
                                "status" => 1
                            ]);

                            return response()->json([
                                'status' => true,
                                'message' => "Se ha habilitado la geocerca " . $geofence->geofence_name
                            ], 200);
                            break;
                    }
                } else {
                    return response()->json([
                        'status' => false,
                        'message' => "This geofence don't exist."
                    ], 200);
                }
            }
        } catch (Exception $th) {
            return response()->json([
                'status' => false,
                'message' => "An error ocurred, try again."
            ], 200);
        }
    }

    /*  Función de habilitar/deshabilitar cuentas */

    public function status_user(Request $request)
    {
        try {
            $rules = [
                "user" => "required",
            ];
            $validator = Validator::make($request->input(), $rules);
            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => $validator->errors()->all()
                ], 200);
            } else {
                $user = apprisa_users::where('id_user', $request->user)->first();
                if ($user != null || $user == "") {
                    switch ($user->status) {
                        case 1:
                            apprisa_users::where('id_user', $request->user)->update([
                                "status" => 2
                            ]);

                            return response()->json([
                                'status' => true,
                                'message' => "Se ha inhabilitado al usuario " . $user->name
                            ], 200);
                            break;

                        case 2:
                            apprisa_users::where('id_user', $request->user)->update([
                                "status" => 1
                            ]);

                            return response()->json([
                                'status' => true,
                                'message' => "Se ha habilitado al usuario " . $user->name
                            ], 200);
                            break;
                    }
                }
            }
        } catch (Exception $th) {
            return response()->json([
                'status' => false,
                'message' => "An error ocurred, try again."
            ], 200);
        }
    }
}
