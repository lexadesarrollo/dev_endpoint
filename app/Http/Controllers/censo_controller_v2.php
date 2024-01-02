<?php

namespace App\Http\Controllers;

use App\Models\censo_commissions_v2;
use App\Models\censo_company_v2;
use App\Models\censo_credentials_v2;
use App\Models\censo_device_user_v2;
use App\Models\censo_lada_v2;
use App\Models\censo_line_business_v2;
use App\Models\censo_municipality_v2;
use App\Models\censo_registered_businesses_v2;
use App\Models\censo_roads_v2;
use App\Models\censo_role_v2;
use App\Models\censo_settlements_v2;
use App\Models\censo_state_v2;
use App\Models\censo_status_v2;
use App\Models\censo_type_business_v2;
use App\Models\censo_users_v2;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class censo_controller_v2 extends Controller
{
    protected $connection = 'DevCenso';

    //Funciones status
    public function ctl_status()
    {
        $ctl_status = censo_status_v2::all();
        return response()->json([
            'status' => true,
            'message' => 'Successful response.',
            'data' => $ctl_status
        ], 200);
    }

    public function created_status(Request $request)
    {
        $rules = [
            'name_status' => 'required'
        ];
        $validator = Validator::make($request->input(), $rules);
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->message()->all()
            ]);
        }
        $validate_status = censo_status_v2::where([
            'name_status' => $request->input('name_status')
        ])->get();
        if (sizeof($validate_status) == 0) {
            $name_status = ucfirst($request->input('name_status'));
            $create_register_bussines = censo_status_v2::insert(
                [
                    'name_status' => $name_status
                ]
            );
            if ($create_register_bussines) {
                return response()->json([
                    'status' => true,
                    'message' => 'The status has been successfully registered.',
                ], 200);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'An error occurred while performing the operation.',
                ], 200);
            }
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Status is already registered, verify information.',
            ], 200);
        }
    }


    public function ctl_role()
    {
        $ctl_role = censo_role_v2::all();
        return response()->json([
            'status' => true,
            'message' => 'Successful response.',
            'data' => $ctl_role
        ], 200);
    }

    public function ctl_company()
    {
        $ctl_company = censo_company_v2::all();
        return response()->json([
            'status' => true,
            'message' => 'Successful response.',
            'data' => $ctl_company
        ], 200);
    }

    public function ctl_lada()
    {
        $ctl_lada = censo_lada_v2::all();
        return response()->json([
            'status' => true,
            'message' => 'Successful response.',
            'data' => $ctl_lada
        ], 200);
    }

    public function ctl_line_business()
    {
        $ctl_line_business = censo_line_business_v2::all();
        return response()->json([
            'status' => true,
            'message' => 'Successful response.',
            'data' => $ctl_line_business
        ], 200);
    }

    public function ctl_type_business()
    {
        $ctl_type_business = censo_type_business_v2::all();
        return response()->json([
            'status' => true,
            'message' => 'Successful response.',
            'data' => $ctl_type_business
        ], 200);
    }

    public function ctl_state()
    {
        $ctl_state = censo_state_v2::all();
        return response()->json([
            'status' => true,
            'message' => 'Successful response.',
            'data' => $ctl_state
        ], 200);
    }

    public function ctl_municipality()
    {
        $ctl_municipality = censo_municipality_v2::all();
        return response()->json([
            'status' => true,
            'message' => 'Successful response.',
            'data' => $ctl_municipality
        ], 200);
    }

    public function ctl_roads()
    {
        $ctl_roads = censo_roads_v2::all();
        return response()->json([
            'status' => true,
            'message' => 'Successful response.',
            'data' => $ctl_roads
        ], 200);
    }

    public function ctl_settlements()
    {
        $ctl_settlements = censo_settlements_v2::all();
        return response()->json([
            'status' => true,
            'message' => 'Successful response.',
            'data' => $ctl_settlements
        ], 200);
    }

    public function tbl_commissions()
    {
        $tbl_commissions = censo_commissions_v2::all();
        return response()->json([
            'status' => true,
            'message' => 'Successful response.',
            'data' => $tbl_commissions
        ], 200);
    }

    public function tbl_credentials()
    {
        $tbl_credentials = censo_credentials_v2::all();
        return response()->json([
            'status' => true,
            'message' => 'Successful response.',
            'data' => $tbl_credentials
        ], 200);
    }

    public function tbl_device_user()
    {
        $tbl_device_user = censo_device_user_v2::all();
        return response()->json([
            'status' => true,
            'message' => 'Successful response.',
            'data' => $tbl_device_user
        ], 200);
    }

    public function tbl_registered_businesses()
    {
        $tbl_registered_businesses = censo_registered_businesses_v2::all();
        return response()->json([
            'status' => true,
            'message' => 'Successful response.',
            'data' => $tbl_registered_businesses
        ], 200);
    }

    public function tbl_users()
    {
        $tbl_users = censo_users_v2::all();
        return response()->json([
            'status' => true,
            'message' => 'Successful response.',
            'data' => $tbl_users
        ], 200);
    }
}
