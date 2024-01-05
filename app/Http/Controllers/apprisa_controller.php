<?php

namespace App\Http\Controllers;

use App\Models\apprisa_categories_view;
use App\Models\apprisa_category;
use App\Models\apprisa_comissions;
use App\Models\apprisa_comissions_view;
use App\Models\apprisa_credentials;
use App\Models\apprisa_documentation;
use App\Models\apprisa_documentation_view;
use App\Models\apprisa_drawing;
use App\Models\apprisa_drawing_modes;
use App\Models\apprisa_drawing_modes_view;
use App\Models\apprisa_geofences;
use App\Models\apprisa_geofences_coords;
use App\Models\apprisa_geofences_view;
use App\Models\apprisa_lada;
use App\Models\apprisa_ladas_view;
use App\Models\apprisa_municipality;
use App\Models\apprisa_municipality_view;
use App\Models\apprisa_permissions;
use App\Models\apprisa_role;
use App\Models\apprisa_roles_view;
use App\Models\apprisa_states;
use App\Models\apprisa_states_view;
use App\Models\apprisa_status;
use App\Models\apprisa_tokens;
use App\Models\apprisa_type_person;
use App\Models\apprisa_type_person_view;
use App\Models\apprisa_type_vehicle;
use App\Models\apprisa_type_vehicle_view;
use App\Models\apprisa_user_credential;
use App\Models\apprisa_users;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

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

    /****************************** Funciones de catálogos ***********************************/

    public function all_categories()
    {
        try {
            $categories = apprisa_categories_view::all();

            return response()->json([
                'status' => true,
                'data' => $categories
            ], 200);
        } catch (Exception $th) {
            return response()->json([
                'status' => false,
                'message' => "An error ocurred, try again."
            ], 200);
        }
    }

    public function create_category(Request $request)
    {
        try {
            $rules = [
                'icon' => 'required',
                'category' => 'required'
            ];
            $validator = Validator::make($request->input(), $rules);
            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => $validator->errors()->all()
                ], 200);
            } else {
                $validate_category = apprisa_category::where('category', $request->category)->first();
                if ($validate_category == false || $validate_category == null) {
                    apprisa_category::insert([
                        'icon_category' => $request->icon,
                        'category' => ucwords(strtolower($request->category))
                    ]);

                    return response()->json([
                        'status' => true,
                        'message' => "Category has been created"
                    ], 200);
                } else {
                    return response()->json([
                        'status' => false,
                        'message' => "Ya existe la categoría."
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

    public function status_category(Request $request)
    {
        try {
            $rules = [
                "category" => "required",
            ];
            $validator = Validator::make($request->input(), $rules);
            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => $validator->errors()->all()
                ], 200);
            } else {
                $category = apprisa_category::where('id_category', $request->category)->first();
                if ($category != null || $category == "") {
                    switch ($category->status) {
                        case 1:
                            apprisa_category::where('id_category', $request->category)->update([
                                "status" => 2
                            ]);

                            return response()->json([
                                'status' => true,
                                'message' => "Se ha inhabilitado la categoría " . $category->category
                            ], 200);
                            break;

                        case 2:
                            apprisa_category::where('id_category', $request->category)->update([
                                "status" => 1
                            ]);

                            return response()->json([
                                'status' => true,
                                'message' => "Se ha habilitado la categoría " . $category->category
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

    public function all_ladas()
    {
        try {
            $ladas = apprisa_ladas_view::all();

            return response()->json([
                'status' => true,
                'data' => $ladas
            ], 200);
        } catch (Exception $th) {
            return response()->json([
                'status' => false,
                'message' => "An error ocurred, try again."
            ], 200);
        }
    }

    public function create_lada(Request $request)
    {
        try {
            $rules = [
                'lada' => 'required',
                'country' => 'required'
            ];
            $validator = Validator::make($request->input(), $rules);
            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => $validator->errors()->all()
                ], 200);
            } else {
                $validate_lada = apprisa_lada::where('lada', $request->lada)->orwhere('country', $request->country)->first();
                if ($validate_lada == false || $validate_lada == null) {
                    apprisa_lada::insert([
                        'lada' => $request->lada,
                        'country' => ucwords(strtolower($request->country))
                    ]);

                    return response()->json([
                        'status' => true,
                        'message' => "Lada has been created"
                    ], 200);
                } else {
                    return response()->json([
                        'status' => false,
                        'message' => "Ya existe esta lada o país."
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

    public function status_lada(Request $request)
    {
        try {
            $rules = [
                "lada" => "required",
            ];
            $validator = Validator::make($request->input(), $rules);
            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => $validator->errors()->all()
                ], 200);
            } else {
                $lada = apprisa_lada::where('id_lada', $request->lada)->first();
                if ($lada != null || $lada == "") {
                    switch ($lada->status) {
                        case 1:
                            apprisa_lada::where('id_lada', $request->lada)->update([
                                "status" => 2
                            ]);

                            return response()->json([
                                'status' => true,
                                'message' => "Se ha inhabilitado la lada de " . $lada->country
                            ], 200);
                            break;

                        case 2:
                            apprisa_lada::where('id_lada', $request->lada)->update([
                                "status" => 1
                            ]);

                            return response()->json([
                                'status' => true,
                                'message' => "Se ha habilitado la lada de " . $lada->country
                            ], 200);
                            break;
                    }
                }
            }
        } catch (Exception $th) {
            return response()->json([
                'status' => false,
                'message' => "An error ocurred, try again. "
            ], 200);
        }
    }

    public function all_type_documentation()
    {
        try {
            $documentation = apprisa_documentation_view::all();

            return response()->json([
                'status' => true,
                'data' => $documentation
            ], 200);
        } catch (Exception $th) {
            return response()->json([
                'status' => false,
                'message' => "An error ocurred, try again."
            ], 200);
        }
    }

    public function create_type_documentation(Request $request)
    {
        try {
            $rules = [
                'documentation' => 'required'
            ];
            $validator = Validator::make($request->input(), $rules);
            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => $validator->errors()->all()
                ], 200);
            } else {
                $validate_documentation = apprisa_documentation::where('type_documentation', $request->documentation)->first();
                if ($validate_documentation == false || $validate_documentation == null) {
                    apprisa_documentation::insert([
                        'type_documentation' => ucwords(strtolower($request->documentation))
                    ]);

                    return response()->json([
                        'status' => true,
                        'message' => "Documentation has been created"
                    ], 200);
                } else {
                    return response()->json([
                        'status' => false,
                        'message' => "Ya existe el tipo de documento."
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

    public function status_documentation(Request $request)
    {
        try {
            $rules = [
                "documentation" => "required",
            ];
            $validator = Validator::make($request->input(), $rules);
            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => $validator->errors()->all()
                ], 200);
            } else {
                $documentation = apprisa_documentation::where('id_documentation', $request->documentation)->first();
                if ($documentation != null || $documentation == "") {
                    switch ($documentation->status) {
                        case 1:
                            apprisa_documentation::where('id_documentation', $request->documentation)->update([
                                "status" => 2
                            ]);

                            return response()->json([
                                'status' => true,
                                'message' => "Se ha inhabilitado el tipo de documento " . $documentation->type_documentation
                            ], 200);
                            break;

                        case 2:
                            apprisa_documentation::where('id_documentation', $request->documentation)->update([
                                "status" => 1
                            ]);

                            return response()->json([
                                'status' => true,
                                'message' => "Se ha habilitado el tipo de documento " . $documentation->type_documentation
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


    public function all_drawing_modes()
    {
        try {
            $drawing_modes = apprisa_drawing_modes_view::all();

            return response()->json([
                'status' => true,
                'data' => $drawing_modes
            ], 200);
        } catch (Exception $th) {
            return response()->json([
                'status' => false,
                'message' => "An error ocurred, try again."
            ], 200);
        }
    }

    public function create_draw(Request $request)
    {
        try {
            $rules = [
                'draw' => 'required'
            ];
            $validator = Validator::make($request->input(), $rules);
            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => $validator->errors()->all()
                ], 200);
            } else {
                $validate_documentation = apprisa_drawing::where('mode', $request->draw)->first();
                if ($validate_documentation == false || $validate_documentation == null) {
                    apprisa_drawing::insert([
                        'mode' => ucwords(strtolower($request->draw))
                    ]);

                    return response()->json([
                        'status' => true,
                        'message' => "Draw mode has been created"
                    ], 200);
                } else {
                    return response()->json([
                        'status' => false,
                        'message' => "Ya existe el tipo de dibujo."
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

    public function status_draw(Request $request)
    {
        try {
            $rules = [
                "draw" => "required",
            ];
            $validator = Validator::make($request->input(), $rules);
            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => $validator->errors()->all()
                ], 200);
            } else {
                $draw = apprisa_drawing::where('id_draw', $request->draw)->first();
                if ($draw != null || $draw == "") {
                    switch ($draw->status) {
                        case 1:
                            apprisa_drawing::where('id_draw', $request->draw)->update([
                                "status" => 2
                            ]);

                            return response()->json([
                                'status' => true,
                                'message' => "Se ha inhabilitado el tipo de dibujo " . $draw->mode
                            ], 200);
                            break;

                        case 2:
                            apprisa_drawing::where('id_draw', $request->draw)->update([
                                "status" => 1
                            ]);

                            return response()->json([
                                'status' => true,
                                'message' => "Se ha habilitado el tipo de dibujo " . $draw->mode
                            ], 200);
                            break;
                    }
                }
            }
        } catch (Exception $th) {
            return response()->json([
                'status' => false,
                'message' => "An error ocurred, try again. "
            ], 200);
        }
    }

    public function all_municipality()
    {
        try {
            $municipality = apprisa_municipality_view::all();

            return response()->json([
                'status' => true,
                'data' => $municipality
            ], 200);
        } catch (Exception $th) {
            return response()->json([
                'status' => false,
                'message' => "An error ocurred, try again."
            ], 200);
        }
    }

    public function create_municipality(Request $request)
    {
        try {
            $rules = [
                'municipality' => 'required',
                'state' => 'required'
            ];
            $validator = Validator::make($request->input(), $rules);
            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => $validator->errors()->all()
                ], 200);
            } else {
                $validate_municipality = apprisa_municipality::where('municipality', $request->municipality)->first();
                if ($validate_municipality == false || $validate_municipality == null) {
                    apprisa_municipality::insert([
                        'municipality' => ucwords(strtolower($request->municipality)),
                        'state' => $request->state
                    ]);

                    return response()->json([
                        'status' => true,
                        'message' => "Municipality has been created"
                    ], 200);
                } else {
                    return response()->json([
                        'status' => false,
                        'message' => "Ya existe el municipio."
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

    public function status_municipality(Request $request)
    {
        try {
            $rules = [
                "municipality" => "required",
            ];
            $validator = Validator::make($request->input(), $rules);
            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => $validator->errors()->all()
                ], 200);
            } else {
                $municipality = apprisa_municipality::where('id_municipality', $request->municipality)->first();
                if ($municipality != null || $municipality == "") {
                    switch ($municipality->status) {
                        case 1:
                            apprisa_municipality::where('id_municipality', $request->municipality)->update([
                                "status" => 2
                            ]);

                            return response()->json([
                                'status' => true,
                                'message' => "Se ha inhabilitado el municipio " . $municipality->municipality
                            ], 200);
                            break;

                        case 2:
                            apprisa_municipality::where('id_municipality', $request->municipality)->update([
                                "status" => 1
                            ]);

                            return response()->json([
                                'status' => true,
                                'message' => "Se ha habilitado el municipio " . $municipality->municipality
                            ], 200);
                            break;
                    }
                }
            }
        } catch (Exception $th) {
            return response()->json([
                'status' => false,
                'message' => "An error ocurred, try again. "
            ], 200);
        }
    }

    public function all_states()
    {
        try {
            $states = apprisa_states_view::all();

            return response()->json([
                'status' => true,
                'data' => $states
            ], 200);
        } catch (Exception $th) {
            return response()->json([
                'status' => false,
                'message' => "An error ocurred, try again."
            ], 200);
        }
    }

    public function create_state(Request $request)
    {
        try {
            $rules = [
                'state' => 'required',
            ];
            $validator = Validator::make($request->input(), $rules);
            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => $validator->errors()->all()
                ], 200);
            } else {
                $validate_state = apprisa_states::where('states', $request->state)->first();
                if ($validate_state == false || $validate_state == null) {
                    apprisa_states::insert([
                        'states' => ucwords(strtolower($request->state)),
                    ]);

                    return response()->json([
                        'status' => true,
                        'message' => "state has been created"
                    ], 200);
                } else {
                    return response()->json([
                        'status' => false,
                        'message' => "Ya existe el estado."
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

    public function status_state(Request $request)
    {
        try {
            $rules = [
                "state" => "required",
            ];
            $validator = Validator::make($request->input(), $rules);
            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => $validator->errors()->all()
                ], 200);
            } else {
                $state = apprisa_states::where('id_states', $request->state)->first();
                if ($state != null || $state == "") {
                    switch ($state->status) {
                        case 1:
                            apprisa_states::where('id_states', $request->state)->update([
                                "status" => 2
                            ]);

                            return response()->json([
                                'status' => true,
                                'message' => "Se ha inhabilitado el estado " . $state->states
                            ], 200);
                            break;

                        case 2:
                            apprisa_states::where('id_states', $request->state)->update([
                                "status" => 1
                            ]);

                            return response()->json([
                                'status' => true,
                                'message' => "Se ha habilitado el estado " . $state->states
                            ], 200);
                            break;
                    }
                }
            }
        } catch (Exception $th) {
            return response()->json([
                'status' => false,
                'message' => "An error ocurred, try again. "
            ], 200);
        }
    }

    public function all_permissions()
    {
        try {
            $permissions = apprisa_permissions::all();

            return response()->json([
                'status' => true,
                'data' => $permissions
            ], 200);
        } catch (Exception $th) {
            return response()->json([
                'status' => false,
                'message' => "An error ocurred, try again."
            ], 200);
        }
    }

    public function create_permission(Request $request)
    {
        try {
            $rules = [
                'permission' => 'required',
            ];
            $validator = Validator::make($request->input(), $rules);
            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => $validator->errors()->all()
                ], 200);
            } else {
                $validate_permissions = apprisa_permissions::where('permission', $request->permissions)->first();
                if ($validate_permissions == false || $validate_permissions == null) {
                    apprisa_permissions::insert([
                        'permission' => ucwords(strtolower($request->permission)),
                    ]);

                    return response()->json([
                        'status' => true,
                        'message' => "Permission has been created"
                    ], 200);
                } else {
                    return response()->json([
                        'status' => false,
                        'message' => "Ya existe el permiso."
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

    public function all_roles()
    {
        try {
            $roles = apprisa_roles_view::all();

            return response()->json([
                'status' => true,
                'data' => $roles
            ], 200);
        } catch (Exception $th) {
            return response()->json([
                'status' => false,
                'message' => "An error ocurred, try again."
            ], 200);
        }
    }

    public function create_role(Request $request)
    {
        try {
            $rules = [
                'rol' => 'required',
                'permission' => 'required'
            ];
            $validator = Validator::make($request->input(), $rules);
            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => $validator->errors()->all()
                ], 200);
            } else {
                $validate_roles = apprisa_role::where('role', $request->rol)->first();
                if ($validate_roles == false || $validate_roles == null) {
                    apprisa_role::insert([
                        'role' => ucwords(strtolower($request->rol)),
                        'permission' => $request->permission
                    ]);

                    return response()->json([
                        'status' => true,
                        'message' => "role has been created"
                    ], 200);
                } else {
                    return response()->json([
                        'status' => false,
                        'message' => "Ya existe el rol."
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

    public function all_comissions()
    {
        try {
            $comissions = apprisa_comissions_view::all();

            return response()->json([
                'status' => true,
                'data' => $comissions
            ], 200);
        } catch (Exception $th) {
            return response()->json([
                'status' => false,
                'message' => "An error ocurred, try again."
            ], 200);
        }
    }

    public function create_comission(Request $request)
    {
        try {
            $rules = [
                'comission' => 'required',
                'amount' => 'required'
            ];
            $validator = Validator::make($request->input(), $rules);
            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => $validator->errors()->all()
                ], 200);
            } else {
                $validate_comissions = apprisa_comissions::where('service_commission', $request->comission)->first();
                if ($validate_comissions == false || $validate_comissions == null) {
                    apprisa_comissions::insert([
                        'service_commission' => strtoupper($request->comission),
                        'commission' => $request->amount
                    ]);

                    return response()->json([
                        'status' => true,
                        'message' => "commissions has been created"
                    ], 200);
                } else {
                    return response()->json([
                        'status' => false,
                        'message' => "Ya existe la comisión."
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

    public function status_comission(Request $request)
    {
        try {
            $rules = [
                "comission" => "required",
            ];
            $validator = Validator::make($request->input(), $rules);
            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => $validator->errors()->all()
                ], 200);
            } else {
                $comissions = apprisa_comissions::where('id_service_commission', $request->comission)->first();
                if ($comissions != null || $comissions == "") {
                    switch ($comissions->status) {
                        case 1:
                            apprisa_comissions::where('id_service_commission', $request->comission)->update([
                                "status" => 2
                            ]);

                            return response()->json([
                                'status' => true,
                                'message' => "Se ha inhabilitado la comisión " . $comissions->service_commission
                            ], 200);
                            break;

                        case 2:
                            apprisa_comissions::where('id_service_commission', $request->comission)->update([
                                "status" => 1
                            ]);

                            return response()->json([
                                'status' => true,
                                'message' => "Se ha habilitado la comisión " . $comissions->service_commission
                            ], 200);
                            break;
                    }
                }
            }
        } catch (Exception $th) {
            return response()->json([
                'status' => false,
                'message' => "An error ocurred, try again. "
            ], 200);
        }
    }

    public function all_status()
    {
        try {
            $status = apprisa_status::all();

            return response()->json([
                'status' => true,
                'data' => $status
            ], 200);
        } catch (Exception $th) {
            return response()->json([
                'status' => false,
                'message' => "An error ocurred, try again."
            ], 200);
        }
    }

    public function create_status(Request $request)
    {
        try {
            $rules = [
                'status' => 'required',
            ];
            $validator = Validator::make($request->input(), $rules);
            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => $validator->errors()->all()
                ], 200);
            } else {
                $validate_status = apprisa_status::where('status', $request->status)->first();
                if ($validate_status == false || $validate_status == null) {
                    apprisa_status::insert([
                        'status' => ucwords(strtolower($request->status)),
                    ]);

                    return response()->json([
                        'status' => true,
                        'message' => "Status has been created"
                    ], 200);
                } else {
                    return response()->json([
                        'status' => false,
                        'message' => "Ya existe el status."
                    ], 200);
                }
            }
        } catch (Exception $th) {
            return response()->json([
                'status' => false,
                'message' => "An error ocurred, try again." . $th
            ], 200);
        }
    }

    public function all_type_person()
    {
        try {
            $type_person = apprisa_type_person_view::all();

            return response()->json([
                'status' => true,
                'data' => $type_person
            ], 200);
        } catch (Exception $th) {
            return response()->json([
                'status' => false,
                'message' => "An error ocurred, try again."
            ], 200);
        }
    }

    public function create_type_person(Request $request)
    {
        try {
            $rules = [
                'type_person' => 'required',
            ];
            $validator = Validator::make($request->input(), $rules);
            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => $validator->errors()->all()
                ], 200);
            } else {
                $validate_type_person = apprisa_type_person::where('type_person', $request->type_person)->first();
                if ($validate_type_person == false || $validate_type_person == null) {
                    apprisa_type_person::insert([
                        'type_person' => ucwords(strtolower($request->type_person)),
                    ]);

                    return response()->json([
                        'status' => true,
                        'message' => "type person has been created"
                    ], 200);
                } else {
                    return response()->json([
                        'status' => false,
                        'message' => "Ya existe el tipo de persona."
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

    public function status_type_person(Request $request)
    {
        try {
            $rules = [
                "type_person" => "required",
            ];
            $validator = Validator::make($request->input(), $rules);
            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => $validator->errors()->all()
                ], 200);
            } else {
                $type_person = apprisa_type_person::where('id_type_person', $request->type_person)->first();
                if ($type_person != null || $type_person == "") {
                    switch ($type_person->status) {
                        case 1:
                            apprisa_type_person::where('id_type_person', $request->type_person)->update([
                                "status" => 2
                            ]);

                            return response()->json([
                                'status' => true,
                                'message' => "Se ha inhabilitado el tipo de persona " . $type_person->type_person
                            ], 200);
                            break;

                        case 2:
                            apprisa_type_person::where('id_type_person', $request->type_person)->update([
                                "status" => 1
                            ]);

                            return response()->json([
                                'status' => true,
                                'message' => "Se ha habilitado el tipo de persona " . $type_person->type_person
                            ], 200);
                            break;
                    }
                }
            }
        } catch (Exception $th) {
            return response()->json([
                'status' => false,
                'message' => "An error ocurred, try again. "
            ], 200);
        }
    }

    public function all_type_vehicle()
    {
        try {
            $type_vehicle = apprisa_type_vehicle_view::all();

            return response()->json([
                'status' => true,
                'data' => $type_vehicle
            ], 200);
        } catch (Exception $th) {
            return response()->json([
                'status' => false,
                'message' => "An error ocurred, try again."
            ], 200);
        }
    }

    public function create_type_vehicle(Request $request)
    {
        try {
            $rules = [
                'type_vehicle' => 'required',
            ];
            $validator = Validator::make($request->input(), $rules);
            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => $validator->errors()->all()
                ], 200);
            } else {
                $validate_type_vehicle = apprisa_type_vehicle::where('vehicle', $request->type_vehicle)->first();
                if ($validate_type_vehicle == false || $validate_type_vehicle == null) {
                    apprisa_type_vehicle::insert([
                        'vehicle' => ucwords(strtolower($request->type_vehicle)),
                    ]);

                    return response()->json([
                        'status' => true,
                        'message' => "type vehicle has been created"
                    ], 200);
                } else {
                    return response()->json([
                        'status' => false,
                        'message' => "Ya existe el tipo de vehículo."
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

    public function status_type_vehicle(Request $request)
    {
        try {
            $rules = [
                "type_vehicle" => "required",
            ];
            $validator = Validator::make($request->input(), $rules);
            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => $validator->errors()->all()
                ], 200);
            } else {
                $type_vehicle = apprisa_type_vehicle::where('id_type_vehicle', $request->type_vehicle)->first();
                if ($type_vehicle != null || $type_vehicle == "") {
                    switch ($type_vehicle->status) {
                        case 1:
                            apprisa_type_vehicle::where('id_type_vehicle', $request->type_vehicle)->update([
                                "status" => 2
                            ]);

                            return response()->json([
                                'status' => true,
                                'message' => "Se ha inhabilitado el tipo de vehículo " . $type_vehicle->vehicle
                            ], 200);
                            break;

                        case 2:
                            apprisa_type_vehicle::where('id_type_vehicle', $request->type_vehicle)->update([
                                "status" => 1
                            ]);

                            return response()->json([
                                'status' => true,
                                'message' => "Se ha habilitado el tipo de vehículo " . $type_vehicle->type_vehicle
                            ], 200);
                            break;
                    }
                }
            }
        } catch (Exception $th) {
            return response()->json([
                'status' => false,
                'message' => "An error ocurred, try again. "
            ], 200);
        }
    }
}
