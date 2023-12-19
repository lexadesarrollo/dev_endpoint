<?php

namespace App\Http\Controllers;

use App\Models\censo_registered_businesses;
use App\Models\censo_role;
use App\Models\censo_status;
use App\Models\censo_types_business;
use App\Models\censo_user_commissions;
use App\Models\censo_user_device;
use App\Models\censo_users;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use function PHPUnit\Framework\isNull;

class censo_controller extends Controller
{
    protected $connection = 'Censo';
    public function ctl_status()
    {
        $ctl_status = censo_status::all();
        return response()->json([
            'status' => true,
            'message' => 'Successful query.',
            'data' => $ctl_status
        ], 200);
    }

    public function ctl_role()
    {
        $ctl_role = censo_role::all();
        return response()->json([
            'status' => true,
            'message' => 'Successful query.',
            'data' => $ctl_role
        ], 200);
    }

    public function view_ctl_role()
    {
        $view_role = DB::connection('Censo')->select('select * from view_ctl_role');
        return response()->json([
            'status' => true,
            'message' => 'Successful query.',
            'data' => $view_role
        ], 200);
    }

    public function ctl_types_business()
    {
        $ctl_types_business = censo_types_business::all();
        return response()->json([
            'status' => true,
            'message' => 'Successful query.',
            'data' => $ctl_types_business
        ], 200);
    }

    public function view_ctl_types_business()
    {
        $ctl_types_business = DB::connection('Censo')->select('select * from view_types_business');
        return response()->json([
            'status' => true,
            'message' => 'Successful query.',
            'data' => $ctl_types_business
        ], 200);
    }

    public function tbl_registered_businesses()
    {
        $tbl_registered_businesses = censo_registered_businesses::all();
        return response()->json([
            'status' => true,
            'message' => 'Successful query.',
            'data' => $tbl_registered_businesses
        ], 200);
    }

    public function tbl_user_commissions()
    {
        $tbl_user_commissions = censo_user_commissions::all();
        return response()->json([
            'status' => true,
            'message' => 'Successful query.',
            'data' => $tbl_user_commissions
        ], 200);
    }

    public function create_users(Request $request)
    {
        $rules = [
            'name_user' => 'required',
            'last_name' => 'required',
            'mother_last_name' => 'required',
            'id_role'   => 'required'
        ];
        $validator = Validator::make($request->input(), $rules);
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->message()->all()
            ], 200);
        }
        $comb = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
        $pass = array();

        $combLen = strlen($comb) - 1;
        for ($i = 0; $i < 8; $i++) {
            $n = rand(0, $combLen);
            $pass[] = $comb[$n];
        }

        function generar_token_seguro($longitud)
        {
            if ($longitud < 4) {
                $longitud = 4;
            }
            return bin2hex(openssl_random_pseudo_bytes(($longitud - ($longitud % 2)) / 2));
        }

        $password_user = implode($pass);
        $password_token = md5($password_user);
        $token_user = generar_token_seguro(5);

        $validate_user = censo_users::where([
            'name_user' => $request->input('name_user'),
            'last_name' => $request->input('last_name'),
            'mother_last_name' => $request->input('mother_last_name')
        ])->get();
        if (sizeof($validate_user) == 0) {
            $create_user = DB::connection('Censo')->insert('insert into tbl_users (name_user, last_name, mother_last_name, email, phone_number, url_image_user) values (?, ?, ?, ?, ?, ?)', [
                $request->input('name_user'),
                $request->input('last_name'),
                $request->input('mother_last_name'),
                $request->input('email'),
                $request->input('phone_number'),
                $request->input('url_image_user')
            ]);
            if ($create_user) {
                $last_inserted_id = censo_users::latest('id_user')->first();
                $id_user = $last_inserted_id['id_user'];
                $username = $request->input('name_user') . '-' . $token_user;
                $create_credentials = DB::connection('Censo')->insert('insert into tbl_user_credentials (username_user, password_user, id_user, id_role) values (?, ?, ?, ?)', [
                    $username,
                    $password_token,
                    $id_user,
                    $request->input('id_role')
                ]);
                $dataCredentials = [
                    'id_user' => $id_user,
                    'username' => $username,
                    'password' => $password_user
                ];
                if ($create_credentials) {
                    return response()->json([
                        'message' => 'User created successfully.',
                        'data' => $dataCredentials
                    ], 200);
                }
            }
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Already registered user, verify information.',
            ], 200);
        }
    }

    public function create_devices_user(Request $request)
    {
        $rules = [
            'info_devices' => 'required',
            'id_users' => 'required',
        ];
        $validator = Validator::make($request->input(), $rules);
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->message()->all()
            ], 200);
        }
        $device_user_validate = censo_user_device::where([
            'id_users' => $request->input('id_users')
        ])->get();
        if (sizeof($device_user_validate) == 0) {
            $device_user = censo_user_device::create($request->post());
            return response()->json([
                'message' => 'User created successfully.',
                'data' => $device_user
            ], 200);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Already registered device user, verify information.',
            ], 200);
        }
    }

    public function login_users_censo(Request $request)
    {
        $rules = [
            'username' => 'required',
            'password' => 'required'
        ];
        $validator = Validator::make($request->input(), $rules);
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->message()->all()
            ]);
        }
        $password_user = md5($request->input('password'));

        $validate_credentials = DB::connection('Censo')->table('users_censo_global')
            ->where('username_user', $request->input('username'))
            ->where('password_user', $password_user)->get();
        if (sizeof($validate_credentials) == 0) {
            return response()->json([
                'status' => false,
                'message' => 'Incorrect username and password.',
            ], 200);
        } else {
            foreach ($validate_credentials as $data) {
                $id_role = $data->id_role;
                $id_status_user = $data->id_status;
                $id_user = $data->id_user;
                $user_name = $data->username_user;
                $name_user = $data->name_user;
                $last_name = $data->last_name;
                $mother_last_name = $data->mother_last_name;
                $email = $data->email;
                $phone_number = $data->phone_number;
                $url_image_user = $data->url_image_user;
                $created_at = $data->created_at;
                $updated_at = $data->updated_at;
            }
            $data_user = [
                'id_role' => $id_role,
                'id_status_user' => $id_status_user,
                'id_user' => $id_user,
                'user_name' => $user_name,
                'name_user' => $name_user,
                'last_name' => $last_name,
                'mother_last_name' => $mother_last_name,
                'email' => $email,
                'phone_number' => $phone_number,
                'url_image_user' => $url_image_user,
                'created_at' => $created_at,
                'updated_at' => $updated_at
            ];
            if ($id_status_user == 3) {
                return response()->json([
                    'status' => false,
                    'message' => 'Suspended account.',
                ], 200);
            } else {
                return response()->json([
                    'status' => true,
                    'message' => 'Successful validation',
                    'data' => $data_user
                ]);
            }
        }
    }

    public function create_bussnines(Request $request)
    {
        $response = json_decode($request->getContent());
        $id_user = $response->id_users;
        $id_user = intval(str_replace('"', '', $id_user));
        $rules = [
            'business_name' => 'required',
            'business_direction' => 'required',
            'business_address_details' => 'required',
            'latitude_address_register' => 'required',
            'longitud_address_register' => 'required',
            'id_users' => 'required',
            'name_type_businesses' => 'required'
        ];
        $validator = Validator::make($request->input(), $rules);
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->message()->all()
            ]);
        }
        $create_register_bussines = censo_registered_businesses::insert(
            [
                'business_name' => $request->input('business_name'),
                'business_direction' => $request->input('business_direction'),
                'business_address_details' => $request->input('business_address_details'),
                'latitude_address_register' => $request->input('latitude_address_register'),
                'longitud_address_register' => $request->input('longitud_address_register'),
                'id_users' => $id_user,
                'name_type_businesses' => $request->input('name_type_businesses'),
                'url_image_business'  => $request->input('url_picture')
            ]
        );
        if ($create_register_bussines) {
            return response()->json([
                'status' => true,
                'message' => 'The business has been successfully registered.',
            ], 200);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while performing the operation.',
            ], 200);
        }
    }
}
