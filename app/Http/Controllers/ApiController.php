<?php

namespace App\Http\Controllers;

use App\Models\UsuarioModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ApiController extends Controller
{
    public function login(Request $request)
    {
        $response = ["status" => 500, "msg" => "Internal Server Error."];

        if ($request !== null) {

            $data = json_decode($request->getContent());

            $user = UsuarioModel::where('correo_electronico', $data->correo)->first();

            if ($user) {

                if ($data->pass = $user->password) {

                    $token = $user->createToken("EndPoint_Api");
                    $response["status"] = 200;
                    $response["msg"] = "Bearer token: " + $token->plainTextToken;

                } else {

                    $response["msg"] = "Password or email are incorrect.";

                }
            } else {

                $response["msg"] = "User don't exist.";

            }
        } else {

            $response["msg"] = "An error ocurred while triyng to get params.";

        }

        return $response;

    }
}
