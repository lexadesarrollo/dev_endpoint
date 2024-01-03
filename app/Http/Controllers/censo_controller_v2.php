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
use App\Models\censo_status;
use App\Models\censo_status_v2;
use App\Models\censo_type_business_v2;
use App\Models\censo_users_v2;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class censo_controller_v2 extends Controller
{
    protected $connection = 'DevCenso';

    //-------------------------Funciones status-------------------------//
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
                'message' => $validator->errors()->all()
            ]);
        }
        $validate_status = censo_status_v2::where([
            'name_status' => $request->input('name_status')
        ])->get();
        if (sizeof($validate_status) == 0) {
            $name_status = ucfirst($request->input('name_status'));
            $created_status = censo_status_v2::insert(
                [
                    'name_status' => $name_status
                ]
            );
            if ($created_status) {
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

    public function updated_status(Request $request)
    {
        $rules = [
            'name_status' => 'required',
            'id_status' => 'required'
        ];
        $validator = Validator::make($request->input(), $rules);
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->all()
            ]);
        }
        $status = censo_status_v2::where('id_status', $request->id_status)->update([
            'name_status' => $request->name_status
        ]);
        if ($status) {
            return response()->json([
                'status' => true,
                'message' => 'The status has been successfully updated.',
            ], 200);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while performing the operation.',
            ], 200);
        }
    }

    //-------------------------Funciones role-------------------------//

    public function ctl_role()
    {
        $ctl_role = censo_role_v2::all();
        return response()->json([
            'status' => true,
            'message' => 'Successful response.',
            'data' => $ctl_role
        ], 200);
    }

    public function created_role(Request $request)
    {
        $rules = [
            'name_role' => 'required'
        ];
        $validator = Validator::make($request->input(), $rules);
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->all()
            ]);
        }
        $validate_role = censo_role_v2::where([
            'name_role' => $request->input('name_role')
        ])->get();
        if (sizeof($validate_role) == 0) {
            $name_role = ucfirst($request->input('name_role'));
            $created_role = censo_role_v2::insert(
                [
                    'name_role' => $name_role
                ]
            );
            if ($created_role) {
                return response()->json([
                    'status' => true,
                    'message' => 'The role has been successfully registered.',
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
                'message' => 'Role is already registered, verify information.',
            ], 200);
        }
    }

    public function updated_role(Request $request)
    {
        $rules = [
            'id_role' => 'required',
            'name_role' => 'required'
        ];
        $validator = Validator::make($request->input(), $rules);
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->all()
            ], 200);
        }
        try {
            DB::connection('DevCenso')->update('exec updated_role ?,?', [
                $request->id_role,
                $request->name_role
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
    }

    public function updated_status_role(Request $request)
    {
        $rules = [
            'id_role' => 'required',
        ];
        $validator = Validator::make($request->input(), $rules);
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->all()
            ], 200);
        }
        try {
            $id_status = censo_role_v2::where('id_role', $request->id_role)->first();
            switch ($id_status->id_status) {
                case 1:
                    censo_role_v2::where('id_role', $request->id_role)->update([
                        'id_status' => 2
                    ]);
                    break;
                case 2:
                    censo_role_v2::where('id_role', $request->id_role)->update([
                        'id_status' => 1
                    ]);
                    break;
            }
            return response()->json([
                'status' => true,
                'message' => 'Role status updated successfully'
            ], 200);
        } catch (Exception $cb) {
            return response()->json([
                'status' => false,
                'message' =>  'An error ocurred during query: ' . $cb
            ], 200);
        }
    }

    public function detail_role(Request $request)
    {
        $rules = [
            'id_role' => 'required',
        ];
        $validator = Validator::make($request->input(), $rules);
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->all()
            ], 200);
        }

        $role = DB::connection('DevCenso')->table('ctl_role')->where('id_role', $request->id_role)->first();
        if ($role == false) {
            return response()->json([
                'status' => false,
                'message' => 'No results found',
            ], 200);
        } else {
            return response()->json([
                'status' => true,
                'message' => $role
            ], 200);
        }
    }

    //-------------------------Funciones Company-------------------------//

    public function ctl_company()
    {
        $ctl_company = censo_company_v2::all();
        return response()->json([
            'status' => true,
            'message' => 'Successful response.',
            'data' => $ctl_company
        ], 200);
    }

    public function created_company(Request $request)
    {
        $rules = [
            'name_company' => 'required'
        ];
        $validator = Validator::make($request->input(), $rules);
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->all()
            ]);
        }
        $validate_company = censo_company_v2::where([
            'name_company' => $request->input('name_company')
        ])->get();
        if (sizeof($validate_company) == 0) {
            $name_company = ucfirst($request->input('name_company'));
            $created_company = censo_company_v2::insert(
                [
                    'name_company' => $name_company
                ]
            );
            if ($created_company) {
                return response()->json([
                    'status' => true,
                    'message' => 'The company has been successfully registered.',
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
                'message' => 'Company is already registered, verify information.',
            ], 200);
        }
    }

    public function updated_company(Request $request)
    {
        $rules = [
            'id_company' => 'required',
            'name_company' => 'required'
        ];
        $validator = Validator::make($request->input(), $rules);
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->all()
            ], 200);
        }
        try {
            DB::connection('DevCenso')->update('exec updated_company ?,?', [
                $request->id_company,
                $request->name_company
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

    public function updated_status_company(Request $request)
    {
        $rules = [
            'id_company' => 'required',
        ];
        $validator = Validator::make($request->input(), $rules);
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->all()
            ], 200);
        }
        try {
            $id_status = censo_company_v2::where('id_company', $request->id_company)->first();
            switch ($id_status->id_status) {
                case 1:
                    censo_company_v2::where('id_company', $request->id_company)->update([
                        'id_status' => 2
                    ]);
                    break;
                case 2:
                    censo_company_v2::where('id_company', $request->id_company)->update([
                        'id_status' => 1
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

    public function detail_company(Request $request)
    {
        $rules = [
            'id_company' => 'required',
        ];
        $validator = Validator::make($request->input(), $rules);
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->all()
            ], 200);
        }

        $company = DB::connection('DevCenso')->table('ctl_company')->where('id_company', $request->id_company)->first();
        if ($company == false) {
            return response()->json([
                'status' => false,
                'message' => 'No results found',
            ], 200);
        } else {
            return response()->json([
                'status' => true,
                'message' => $company
            ], 200);
        }
    }

    //-------------------------Funciones Lada-------------------------//

    public function ctl_lada()
    {
        $ctl_lada = censo_lada_v2::all();
        return response()->json([
            'status' => true,
            'message' => 'Successful response.',
            'data' => $ctl_lada
        ], 200);
    }

    public function created_lada(Request $request)
    {
        $rules = [
            'lada_cell_phone' => 'required',
            'country_lada' => 'required'
        ];
        $validator = Validator::make($request->input(), $rules);
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->all()
            ]);
        }
        $validate_lada = censo_lada_v2::orwhere([
            'lada_cell_phone' => $request->input('lada_cell_phone')
        ])
            ->orwhere(['country_lada' => $request->country_lada])
            ->get();
        if (sizeof($validate_lada) == 0) {
            $created_company = censo_lada_v2::insert(
                [
                    'lada_cell_phone' => $request->lada_cell_phone,
                    'country_lada' => $request->country_lada
                ]
            );
            if ($created_company) {
                return response()->json([
                    'status' => true,
                    'message' => 'The lada has been successfully registered.',
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
                'message' => 'Lada is already registered, verify information.',
            ], 200);
        }
    }

    public function updated_lada(Request $request)
    {
        $rules = [
            'id_lada' => 'required',
            'lada_cell_phone' => 'required',
            'country_lada' => 'required'
        ];
        $validator = Validator::make($request->input(), $rules);
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->all()
            ], 200);
        }
        try {
            DB::connection('DevCenso')->update('exec updated_lada ?,?,?', [
                $request->id_lada,
                $request->lada_cell_phone,
                $request->country_lada
            ]);
            return response()->json([
                'status' => true,
                'message' => 'Lada updated successfully'
            ], 200);
        } catch (Exception $cb) {
            return response()->json([
                'status' => false,
                'message' =>  'An error ocurred during query: ' . $cb
            ], 200);
        }
    }

    public function updated_status_lada(Request $request)
    {
        $rules = [
            'id_lada' => 'required',
        ];
        $validator = Validator::make($request->input(), $rules);
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->all()
            ], 200);
        }
        try {
            $id_status = censo_lada_v2::where('id_lada', $request->id_lada)->first();
            switch ($id_status->id_status) {
                case 1:
                    censo_lada_v2::where('id_lada', $request->id_lada)->update([
                        'id_status' => 2
                    ]);
                    break;
                case 2:
                    censo_lada_v2::where('id_lada', $request->id_lada)->update([
                        'id_status' => 1
                    ]);
                    break;
            }
            return response()->json([
                'status' => true,
                'message' => 'Lada status updated successfully'
            ], 200);
        } catch (Exception $cb) {
            return response()->json([
                'status' => false,
                'message' =>  'An error ocurred during query: ' . $cb
            ], 200);
        }
    }

    public function detail_lada(Request $request)
    {
        $rules = [
            'id_lada' => 'required',
        ];
        $validator = Validator::make($request->input(), $rules);
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->all()
            ], 200);
        }

        $lada = DB::connection('DevCenso')->table('ctl_lada')->where('id_lada', $request->id_lada)->first();
        if ($lada == false) {
            return response()->json([
                'status' => false,
                'message' => 'No results found',
            ], 200);
        } else {
            return response()->json([
                'status' => true,
                'message' => $lada
            ], 200);
        }
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
