<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class credentials_global extends Controller
{

    private static function generar_token_seguro($longitud)
        {
            if ($longitud < 4) {
                $longitud = 4;
            }
            return bin2hex(openssl_random_pseudo_bytes(($longitud - ($longitud % 2)) / 2));
        }

    public static function created_credentials($name_user)
    {
        try {

            $token_user = self::generar_token_seguro(5);
            $name = $name_user;
            $user_name_v1 = strtr($name, " ", "-");
            $user_name_v2 = strtolower($user_name_v1);
            $user_name = $user_name_v2 . '-' . $token_user;

            $comb = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
            $pass = array();

            $combLen = strlen($comb) - 1;
            for ($i = 0; $i < 8; $i++) {
                $n = rand(0, $combLen);
                $pass[] = $comb[$n];
            }

            $password_user = implode($pass);
            $password_token = Hash::make($password_user);

            return [
                'user_name' => $user_name,
                'password' =>  $password_user,
                'password_token' => $password_token
            ];
        } catch (Exception $cb) {
            $response = ([
                'status' => false,
                'message' =>  'An error ocurred during query: ' . $cb
            ]);
        }
        echo json_encode($response);
    }
}
