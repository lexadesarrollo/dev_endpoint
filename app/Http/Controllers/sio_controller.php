<?php

namespace App\Http\Controllers;

use App\Models\sio_bank;
use App\Models\sio_cia;
use App\Models\sio_employees;
use App\Models\sio_origin_accounts;
use App\Models\sio_partners;
use App\Models\sio_payment_receipts;
use App\Models\sio_role;
use App\Models\sio_status;
use App\Models\sio_type_file;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class sio_controller extends Controller
{

    //Funciones catalogo status
    public function ctl_status_all()
    {
        $ctl_status = sio_status::all();
        return response()->json([
            'status' => true,
            'data' => $ctl_status
        ], 200);
    }

    public function created_status(Request $request)
    {
        $rules = [
            'descrip_status' => 'required'
        ];
        $validator = Validator::make($request->input(), $rules);
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()->all()
            ], 400);
        }
        $status_validate = sio_status::where(['descrip_status' => $request->descrip_status])->get();
        if (sizeof($status_validate) == 0) {
            try {
                sio_status::insert([
                    'descrip_status' => $request->descrip_status
                ]);

                return response()->json([
                    'status' => true,
                    'message' => 'Status created successfully'
                ], 200);
            } catch (Exception $cb) {
                return response()->json([
                    'status' => false,
                    'message' =>  'An error ocurred during query: ' . $cb
                ], 200);
            }
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Already registered status',
            ], 200);
        }
    }

    public function updated_status(Request $request)
    {
        $data = json_decode($request->getContent());
        $rules = [
            'id_status' => 'required',
            'descrip_status' => 'required'
        ];
        $validator = Validator::make($request->input(), $rules);
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()->all()
            ], 400);
        }
        try {
            DB::connection('DevSio')->update('exec update_status ?,?', [
                $data->id_status,
                $data->descrip_status
            ]);
            return response()->json([
                'status' => true,
                'message' => 'Status updated successfully'
            ], 200);
        } catch (Exception $cb) {
            return response()->json([
                'status' => false,
                'message' =>  'An error ocurred during query: ' . $cb
            ], 200);
        }
    }

    public function detail_status($id_status)
    {
        if (!$id_status) {
            return response()->json([
                'status' => false,
                'message' => 'No results found',
            ], 200);
        } else {
            $status = sio_status::where('id_status', $id_status)->first();
            if ($status == false) {
                return response()->json([
                    'status' => false,
                    'message' => 'No results found',
                ], 200);
            } else {
                return $status;
            }
        }
    }


    //---------------------------------

    //------------------Funciones catalogo archivos admitidos por el sistema--------------------

    public function ctl_type_file()
    {
        $ctl_type_files = DB::connection('DevSio')->select('select * from type_file_view');
        return response()->json([
            'status' => true,
            'data' => $ctl_type_files
        ], 200);
    }

    public function detail_type_file(Request $request)
    {
        if (!$request) {
            return response()->json([
                'status' => false,
                'message' => 'No results found',
            ], 200);
        } else {
            $type_file = DB::connection('DevSio')->table('type_file_view')->where('id_type_file', $request->id_type_file)->first();
            if ($type_file == false) {
                return response()->json([
                    'status' => false,
                    'message' => 'No results found',
                ], 200);
            } else {
                return $type_file;
            }
        }
    }

    public function created_type_file(Request $request)
    {
        $rules = [
            'descrip_type_file' => 'required',
            'type_mime_file' => 'required',
            'extension_file' => 'required'
        ];
        $validator = Validator::make($request->input(), $rules);
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()->all()
            ], 400);
        }
        $type_file_validate = sio_type_file::orwhere(['descrip_type_file' => $request->descrip_type_file])
            ->orwhere(['type_mime_file' => $request->type_mime_file])
            ->orwhere(['extension_file' => $request->extension_file])->get();
        if (sizeof($type_file_validate) == 0) {
            try {
                sio_type_file::insert([
                    'descrip_type_file' => $request->descrip_type_file,
                    'type_mime_file' => $request->type_mime_file,
                    'extension_file' => $request->extension_file,
                ]);
                return response()->json([
                    'status' => true,
                    'message' => 'Type file created successfully'
                ], 200);
            } catch (Exception $cb) {
                return response()->json([
                    'status' => false,
                    'message' =>  'An error ocurred during query: ' . $cb
                ], 200);
            }
        } else {
            return response()->json([
                'status' => false,
                'message' => 'This type of file is already registered'
            ], 200);
        }
    }

    public function updated_status_type_file(Request $request)
    {
        $rules = [
            'id_type_file' => 'required',
        ];
        $validator = Validator::make($request->input(), $rules);
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()->all()
            ], 400);
        }
        try {
            $id_status = sio_type_file::where('id_type_file', $request->id_type_file)->first();
            switch ($id_status->id_status) {
                case 16:
                    sio_type_file::where('id_type_file', $request->id_type_file)->update([
                        'id_status' => 4
                    ]);
                    break;
                case 4:
                    sio_type_file::where('id_type_file', $request->id_type_file)->update([
                        'id_status' => 16
                    ]);
                    break;
            }
            return response()->json([
                'status' => true,
                'message' => 'Type file updated successfully'
            ], 200);
        } catch (Exception $cb) {
            return response()->json([
                'status' => false,
                'message' =>  'An error ocurred during query: ' . $cb
            ], 200);
        }
    }

    public function updated_data_type_file(Request $request)
    {
        $rules = [
            'id_type_file' => 'required',
        ];
        $validator = Validator::make($request->input(), $rules);
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()->all()
            ], 400);
        }
    }



    ///---------------------------------------------------------------------------------///

    //---------------------------Funciones catalogo tipo de usuarios --------------------
    public function ctl_roles()
    {
        $ctl_roles = sio_role::all();
        return response()->json([
            'status' => true,
            'data' => $ctl_roles
        ], 200);
    }

    public function created_role(Request $request)
    {
        $rules = [
            'descrip_role' => 'required',
        ];

        $validator = Validator::make($request->input(), $rules);
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()->all()
            ], 400);
        }
        $role_validate = sio_role::where(['descrip_role' => $request->descrip_role])->get();
        if (sizeof($role_validate) == 0) {
            try {
                sio_role::insert([
                    'descrip_role'  => $request->descrip_role
                ]);
                return response()->json([
                    'status' => true,
                    'message' => 'Role created successfully'
                ], 200);
            } catch (Exception $cb) {
                return response()->json([
                    'status' => false,
                    'message' =>  'An error ocurred during query: ' . $cb
                ], 200);
            }
        } else {
            return response()->json([
                'status' => false,
                'message' => 'This type of user is already registered'
            ], 200);
        }
    }

    public function updated_descrip_role(Request $request)
    {
        $rules = [
            'descrip_role' => 'required',
            'id_role'      => 'required'
        ];

        $validator = Validator::make($request->input(), $rules);
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()->all()
            ], 400);
        }
        $descrip_role_validate = sio_role::where(['descrip_role' => $request->descrip_role])->where(['id_role' => $request->id_role])->get();
        if (sizeof($descrip_role_validate) == 0) {
            try {
                DB::connection('DevSio')->update('exec update_role ?,?', [
                    $request->id_role,
                    $request->descrip_role
                ]);
                return response()->json([
                    'status' => true,
                    'message' => 'Role updated successfully'
                ], 200);
            } catch (Exception $cb) {
                return response()->json([
                    'status' => false,
                    'message' =>  'An error ocurred during query: ' . $cb
                ], 200);
            }
        } else {
            return response()->json([
                'status' => false,
                'message' => 'No changes to reflect, verify information'
            ], 200);
        }
    }

    public function updated_status_role(Request $request)
    {
        $rules = [
            'descrip_role' => 'required',
            'id_role'      => 'required'
        ];

        $validator = Validator::make($request->input(), $rules);
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()->all()
            ], 400);
        }
    }

    //--------------------Funciones catalogo bancos --------------------------------
    public function view_banks()
    {
        $view_banks = DB::connection('DevSio')->table('banks_view')->get();
        return response()->json([
            'status' => true,
            'data' => $view_banks
        ], 200);
    }

    public function created_banks(Request $request)
    {
        $rules = [
            'key_bank' => 'required',
            'name_bank' => 'required',
            'business_name' => 'required'
        ];

        $validator = Validator::make($request->input(), $rules);
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()->all()
            ], 400);
        }
        $views_global = DB::connection('DevSio')->table('banks_view')
            ->orwhere('key_bank', $request->key_bank)
            ->orwhere('name_bank', $request->name_bank)
            ->orwhere('business_name', $request->business_name)->get();
        if (sizeof($views_global) == 0) {
            try {
                sio_bank::insert([
                    'key_bank' => $request->key_bank,
                    'name_bank' => $request->name_bank,
                    'business_name' => $request->business_name
                ]);

                return response()->json([
                    'status' => true,
                    'message' => 'Bank created successfully'
                ], 200);
            } catch (Exception $cb) {
                return response()->json([
                    'status' => false,
                    'message' =>  'An error ocurred during query: ' . $cb
                ], 200);
            }
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Already registered bank'
            ], 200);
        }
    }

    public function detail_bank(Request $request)
    {
        $rules = [
            'id_bank' => 'required'
        ];
        $validator = Validator::make($request->input(), $rules);
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()->all()
            ], 400);
        }
        if (!$request) {
            return response()->json([
                'status' => false,
                'message' => 'No results found',
            ], 200);
        } else {
            $detail_bank = DB::connection('DevSio')->table('banks_view')->where('id_bank', $request->id_bank)->first();
            if ($detail_bank == false) {
                return response()->json([
                    'status' => false,
                    'message' => 'No results found',
                ], 200);
            } else {
                return response()->json([
                    'status' => true,
                    'data' => $detail_bank
                ], 200);
            }
        }
    }


    public function updated_bank(Request $request){
        $rules = [
            'id_bank' => 'required',
            'key_bank' => 'required',
            'name_bank' => 'required',
            'business_name' => 'required'
        ];
        $validator = Validator::make($request->input(), $rules);
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()->all()
            ], 400);
        }
        try {
            DB::connection('DevSio')->update('exec updated_banks ?,?,?,?', [
                $request->id_bank,
                $request->key_bank,
                $request->name_bank,
                $request->business_name
            ]);
            return response()->json([
                'status' => true,
                'message' => 'Banks updated successfully'
            ], 200);
        } catch (Exception $cb) {
            return response()->json([
                'status' => false,
                'message' =>  'An error ocurred during query: ' . $cb
            ], 200);
        }
    }

    public function updated_status_banks(Request $request)
    {
        $rules = [
            'id_bank' => 'required',
        ];
        $validator = Validator::make($request->input(), $rules);
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()->all()
            ], 400);
        }
        try {
            $id_status = sio_bank::where('id_bank', $request->id_bank)->first();
            switch ($id_status->id_status) {
                case 11:
                    sio_bank::where('id_bank', $request->id_bank)->update([
                        'id_status' => 4
                    ]);
                    break;
                case 4:
                    sio_bank::where('id_bank', $request->id_bank)->update([
                        'id_status' => 11
                    ]);
                    break;
            }
            return response()->json([
                'status' => true,
                'message' => 'Bank status updated successfully'
            ], 200);
        } catch (Exception $cb) {
            return response()->json([
                'status' => false,
                'message' =>  'An error ocurred during query: ' . $cb
            ], 200);
        }
    }




    //----------------------Funciones empleados----------------------------

    public function created_employees(Request $request)
    {
        $rules = [
            'name' => 'required',
            'last_name' => 'required',
            'mother_last_name' => 'required',
            'email' => 'required',
            'id_role' => 'required'
        ];
        $validator = Validator::make($request->input(), $rules);
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()->all()
            ], 400);
        }

        function generar_token_seguro($longitud)
        {
            if ($longitud < 4) {
                $longitud = 4;
            }
            return bin2hex(openssl_random_pseudo_bytes(($longitud - ($longitud % 2)) / 2));
        }

        $token_user = generar_token_seguro(5);
        $name = $request->name;
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

        $employees_validate = sio_employees::where(['name' => $request->name])
            ->where(['last_name' => $request->last_name])
            ->where(['mother_last_name' => $request->mother_last_name])
            ->where(['email' => $request->email])->get();

        if (sizeof($employees_validate) == 0) {
            try {
                sio_employees::insert([
                    'name'              => $request->name,
                    'last_name'         => $request->last_name,
                    'mother_last_name'  => $request->mother_last_name,
                    'cell_phone_number' => $request->cell_phone_number,
                    'email'     => $request->email,
                    'username'  => $user_name,
                    'password'  => $password_token,
                    'id_role'   => $request->id_role
                ]);
                $last_inserted_id = sio_employees::latest('id_employees')->first();
                $id_employees = $last_inserted_id['id_employees'];
                $dataCredentials = [
                    'id_user' => $id_employees,
                    'username' => $user_name,
                    'password' => $password_user
                ];
                return response()->json([
                    'message' => 'Employeed created successfully.',
                    'data' => $dataCredentials
                ], 200);
            } catch (Exception $cb) {
                return response()->json([
                    'status' => false,
                    'message' =>  'An error ocurred during query: ' . $cb
                ], 200);
            }
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Already registered employee',
            ], 200);
        }
    }
    //------------------ Funciones recibos ---------

    public function ctl_receipts()
    {
        try {
            set_time_limit(0);
            $ctl_receipts = sio_payment_receipts::orderBy('id_payment_receipts', 'desc')->get();
            return response()->json([
                'status' => true,
                'data' => $ctl_receipts
            ], 200);
        } catch (Exception $cb) {
            return response()->json([
                'status' => false,
                'message' =>  'An error ocurred during query: ' . $cb
            ], 200);
        }
    }

    public function view_receipts_complete()
    {
        try {
            set_time_limit(0);
            $view_receipts_c = DB::connection('DevSio')->select('select * from receipts_complete_view order by pay_date desc');
            return response()->json([
                'status' => true,
                'data' => $view_receipts_c
            ], 200);
        } catch (Exception $cb) {
            return response()->json([
                'status' => false,
                'message' =>  'An error ocurred during query: ' . $cb
            ], 200);
        }
    }

    public function view_receipts_incomplete()
    {
        try {
            set_time_limit(0);
            $view_receipts_i = DB::connection('DevSio')->select('select * from receipts_incomplete_view order by pay_date desc');
            return response()->json([
                'status' => true,
                'data' => $view_receipts_i
            ], 200);
        } catch (Exception $cb) {
            return response()->json([
                'status' => false,
                'message' =>  'An error ocurred during query: ' . $cb
            ], 200);
        }
    }


    //------------------ Funciones socios comanditarios ---------
    public function ctl_partners()
    {
        try {
            set_time_limit(0);
            $ctl_receipts = DB::connection('DevSio')->table('partners_general')->orderBy('id_partener', 'desc')->get();
            return response()->json([
                'status' => true,
                'data' => $ctl_receipts
            ], 200);
        } catch (Exception $cb) {
            return response()->json([
                'status' => false,
                'message' =>  'An error ocurred during query: ' . $cb
            ], 200);
        }
    }


    public function ctl_partners_general()
    {
        try {
            $ctl_status = sio_partners::all();
            return response()->json([
                'status' => true,
                'data' => $ctl_status
            ], 200);
        } catch (Exception $cb) {
            return response()->json([
                'status' => false,
                'message' =>  'An error ocurred during query: ' . $cb
            ], 200);
        }
    }

    public function detail_partners(Request $request)
    {
        $rules = [
            'id_partener' => 'required'
        ];
        $validator = Validator::make($request->input(), $rules);
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()->all()
            ], 400);
        }
        if (!$request) {
            return response()->json([
                'status' => false,
                'message' => 'No results found',
            ], 200);
        } else {
            $details_partener = DB::connection('DevSio')->table('tbl_partners')
            ->where('id_partener', $request->input('id_partener'))->get();
            if ($details_partener == false) {
                return response()->json([
                    'status' => false,
                    'message' => 'No results found',
                ], 200);
            } else {
                return response()->json([
                    'status' => true,
                    'data' => $details_partener
                ], 200);
            }
        }
    }


    public function updated_partners(Request $request)
    {
        $data = json_decode($request->getContent());

        $rules = [
            'name' => 'required',
            'last_name' => 'required',
            'mother_last_name' => 'required',
            'id_sex' => 'required',
            'birthdate' => 'required',
            'curp' => 'required',
            'rfc' => 'required'
        ];

        $validator = Validator::make($request->input(), $rules);
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()->all()
            ], 400);
        }
        
        try {
            DB::connection('DevSio')->update('exec update_partners ?,?,?,?,?,?,?,?', [
                $data->id_partener,
                $data->name,
                $data->last_name,
                $data->mother_last_name,
                $data->id_sex,
                $data->birthdate,
                $data->curp,
                $data->rfc,
            ]);
            return response()->json([
                'status' => true,
                'message' => 'Partner updated successfully'
            ], 200);
        } catch (Exception $cb) {
            return response()->json([
                'status' => false,
                'message' =>  'An error ocurred during query: ' . $cb
            ], 200);
        }
    }

    public function created_partners(Request $request)
    {
        $rules = [
            'name' => 'required',
            'last_name' => 'required',
            'mother_last_name' => 'required',
            'id_sex' => 'required',
            'birthdate' => 'required',
            'curp' => 'required',
            'rfc' => 'required',
            'id_bank' => 'required',
            'account_number' => 'required',
            'key_account' => 'required',
            'card_number' => 'required',
            'id_lada_cell_phone' => 'required',
            'cell_phone_number' => 'required',
            'email' => 'required',
            'id_states' => 'required',
            'id_municipality' => 'required',
            'location' => 'required',
            'street' => 'required',
            'cologne' => 'required',
            'outdoor_number' => 'required',
            'interior_number' => 'required',
            'cp' => 'required',
            'references_1' => 'required',
            'references_2' => 'required',
            'id_education_level' => 'required',
            'id_marital_status' => 'required',
            'id_employees' => 'required',
            'id_nationality' => 'required'
        ];

        $validator = Validator::make($request->input(), $rules);
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()->all()
            ], 400);
        }
        $details_parteners = DB::connection('DevSio')->table('tbl_partners')
            ->where('name', $request->name)
            ->where('last_name', $request->last_name)
            ->where('mother_last_name', $request->mother_last_name)->get();
        if (sizeof($details_parteners) == 0) {
            try {
                sio_partners::insert([
                    'name' => $request->name,
                    'last_name' => $request->last_name,
                    'mother_last_name' => $request->mother_last_name,
                    'id_sex' => $request->id_sex,
                    'birthdate' => $request->birthdate,
                    'curp' => $request->curp,
                    'rfc' => $request->rfc,
                    'id_bank' => $request->id_bank,
                    'account_number' => $request->account_number,
                    'key_account' => $request->key_account,
                    'card_number' => $request->card_number,
                    'id_lada_cell_phone' => $request->id_lada_cell_phone,
                    'cell_phone_number' => $request->cell_phone_number,
                    'email' => $request->email,
                    'id_states' => $request->id_states,
                    'id_municipality' => $request->id_municipality,
                    'location' => $request->location,
                    'street' => $request->street,
                    'cologne' => $request->cologne,
                    'outdoor_number' => $request->outdoor_number,
                    'interior_number' => $request->interior_number,
                    'cp' => $request->cp,
                    'references_1' => $request->references_1,
                    'references_2' => $request->references_2,
                    'id_education_level' => $request->id_education_level,
                    'id_marital_status' => $request->id_marital_status,
                    'id_employees' => $request->id_employees,
                    'id_nationality' => $request->id_nationality
                ]);

                return response()->json([
                    'status' => true,
                    'message' => 'Partner created successfully'
                ], 200);
            } catch (Exception $cb) {
                return response()->json([
                    'status' => false,
                    'message' =>  'An error ocurred during query: ' . $cb
                ], 200);
            }
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Already registered partner'
            ], 200);
        }
    }

    //----------------------Funciones cuentas origen----------------------------
    public function ctl_account_origin()
    {
        try {
            set_time_limit(0);
            $ctl_account_origin = DB::connection('DevSio')->table('origin_account_view')->get();
            return response()->json([
                'status' => true,
                'data' => $ctl_account_origin
            ], 200);
        } catch (Exception $cb) {
            return response()->json([
                'status' => false,
                'message' =>  'An error ocurred during query: ' . $cb
            ], 200);
        }
    }

    public function create_account_origin(Request $request)
    {
        $rules = [
            "clabe" => "required",
            "bank" => "required"
        ];

        $validator = Validator::make($request->input(), $rules);
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()->all()
            ], 400);
        }

        try {
            $origin_account_exist = sio_origin_accounts::where("account_number", $request->clabe)->where("id_bank", $request->bank)->get();

            if (sizeof($origin_account_exist) == 0) {
                sio_origin_accounts::insert([
                    "account_number" => $request->clabe,
                    "id_bank" => $request->bank
                ]);

                return response()->json([
                    'status' => true,
                    'message' => "Source account has been created."
                ], 200);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => "This source account already exist."
                ], 200);
            }
        } catch (Exception $th) {
            return response()->json([
                'status' => false,
                'message' => "An error ocurred on query."
            ], 200);
        }
    }

    public function detail_origin_account(Request $request)
    {
        $rules = [
            'id_origin_accounts' => 'required'
        ];
        $validator = Validator::make($request->input(), $rules);
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()->all()
            ], 400);
        }
        if (!$request) {
            return response()->json([
                'status' => false,
                'message' => 'No results found',
            ], 200);
        } else {
            $detail_origin_accounts = DB::connection('DevSio')->table('origin_account_view')->where('id_origin_accounts', $request->id_origin_accounts)->first();
            if ($detail_origin_accounts == false) {
                return response()->json([
                    'status' => false,
                    'message' => 'No results found',
                ], 200);
            } else {
                return response()->json([
                    'status' => true,
                    'data' => $detail_origin_accounts
                ], 200);
            }
        }
    }

    public function updated_status_origin_account(Request $request)
    {
        $rules = [
            'id_origin_accounts' => 'required',
        ];
        $validator = Validator::make($request->input(), $rules);
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()->all()
            ], 400);
        }
        try {
            $id_status = sio_origin_accounts::where('id_origin_accounts', $request->id_origin_accounts)->first();
            switch ($id_status->id_status) {
                case 8:
                    sio_origin_accounts::where('id_origin_accounts', $request->id_origin_accounts)->update([
                        'id_status' => 4
                    ]);
                    break;
                case 4:
                    sio_origin_accounts::where('id_origin_accounts', $request->id_origin_accounts)->update([
                        'id_status' => 8
                    ]);
                    break;
            }
            return response()->json([
                'status' => true,
                'message' => 'Origin account status updated successfully'
            ], 200);
        } catch (Exception $cb) {
            return response()->json([
                'status' => false,
                'message' =>  'An error ocurred during query: ' . $cb
            ], 200);
        }
    }


    public function updated_origin_account(Request $request){
        $rules = [
            'key_account' => 'required',
            'id_bank' => 'required',
            'id_origin_accounts' => 'required'
        ];
        $validator = Validator::make($request->input(), $rules);
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()->all()
            ], 400);
        }
        try {
            DB::connection('DevSio')->update('exec updated_origin_accounts ?,?,?,?,?,?,?', [
                $request->account_number,
                $request->key_account,
                $request->card_number,
                $request->account_holder,
                $request->exchange_rate,
                $request->id_bank,
                $request->id_origin_accounts
            ]);
            return response()->json([
                'status' => true,
                'message' => 'Origin account updated successfully'
            ], 200);
        } catch (Exception $cb) {
            return response()->json([
                'status' => false,
                'message' =>  'An error ocurred during query: ' . $cb
            ], 200);
        }
    }


    ////---------------------Funciones compañias----------------------------------------

    public function ctl_cia()
    {
        try {
            set_time_limit(0);
            $ctl_cia = DB::connection('DevSio')->table('cia_view')->get();
            return response()->json([
                'status' => true,
                'data' => $ctl_cia
            ], 200);
        } catch (Exception $cb) {
            return response()->json([
                'status' => false,
                'message' =>  'An error ocurred during query: ' . $cb
            ], 200);
        }
    }

    public function create_cia(Request $request)
    {
        $rules = [
            "name_cia" => "required",
            "abbreviation_cia" => "required"
        ];

        $validator = Validator::make($request->input(), $rules);
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()->all()
            ], 400);
        }

        try {
            $cia_exist = sio_cia::where("name_cia", $request->name_cia)->where("abbreviation_cia", $request->abbreviation_cia)->get();

            if (sizeof($cia_exist) == 0) {
                sio_cia::insert([
                    "name_cia" => $request->name_cia,
                    "abbreviation_cia" => $request->abbreviation_cia
                ]);

                return response()->json([
                    'status' => true,
                    'message' => "Cia has been created."
                ], 200);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => "This cia already exist."
                ], 200);
            }
        } catch (Exception $th) {
            return response()->json([
                'status' => false,
                'message' =>  'An error ocurred during query: ' . $th
            ], 200);
        }
    }

    public function updated_status_cia(Request $request)
    {
        $rules = [
            'id_cia' => 'required',
        ];
        $validator = Validator::make($request->input(), $rules);
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()->all()
            ], 400);
        }
        try {
            $id_status = sio_cia::where('id_cia', $request->id_cia)->first();
            switch ($id_status->id_status) {
                case 18:
                    sio_cia::where('id_cia', $request->id_cia)->update([
                        'id_status' => 7
                    ]);
                    break;
                case 7:
                    sio_cia::where('id_cia', $request->id_cia)->update([
                        'id_status' => 18
                    ]);
                    break;
            }
            return response()->json([
                'status' => true,
                'message' => 'Company status updated successfully'
            ], 200);
        } catch (Exception $cb) {
            return response()->json([
                'status' => false,
                'message' =>  'An error ocurred during query: ' . $cb
            ], 200);
        }
    }


    public function updated_cia(Request $request){
        $rules = [
            'name_cia' => 'required',
            'abbreviation_cia' => 'required',
            'id_cia' => 'required'
        ];
        $validator = Validator::make($request->input(), $rules);
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()->all()
            ], 400);
        }
        try {
            DB::connection('DevSio')->update('exec updated_cia ?,?,?,?,?', [
                $request->name_cia,
                $request->abbreviation_cia,
                $request->business_name,
                $request->rfc_cia,
                $request->id_cia
            ]);
            return response()->json([
                'status' => true,
                'message' => 'Company updated successfully'
            ], 200);
        } catch (Exception $cb) {
            return response()->json([
                'status' => false,
                'message' =>  'An error ocurred during query: ' . $cb
            ], 200);
        }
    }

    public function detail_cia(Request $request)
    {
        $rules = [
            'id_cia' => 'required'
        ];
        $validator = Validator::make($request->input(), $rules);
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()->all()
            ], 400);
        }
        if (!$request) {
            return response()->json([
                'status' => false,
                'message' => 'No results found',
            ], 200);
        } else {
            $details_cia = DB::connection('DevSio')->table('cia_view')->where('id_cia', $request->id_cia)->first();
            if ($details_cia == false) {
                return response()->json([
                    'status' => false,
                    'message' => 'No results found',
                ], 200);
            } else {
                return response()->json([
                    'status' => true,
                    'data' => $details_cia
                ], 200);
            }
        }
    }

    ////---------------------Funciones compañias----------------------------------------
    public function ctl_doc_partners_global()
    {
        try {
            set_time_limit(0);
            $ctl_doc_global = DB::connection('DevSio')->table('partners_doc_global_view')->get();
            return response()->json([
                'status' => true,
                'data' => $ctl_doc_global
            ], 200);
        } catch (Exception $cb) {
            return response()->json([
                'status' => false,
                'message' =>  'An error ocurred during query: ' . $cb
            ], 200);
        }
    }


    ////---------------------Funciones estatos de cuenta----------------------------------------
    public function ct_states_accounts()
    {
        try {
            set_time_limit(0);
            $ctl_states_accounts = DB::connection('DevSio')->table('states_account_view')->get();
            return response()->json([
                'status' => true,
                'data' => $ctl_states_accounts
            ], 200);
        } catch (Exception $cb) {
            return response()->json([
                'status' => false,
                'message' =>  'An error ocurred during query: ' . $cb
            ], 200);
        }
    }

     ////---------------------Funciones municipios----------------------------------------
     public function ctl_municipality(Request $request)
     {
         try {
             set_time_limit(0);
             $ctl_municipality = DB::connection('DevSio')->table('ctl_municipality')->where('id_states', $request->id_states)->orderBy('id_municipality', 'desc')->get();
             return response()->json([
                 'status' => true,
                 'data' => $ctl_municipality
             ], 200);
         } catch (Exception $cb) {
             return response()->json([
                 'status' => false,
                 'message' =>  'An error ocurred during query: ' . $cb
             ], 200);
         }
     }
}
