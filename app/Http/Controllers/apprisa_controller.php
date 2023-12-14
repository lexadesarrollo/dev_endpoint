<?php

namespace App\Http\Controllers;

use App\Http\Resources\RestauranteCollection;
use App\Http\Resources\UsuarioCollection;
use App\Models\GeocercasModel;
use App\Models\RestauranteModel;
use App\Models\StatusModel;
use App\Models\UsuarioModel;
use App\Models\ZonasModel;
use Illuminate\Http\Request;
use DateTime;
use Exception;
use Illuminate\Support\Facades\Hash;

class apprisa_controller extends Controller
{
    /*  Funciones Status */
    public function createStatus(Request $request)
    {
        $boolean = false;
        $response = ["status" => 500, "msg" => "Internal Server Error"];

        if (!$request->missing("status")) {

            $data = json_decode($request->getContent());

            if ($data->status !== "") {

                StatusModel::insert([
                    'status' => $data->status
                ]);

                $boolean = true;
                $response["status"] = 201;
                $response["msg"] = "Status successfuly created.";
            } else {
                $response["status"] = 400;
                $response["msg"] = "Error, null field invalid.";
            }
        } else {
            $response["status"] = 500;
        }

        return [$response, $boolean];
    }


    /*  Funciones Usuario */

    public function getUser(Request $request)
    {
        $boolean = false;
        $response = ["status" => 0, "msg" => ""];

        try {
            if (!$request->missing("id", "pass")) {
                $data = json_decode($request->getContent());

                $sql = UsuarioModel::all()
                    ->where('id_usuario', $data->id)
                    ->where('password', $data->pass);

                $collection = new UsuarioCollection($sql);

                $boolean = true;
                $response["status"] = 200;
                $response["msg"] = "Filter query completed.";
            } else {
                $sql = UsuarioModel::all();

                $collection = new UsuarioCollection($sql);

                $boolean = true;
                $response["status"] = 200;
                $response["msg"] = "Query completed.";
            }
            return [$collection, $response, $boolean];
        } catch (Exception $e) {
            return response()->json([200, "An error ocurred during query: " . $e]);
        }
    }

    public function createUser(Request $request)
    {
        setlocale(LC_ALL, 'es_ES');
        date_default_timezone_set('America/Mexico_City');

        $boolean = false;
        $response = ["status" => 500, "msg" => "Internal Server Error"];

        if (!$request->missing("name", "lastname", "birth_date", "email", "phone", "role")) {

            $data = json_decode($request->getContent());
            $fecha  = new DateTime();

            if ($request) {

                try {
                    UsuarioModel::insert([
                        "nombre" => $data->name,
                        "apellidos" => $data->lastname,
                        "fecha_nacimiento" => $data->birth_date,
                        "email" => $data->email,
                        "password" => Hash::make($data->password),
                        "numero_telefono" => $data->phone,
                        "rol" => $data->role,
                        "status" => 1
                    ]);

                    $apellidos = explode(' ', $data->lastname);


                    $apP = $apellidos[0];

                    $apM = $apellidos[1];


                    // SocioComanditarioModel::insert([
                    //     "name" => $data->name,
                    //     "last_name" => $apP,
                    //     "mother_last_name" => $apM,
                    //     "birth_date" => $data->birth_date,
                    //     "street" => $data->street,
                    //     "sex" => $data->sex,
                    //     "outdoor_number"  => $data->outdoorNumber,
                    //     "number"  => $data->number,
                    //     "cologne"  => $data->cologne,
                    //     "locality"  => $data->locality,
                    //     "municipality"  => $data->municipality,
                    //     "state" => $data->state,
                    //     "cp" => $data->cp,
                    //     "reference_1" => $data->reference1,
                    //     "reference_2" => $data->reference2,
                    //     "curp" => $data->curp,
                    //     "rfc" => $data->rfc,
                    //     "marital_status" => $data->maritalStatus,
                    //     "education_level" => $data->educationLevel,
                    //     "industry" => $data->industry,
                    //     "nationality" => $data->nationality,
                    //     "lada_number" => $data->ladaNumber,
                    //     "telephone_number" => $data->phone,
                    //     "email" => $data->email,
                    //     "bank" => $data->bank,
                    //     "account_clabe_cb" => $data->accountClabe,
                    //     "date_register" => $fecha->format("Y-m-d"),
                    //     "status" => 4
                    // ]);

                    $boolean = true;
                    $response["status"] = 201;
                    $response["msg"] = "User successfuly created.";
                } catch (Exception $e) {
                    $response["status"] = 400;
                    $response["msg"] = "Error, data type invalid for rows; Int or Date type require.";
                }
            } else {
                $response["status"] = 400;
                $response["msg"] = "Error, null fields invalid.";
            }
        } else {
            $response["status"] = 500;
        }

        return [$response, $boolean];
    }

     /*  Funciones Restaurante */

     public function getRestaurante(Request $request)
    {
        $boolean = false;
        $response = ["status" => 500, "msg" => "Internal Server Error."];

        try {
            if (!$request->missing("id")) {

                $data = json_decode($request->getContent());

                $sql = RestauranteModel::all()
                    ->where('id_restaurante', $data->id);

                $collection = new RestauranteCollection($sql);

                $boolean = true;
                $response["status"] = 200;
                $response["msg"] = "Filter query completed.";

            } else {

                $sql = RestauranteModel::all();

                $collection = new RestauranteCollection($sql);

                $boolean = true;
                $response["status"] = 200;
                $response["msg"] = "Query completed.";

            }

            return [$collection, $response, $boolean];
        } catch (Exception $e) {
            return response()->json(["status"=>500, "An error ocurred during query: " . $e]);
        }
    }


    /* Funciones geocercas */

    public function viewGeofences()
    {
        $boolean = false;
        $response = ["status" => 500, "msg" => "Internal Server Error"];
        try {

            $geofences = [];

            $nombre = GeocercasModel::select("nombre_geocerca")
                ->where('status', 8)
                ->distinct()
                ->get();

            $i = 0;
            $id = 1;
            while ($i < sizeof($nombre)) {
                $coords = GeocercasModel::select("latitud", "longitud")
                    ->where('nombre_geocerca', $nombre[$i]->nombre_geocerca)
                    ->get();

                $type = GeocercasModel::select("figura")
                    ->where('nombre_geocerca', $nombre[$i]->nombre_geocerca)
                    ->get();

                $radio = GeocercasModel::select("radio", "color")
                    ->where('nombre_geocerca', $nombre[$i]->nombre_geocerca)
                    ->get();

                $geofences[$i] = [
                    "id" => $id,
                    "nombre" => $nombre[$i]->nombre_geocerca,
                    "coordenadas" => $coords,
                    "radio" => $radio[0]->radio,
                    "figura" => $type[0]->figura,
                    "color" => $radio[0]->color
                ];
                $i++;
                $id++;
            }

            $boolean = true;
            $response["status"] = 200;
            $response["msg"] = "Query completed";

            return $geofences;
        } catch (Exception $th) {
            $boolean = false;
            $response["status"] = 500;
            $response["msg"] = "No hay datos.";
            return [$boolean, $response, $th];
        }
    }

    public function createGeofence(Request $request)
    {
        $boolean = false;
        $response = ["status" => 500, "msg" => "Internal Server Error"];

        if ($request->missing("radio")) {

            $data = json_decode($request->getContent());

            try {
                if ($data->name !== "" && $data->lat !== "" && $data->lng !== "") {

                    GeocercasModel::insert([
                        'nombre_geocerca' => $data->name,
                        'latitud' => $data->lat,
                        'longitud' => $data->lng,
                        'status' => 1,
                        'figura' => $data->figura,
                        'color' => $data->color
                    ]);

                    $boolean = true;
                    $response["status"] = 201;
                    $response["msg"] = "Geofence successfuly created.";
                } else {
                    $response["msg"] = "Empty fiels not supported.";
                }
                return [$boolean, $response];
            } catch (Exception $th) {
                $datos = json_decode($request->getContent());
                return [$boolean, $response, $datos->radio];
            }
        } else if (!$request->missing("radio")) {

            $data = json_decode($request->getContent());

            try {
                if ($data->name !== "" && $data->lat !== "" && $data->lng !== "") {

                    GeocercasModel::insert([
                        'nombre_geocerca' => $data->name,
                        'latitud' => $data->lat,
                        'longitud' => $data->lng,
                        'status' => 8,
                        'figura' => $data->figura,
                        'radio' => $data->radio,
                        'color' => $data->color
                    ]);

                    $boolean = true;
                    $response["status"] = 201;
                    $response["msg"] = "Geofence successfuly created.";
                } else {
                    $response["msg"] = "Empty fiels not supported.";
                }
                return [$boolean, $response];
            } catch (Exception $th) {
                return [$boolean, $response];
            }
        }
        $response["msg"] = "Fiels 'name', 'lat', 'lng' are missing.";
        return [$boolean, $response];
    }

    public function createZone(Request $request)
    {
        $boolean = false;
        $response = ["status" => 500, "msg" => "Internal Server Error"];

        if (!$request->missing('geofence', 'name', 'lat', 'lng')) {
            $data = json_decode($request->getContent());

            try {
                if ($data->name !== "") {

                    ZonasModel::insert([
                        'geocerca' => $data->geofence,
                        'nombre_zona' => $data->name,
                        'latitud' => $data->lat,
                        'longitud' => $data->lng,
                        'figura' => $data->figura
                    ]);

                    $boolean = true;
                    $response["status"] = 201;
                    $response["msg"] = "Zone successfuly created.";
                } else {
                    $response["msg"] = "Empty fiels not supported.";
                }

                return [$boolean, $response];
            } catch (Exception $th) {
                return [$boolean, $response];
            }
        }
    }

    public function viewAllGeofences()
    {
        $boolean = false;
        $response = ["status" => 500, "msg" => "Internal Server Error"];
        try {

            $geofencesAll = [];

            $nombre = GeocercasModel::select("nombre_geocerca")
                ->distinct()
                ->get();

            $i = 0;
            $id = 1;
            while ($i < sizeof($nombre)) {
                $coords = GeocercasModel::select("latitud", "longitud")
                    ->where('nombre_geocerca', $nombre[$i]->nombre_geocerca)
                    ->get();

                $type = GeocercasModel::select("figura")
                    ->where('nombre_geocerca', $nombre[$i]->nombre_geocerca)
                    ->get();

                $radio = GeocercasModel::select("radio", "color", "status")
                    ->where('nombre_geocerca', $nombre[$i]->nombre_geocerca)
                    ->get();

                    if ($radio[0]->status == 8) {
                        $status = "Activo";
                    }else if ($radio[0]->status == 9){
                        $status = "Deshabilitado";
                    }

                $geofencesAll[$i] = [
                    "id" => $id,
                    "nombre" => $nombre[$i]->nombre_geocerca,
                    "coordenadas" => $coords,
                    "radio" => $radio[0]->radio,
                    "figura" => $type[0]->figura,
                    "color" => $radio[0]->color,
                    "status" => $status
                ];
                $i++;
                $id++;
            }

            $boolean = true;
            $response["status"] = 200;
            $response["msg"] = "Query completed";

            return $geofencesAll;
        } catch (Exception $th) {
            $boolean = false;
            $response["status"] = 500;
            $response["msg"] = "No hay datos.";
            return [$boolean, $response, $th];
        }
    }

    public function updateGeocercas(Request $request)
    {
        $boolean = false;
        $response = ["status" => 500, "msg" => "Internal Server Error"];

        if (!$request->missing('name')) {
            try {
            if ($request["name"] !== "") {

                $status = GeocercasModel::select('status')
                    ->where('nombre_geocerca', $request["name"])->first();

                switch ($status->status) {
                    case 8:
                        GeocercasModel::where('nombre_geocerca', $request["name"])
                            ->update(['status' => '9']);
                        $response["msg"] = "Geocerca successfuly disabled.";
                        break;

                    case 9:
                        GeocercasModel::where('nombre_geocerca', $request["name"])
                            ->update(['status' => '8']);

                        $response["msg"] = "Geocerca successfuly enabled.";
                        break;
                }

                $boolean = true;
                $response["status"] = 201;
            } else {
                $response["msg"] = "Empty fiels not supported.";
            }

            return [$boolean, $response];
            } catch (Exception $th) {
                return [$boolean, $response,];
            }
        }
        $response["msg"] = "Fiels 'name' are missing.";
        return [$boolean, $response];
    }

}
