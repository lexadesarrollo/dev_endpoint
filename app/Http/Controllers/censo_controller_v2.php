<?php

namespace App\Http\Controllers;

use App\Models\censo_commissions_v2;
use App\Models\censo_company_v2;
use App\Models\censo_credentials_v2;
use App\Models\censo_device_user_v2;
use App\Models\censo_lada_v2;
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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
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
        $ctl_role = censo_role_v2::all()->where('id_status', 1);
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
            $name_role = ucfirst($request->input('name_role'));
            DB::connection('DevCenso')->update('exec updated_role ?,?', [
                $request->id_role,
                $name_role
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
        $ctl_company = censo_company_v2::all()->where('id_status', 1);
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
            $name_company = ucfirst($request->input('name_company'));
            DB::connection('DevCenso')->update('exec updated_company ?,?', [
                $request->id_company,
                $name_company
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
        $ctl_lada = censo_lada_v2::all()->where('id_status', 1);
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
            $created_lada = censo_lada_v2::insert(
                [
                    'lada_cell_phone' => $request->lada_cell_phone,
                    'country_lada' => $request->country_lada
                ]
            );
            if ($created_lada) {
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

    //-------------------------Funciones Type Business-------------------------//

    public function ctl_type_business()
    {
        $ctl_type_business = censo_type_business_v2::all()->where('id_status', 1);
        return response()->json([
            'status' => true,
            'message' => 'Successful response.',
            'data' => $ctl_type_business
        ], 200);
    }

    public function created_type_business(Request $request)
    {
        $rules = [
            'descrip_type_business' => 'required',
        ];
        $validator = Validator::make($request->input(), $rules);
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->all()
            ]);
        }
        $validate_type_business = censo_type_business_v2::where([
            'descrip_type_business' => $request->input('descrip_type_business')
        ])->get();
        if (sizeof($validate_type_business) == 0) {
            $descrip_type_business = ucfirst($request->input('descrip_type_business'));
            $created_type_bussiness = censo_type_business_v2::insert(
                [
                    'descrip_type_business' => $descrip_type_business
                ]
            );
            if ($created_type_bussiness) {
                return response()->json([
                    'status' => true,
                    'message' => 'The type business has been successfully registered.',
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
                'message' => 'Type business is already registered, verify information.',
            ], 200);
        }
    }

    public function updated_type_business(Request $request)
    {
        $rules = [
            'id_type_business' => 'required',
            'descrip_type_business' => 'required'
        ];
        $validator = Validator::make($request->input(), $rules);
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->all()
            ], 200);
        }
        try {
            $descrip_type_business = ucfirst($request->input('descrip_type_business'));
            DB::connection('DevCenso')->update('exec updated_type_business ?,?', [
                $request->id_type_business,
                $descrip_type_business
            ]);
            return response()->json([
                'status' => true,
                'message' => 'Type business updated successfully'
            ], 200);
        } catch (Exception $cb) {
            return response()->json([
                'status' => false,
                'message' =>  'An error ocurred during query: ' . $cb
            ], 200);
        }
    }

    public function updated_status_type_business(Request $request)
    {
        $rules = [
            'id_type_business' => 'required',
        ];
        $validator = Validator::make($request->input(), $rules);
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->all()
            ], 200);
        }
        try {
            $id_status = censo_type_business_v2::where('id_type_business', $request->id_type_business)->first();
            switch ($id_status->id_status) {
                case 1:
                    censo_type_business_v2::where('id_type_business', $request->id_type_business)->update([
                        'id_status' => 2
                    ]);
                    break;
                case 2:
                    censo_type_business_v2::where('id_type_business', $request->id_type_business)->update([
                        'id_status' => 1
                    ]);
                    break;
            }
            return response()->json([
                'status' => true,
                'message' => 'Type business status updated successfully'
            ], 200);
        } catch (Exception $cb) {
            return response()->json([
                'status' => false,
                'message' =>  'An error ocurred during query: ' . $cb
            ], 200);
        }
    }

    public function detail_type_business(Request $request)
    {
        $rules = [
            'id_type_business' => 'required',
        ];
        $validator = Validator::make($request->input(), $rules);
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->all()
            ], 200);
        }

        $lada = DB::connection('DevCenso')->table('ctl_type_business')->where('id_type_business', $request->id_type_business)->first();
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

    //-------------------------Funciones State-------------------------//

    public function ctl_state()
    {
        $ctl_state = censo_state_v2::all()->where('id_status', 1);
        return response()->json([
            'status' => true,
            'message' => 'Successful response.',
            'data' => $ctl_state
        ], 200);
    }

    public function created_state(Request $request)
    {
        $rules = [
            'name_state' => 'required',
        ];
        $validator = Validator::make($request->input(), $rules);
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->all()
            ]);
        }
        $validate_state = censo_state_v2::where([
            'name_state' => $request->input('name_state')
        ])->get();
        if (sizeof($validate_state) == 0) {
            $name_state = ucfirst($request->input('name_state'));
            $created_state = censo_state_v2::insert(
                [
                    'name_state' => $name_state
                ]
            );
            if ($created_state) {
                return response()->json([
                    'status' => true,
                    'message' => 'The state has been successfully registered.',
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
                'message' => 'State is already registered, verify information.',
            ], 200);
        }
    }

    public function updated_state(Request $request)
    {
        $rules = [
            'id_state' => 'required',
            'name_state' => 'required'
        ];
        $validator = Validator::make($request->input(), $rules);
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->all()
            ], 200);
        }
        try {
            $name_state = ucfirst($request->input('name_state'));
            DB::connection('DevCenso')->update('exec updated_state ?,?', [
                $request->id_state,
                $name_state
            ]);
            return response()->json([
                'status' => true,
                'message' => 'State updated successfully'
            ], 200);
        } catch (Exception $cb) {
            return response()->json([
                'status' => false,
                'message' =>  'An error ocurred during query: ' . $cb
            ], 200);
        }
    }

    public function updated_status_state(Request $request)
    {
        $rules = [
            'id_state' => 'required',
        ];
        $validator = Validator::make($request->input(), $rules);
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->all()
            ], 200);
        }
        try {
            $id_status = censo_state_v2::where('id_state', $request->id_state)->first();
            switch ($id_status->id_status) {
                case 1:
                    censo_state_v2::where('id_state', $request->id_state)->update([
                        'id_status' => 2
                    ]);
                    break;
                case 2:
                    censo_state_v2::where('id_state', $request->id_state)->update([
                        'id_status' => 1
                    ]);
                    break;
            }
            return response()->json([
                'status' => true,
                'message' => 'State status updated successfully'
            ], 200);
        } catch (Exception $cb) {
            return response()->json([
                'status' => false,
                'message' =>  'An error ocurred during query: ' . $cb
            ], 200);
        }
    }

    public function detail_state(Request $request)
    {
        $rules = [
            'id_state' => 'required',
        ];
        $validator = Validator::make($request->input(), $rules);
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->all()
            ], 200);
        }

        $state = DB::connection('DevCenso')->table('ctl_state')->where('id_state', $request->id_state)->first();
        if ($state == false) {
            return response()->json([
                'status' => false,
                'message' => 'No results found',
            ], 200);
        } else {
            return response()->json([
                'status' => true,
                'message' => $state
            ], 200);
        }
    }

    //-------------------------Funciones Municipality-------------------------//

    public function ctl_municipality()
    {
        $ctl_municipality = censo_municipality_v2::all()->where('id_status', 1);
        return response()->json([
            'status' => true,
            'message' => 'Successful response.',
            'data' => $ctl_municipality
        ], 200);
    }

    public function created_municipality(Request $request)
    {
        $rules = [
            'name_municipality' => 'required',
            'id_state' => 'required'
        ];
        $validator = Validator::make($request->input(), $rules);
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->all()
            ]);
        }
        $name_municipality = ucfirst($request->input('name_municipality'));
        $created_municipality = censo_municipality_v2::insert(
            [
                'name_municipality' => $name_municipality,
                'id_state' => $request->id_state
            ]
        );
        if ($created_municipality) {
            return response()->json([
                'status' => true,
                'message' => 'The municipality has been successfully registered.',
            ], 200);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while performing the operation.',
            ], 200);
        }
    }

    public function updated_municipality(Request $request)
    {
        $rules = [
            'id_municipality' => 'required',
            'name_municipality' => 'required',
            'id_state'  => 'required'
        ];
        $validator = Validator::make($request->input(), $rules);
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->all()
            ], 200);
        }
        try {
            $name_municipality = ucfirst($request->input('name_municipality'));
            DB::connection('DevCenso')->update('exec updated_municipality ?,?,?', [
                $request->id_municipality,
                $name_municipality,
                $request->id_state
            ]);
            return response()->json([
                'status' => true,
                'message' => 'Municipality updated successfully'
            ], 200);
        } catch (Exception $cb) {
            return response()->json([
                'status' => false,
                'message' =>  'An error ocurred during query: ' . $cb
            ], 200);
        }
    }

    public function updated_status_municipality(Request $request)
    {
        $rules = [
            'id_municipality' => 'required',
        ];
        $validator = Validator::make($request->input(), $rules);
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->all()
            ], 200);
        }
        try {
            $id_status = censo_municipality_v2::where('id_municipality', $request->id_municipality)->first();
            switch ($id_status->id_status) {
                case 1:
                    censo_municipality_v2::where('id_municipality', $request->id_municipality)->update([
                        'id_status' => 2
                    ]);
                    break;
                case 2:
                    censo_municipality_v2::where('id_municipality', $request->id_municipality)->update([
                        'id_status' => 1
                    ]);
                    break;
            }
            return response()->json([
                'status' => true,
                'message' => 'Municipality status updated successfully'
            ], 200);
        } catch (Exception $cb) {
            return response()->json([
                'status' => false,
                'message' =>  'An error ocurred during query: ' . $cb
            ], 200);
        }
    }

    public function detail_municipality(Request $request)
    {
        $rules = [
            'id_municipality' => 'required',
        ];
        $validator = Validator::make($request->input(), $rules);
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->all()
            ], 200);
        }

        $municipality = DB::connection('DevCenso')->table('ctl_municipality')->where('id_municipality', $request->id_municipality)->first();
        if ($municipality == false) {
            return response()->json([
                'status' => false,
                'message' => 'No results found',
            ], 200);
        } else {
            return response()->json([
                'status' => true,
                'message' => $municipality
            ], 200);
        }
    }

    //-------------------------Funciones Roads-------------------------//

    public function ctl_roads()
    {
        $ctl_roads = censo_roads_v2::all()->where('id_status', 1);
        return response()->json([
            'status' => true,
            'message' => 'Successful response.',
            'data' => $ctl_roads
        ], 200);
    }

    public function created_roads(Request $request)
    {
        $rules = [
            'name_roads' => 'required'
        ];
        $validator = Validator::make($request->input(), $rules);
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->all()
            ]);
        }
        $validate_roads = censo_roads_v2::where([
            'name_roads' => $request->input('name_roads')
        ])->get();
        if (sizeof($validate_roads) == 0) {
            $name_roads = ucfirst($request->input('name_roads'));
            $created_roads = censo_roads_v2::insert(
                [
                    'name_roads' => $name_roads
                ]
            );
            if ($created_roads) {
                return response()->json([
                    'status' => true,
                    'message' => 'The roads has been successfully registered.',
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
                'message' => 'Roads is already registered, verify information.',
            ], 200);
        }
    }

    public function updated_roads(Request $request)
    {
        $rules = [
            'id_roads' => 'required',
            'name_roads' => 'required'
        ];
        $validator = Validator::make($request->input(), $rules);
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->all()
            ], 200);
        }
        try {
            $name_roads = ucfirst($request->input('name_roads'));
            DB::connection('DevCenso')->update('exec updated_roads ?,?', [
                $request->id_roads,
                $name_roads
            ]);
            return response()->json([
                'status' => true,
                'message' => 'Roads updated successfully'
            ], 200);
        } catch (Exception $cb) {
            return response()->json([
                'status' => false,
                'message' =>  'An error ocurred during query: ' . $cb
            ], 200);
        }
    }

    public function updated_status_roads(Request $request)
    {
        $rules = [
            'id_roads' => 'required',
        ];
        $validator = Validator::make($request->input(), $rules);
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->all()
            ], 200);
        }
        try {
            $id_status = censo_roads_v2::where('id_roads', $request->id_roads)->first();
            switch ($id_status->id_status) {
                case 1:
                    censo_roads_v2::where('id_roads', $request->id_roads)->update([
                        'id_status' => 2
                    ]);
                    break;
                case 2:
                    censo_roads_v2::where('id_roads', $request->id_roads)->update([
                        'id_status' => 1
                    ]);
                    break;
            }
            return response()->json([
                'status' => true,
                'message' => 'Roads status updated successfully'
            ], 200);
        } catch (Exception $cb) {
            return response()->json([
                'status' => false,
                'message' =>  'An error ocurred during query: ' . $cb
            ], 200);
        }
    }

    public function detail_roads(Request $request)
    {
        $rules = [
            'id_roads' => 'required',
        ];
        $validator = Validator::make($request->input(), $rules);
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->all()
            ], 200);
        }

        $roads = DB::connection('DevCenso')->table('ctl_roads')->where('id_roads', $request->id_roads)->first();
        if ($roads == false) {
            return response()->json([
                'status' => false,
                'message' => 'No results found',
            ], 200);
        } else {
            return response()->json([
                'status' => true,
                'message' => $roads
            ], 200);
        }
    }

    //-------------------------Funciones Settlements-------------------------//

    public function ctl_settlements()
    {
        $ctl_settlements = censo_settlements_v2::all()->where('id_status', 1);
        return response()->json([
            'status' => true,
            'message' => 'Successful response.',
            'data' => $ctl_settlements
        ], 200);
    }

    public function created_settlements(Request $request)
    {
        $rules = [
            'name_settlements' => 'required'
        ];
        $validator = Validator::make($request->input(), $rules);
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->all()
            ]);
        }
        $validate_settlements = censo_settlements_v2::where([
            'name_settlements' => $request->input('name_settlements')
        ])->get();
        if (sizeof($validate_settlements) == 0) {
            $name_settlements = ucfirst($request->input('name_settlements'));
            $created_settlements = censo_settlements_v2::insert(
                [
                    'name_settlements' => $name_settlements
                ]
            );
            if ($created_settlements) {
                return response()->json([
                    'status' => true,
                    'message' => 'The settlements has been successfully registered.',
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
                'message' => 'Settlements is already registered, verify information.',
            ], 200);
        }
    }

    public function updated_settlements(Request $request)
    {
        $rules = [
            'id_settlements' => 'required',
            'name_settlements' => 'required'
        ];
        $validator = Validator::make($request->input(), $rules);
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->all()
            ], 200);
        }
        try {
            $name_settlements = ucfirst($request->input('name_settlements'));
            DB::connection('DevCenso')->update('exec updated_settlements ?,?', [
                $request->id_roads,
                $name_settlements
            ]);
            return response()->json([
                'status' => true,
                'message' => 'Settlements updated successfully'
            ], 200);
        } catch (Exception $cb) {
            return response()->json([
                'status' => false,
                'message' =>  'An error ocurred during query: ' . $cb
            ], 200);
        }
    }

    public function updated_status_settlements(Request $request)
    {
        $rules = [
            'id_settlements' => 'required',
        ];
        $validator = Validator::make($request->input(), $rules);
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->all()
            ], 200);
        }
        try {
            $id_status = censo_settlements_v2::where('id_settlements', $request->id_settlements)->first();
            switch ($id_status->id_status) {
                case 1:
                    censo_settlements_v2::where('id_settlements', $request->id_settlements)->update([
                        'id_status' => 2
                    ]);
                    break;
                case 2:
                    censo_settlements_v2::where('id_settlements', $request->id_settlements)->update([
                        'id_status' => 1
                    ]);
                    break;
            }
            return response()->json([
                'status' => true,
                'message' => 'Settlements status updated successfully'
            ], 200);
        } catch (Exception $cb) {
            return response()->json([
                'status' => false,
                'message' =>  'An error ocurred during query: ' . $cb
            ], 200);
        }
    }

    public function detail_settlements(Request $request)
    {
        $rules = [
            'id_settlements' => 'required',
        ];
        $validator = Validator::make($request->input(), $rules);
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->all()
            ], 200);
        }

        $settlements = DB::connection('DevCenso')->table('ctl_settlements')->where('id_settlements', $request->id_settlements)->first();
        if ($settlements == false) {
            return response()->json([
                'status' => false,
                'message' => 'No results found',
            ], 200);
        } else {
            return response()->json([
                'status' => true,
                'message' => $settlements
            ], 200);
        }
    }

    //-------------------------Funciones Users-------------------------//

    public function tbl_users()
    {
        $tbl_users = censo_users_v2::all()->where('id_status', 1);
        return response()->json([
            'status' => true,
            'message' => 'Successful response.',
            'data' => $tbl_users
        ], 200);
    }

    public function created_users(Request $request)
    {
        $rules = [
            'name_user' => 'required',
            'last_name_user' => 'required',
            'mother_last_name_user' => 'required',
            'email' => 'required',
            'id_lada' => 'required',
            'cell_phone' => 'required',
            'id_state' => 'required',
            'picture_profile' => 'required',
            'id_role' => 'required'
        ];
        $validator = Validator::make($request->input(), $rules);
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->all()
            ]);
        }
        $data = json_decode($request->getContent());

        $validate_user = censo_users_v2::orwhere([
            'email' => $data->email
        ])
            ->orwhere(['cell_phone' => $data->cell_phone])->get();
        if (sizeof($validate_user) == 0) {
            $name_user = ucfirst($data->name_user);
            $last_name_user = ucfirst($data->last_name_user);
            $mother_last_name_user = ucfirst($data->mother_last_name_user);
            $image_64F = $data->picture_profile;
            $extends_picture = explode('/', explode(':', substr($image_64F, 0, strpos($image_64F, ';')))[1])[1];
            $replace = substr($image_64F, 0, strpos($image_64F, ',') + 1);
            $image = str_replace($replace, '', $image_64F);
            $image = str_replace(' ', '+', $image);
            $imageNameF = 'CensoApp/usuarios/' . $name_user . '/picture_user' . $name_user . '_' . uniqid() . '.' . $extends_picture;

            Storage::disk('public')->put($imageNameF, base64_decode($image));
            $url_profile_user = $imageNameF;
            $created_user = censo_users_v2::insert(
                [
                    'name_user' => $name_user,
                    'last_name_user' => $last_name_user,
                    'mother_last_name_user' => $mother_last_name_user,
                    'email' => $data->email,
                    'id_lada' => $data->id_lada,
                    'cell_phone' => $data->cell_phone,
                    'id_state' => $data->id_state,
                    'picture_profile' => $url_profile_user,
                    'id_role' => $data->id_role
                ]
            );
            if ($created_user) {
                send_email_global::$empresa = 'CensoApp';
                $credentials = credentials_global::created_credentials($request->name_user);
                $name_complete = ucwords(strtolower($request->name_user)) . ' ' . ucwords(strtolower($request->last_name)) . ' ' . ucwords(strtolower($request->mother_last_name));
                $data = [
                    'name_complete' => $name_complete,
                    'email'    => $request->email,
                    'username' => $credentials['user_name'],
                    'password' => $credentials['password']
                ];
                $password_user = md5($credentials['password']);
                $email = send_email_global::send_email_credentials($data);
                if ($email['status'] == true) {
                    $last_customer_id = censo_users_v2::where('email', $request->email)->first();
                    $id_user = $last_customer_id->id_users;
                    $created_credentials = censo_credentials_v2::insert([
                        'username' => $credentials['user_name'],
                        'password' => $password_user,
                        'id_user' => $id_user
                    ]);
                    if ($created_credentials) {
                        return response()->json([
                            'status' => true,
                            'message' => 'The user has been successfully registered.'
                        ], 200);
                    } else {
                        return response()->json([
                            'status' => false,
                            'message' =>  'An error ocurred during query created credentials.'
                        ], 200);
                    }
                } else {
                    return response()->json([
                        'status' => false,
                        'message' =>  'An error occurred, retry later.'
                    ], 200);
                }
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'An error occurred while performing the operation.',
                ], 200);
            }
        } else {
            return response()->json([
                'status' => false,
                'message' => 'User is already registered, verify information.',
            ], 200);
        }
    }

    public function updated_user(Request $request)
    {
        $rules = [
            'id_users' => 'required',
            'name_user' => 'required',
            'last_name_user' => 'required',
            'mother_last_name_user' => 'required',
            'email' => 'required',
            'id_lada' => 'required',
            'cell_phone' => 'required',
            'id_state' => 'required',
            'picture_profile' => 'required'
        ];
        $validator = Validator::make($request->input(), $rules);
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->all()
            ], 200);
        }
        try {
            $data = json_decode($request->getContent());
            $name_user = ucfirst($data->name_user);
            $last_name_user = ucfirst($data->last_name_user);
            $mother_last_name_user = ucfirst($data->mother_last_name_user);
            $image_64F = $data->picture_profile;
            $extends_picture = explode('/', explode(':', substr($image_64F, 0, strpos($image_64F, ';')))[1])[1];
            $replace = substr($image_64F, 0, strpos($image_64F, ',') + 1);
            $image = str_replace($replace, '', $image_64F);
            $image = str_replace(' ', '+', $image);
            $imageNameF = 'CensoApp/usuarios/' . $name_user . '/picture_user_' . $name_user . '_' . uniqid() . '.' . $extends_picture;
            Storage::disk('public')->put($imageNameF, base64_decode($image));
            $url_profile_user = $imageNameF;
            DB::connection('DevCenso')->update('exec updated_user ?,?,?,?,?,?,?,?,?', [
                $request->id_users,
                $name_user,
                $last_name_user,
                $mother_last_name_user,
                $request->email,
                $request->id_lada,
                $request->cell_phone,
                $request->id_state,
                $url_profile_user
            ]);
            return response()->json([
                'status' => true,
                'message' => 'User updated successfully'
            ], 200);
        } catch (Exception $cb) {
            return response()->json([
                'status' => false,
                'message' =>  'An error ocurred during query: ' . $cb
            ], 200);
        }
    }

    public function updated_status_user(Request $request)
    {
        $rules = [
            'id_users' => 'required',
        ];
        $validator = Validator::make($request->input(), $rules);
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->all()
            ], 200);
        }
        try {
            $id_status = censo_users_v2::where('id_users', $request->id_users)->first();
            switch ($id_status->id_status) {
                case 1:
                    censo_users_v2::where('id_users', $request->id_users)->update([
                        'id_status' => 2
                    ]);
                    break;
                case 2:
                    censo_users_v2::where('id_users', $request->id_users)->update([
                        'id_status' => 1
                    ]);
                    break;
            }
            return response()->json([
                'status' => true,
                'message' => 'User status updated successfully'
            ], 200);
        } catch (Exception $cb) {
            return response()->json([
                'status' => false,
                'message' =>  'An error ocurred during query: ' . $cb
            ], 200);
        }
    }

    public function detail_users(Request $request)
    {
        $rules = [
            'id_users' => 'required',
        ];
        $validator = Validator::make($request->input(), $rules);
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->all()
            ], 200);
        }

        $user = DB::connection('DevCenso')->table('tbl_users')->where('id_users', $request->id_users)->first();
        if ($user == false) {
            return response()->json([
                'status' => false,
                'message' => 'No results found',
            ], 200);
        } else {
            return response()->json([
                'status' => true,
                'message' => $user
            ], 200);
        }
    }

    //-------------------------Funciones Credentials-------------------------//

    public function tbl_credentials()
    {
        $tbl_credentials = censo_credentials_v2::all()->where('id_status', 1);
        return response()->json([
            'status' => true,
            'message' => 'Successful response.',
            'data' => $tbl_credentials
        ], 200);
    }

    public function recover_password(Request $request)
    {
        $rules = [
            'email' => 'required'
        ];
        $validator = Validator::make($request->input(), $rules);
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->all()
            ], 200);
        }
        try {
            $validate_user = censo_users_v2::where([
                'email' => $request->email
            ])->get();
            if (sizeof($validate_user) == 0) {
                return response()->json([
                    'status' => false,
                    'message' => 'User not found, verify information',
                ], 200);
            } else {
                send_email_global::$empresa = 'CensoApp - Recuperación de cuenta';
                $name_user = censo_users_v2::where('email', $request->email)->first();
                $data_user = $name_user;
                $name_user =  $name_user->name_user;
                $name_complete = ucwords(strtolower($data_user->name_user)) . ' ' . ucwords(strtolower($data_user->last_name_user)) . ' ' . ucwords(strtolower($data_user->mother_last_name_user));
                $credentials = credentials_global::created_credentials($name_user);
                $data = [
                    'name_complete' => $name_complete,
                    'email'    => $request->email,
                    'username' => $credentials['user_name'],
                    'password' => $credentials['password']
                ];
                $email = send_email_global::send_email_credentials($data);
                if ($email['status'] == true) {
                    try {
                        DB::connection('DevCenso')->update('exec recover_password ?,?,?', [
                            $credentials['user_name'],
                            $credentials['password_token'],
                            $data_user->id_users
                        ]);
                        return response()->json([
                            'status' => true,
                            'message' => 'Credential recover successfully'
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
                        'message' =>  'An error occurred, retry later.'
                    ], 200);
                }
            }
        } catch (Exception $cb) {
            return response()->json([
                'status' => false,
                'message' =>  'An error ocurred during query: ' . $cb
            ], 200);
        }
    }

    public function updated_status_credentials(Request $request)
    {
        $rules = [
            'id_user' => 'required',
        ];
        $validator = Validator::make($request->input(), $rules);
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->all()
            ], 200);
        }
        try {
            $id_status = censo_credentials_v2::where('id_user', $request->id_user)->first();
            switch ($id_status->id_status) {
                case 1:
                    censo_credentials_v2::where('id_user', $request->id_user)->update([
                        'id_status' => 2
                    ]);
                    censo_users_v2::where('id_users', $request->id_user)->update([
                        'id_status' => 2
                    ]);
                    break;
                case 2:
                    censo_credentials_v2::where('id_user', $request->id_user)->update([
                        'id_status' => 1
                    ]);
                    censo_users_v2::where('id_users', $request->id_user)->update([
                        'id_status' => 1
                    ]);
                    break;
            }
            return response()->json([
                'status' => true,
                'message' => 'Credentials status updated successfully'
            ], 200);
        } catch (Exception $cb) {
            return response()->json([
                'status' => false,
                'message' =>  'An error ocurred during query: ' . $cb
            ], 200);
        }
    }

    public function detail_credentials(Request $request)
    {
        $rules = [
            'id_user' => 'required',
        ];
        $validator = Validator::make($request->input(), $rules);
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->all()
            ], 200);
        }

        $user = DB::connection('DevCenso')->table('tbl_credentials')->where('id_user', $request->id_user)->first();
        if ($user == false) {
            return response()->json([
                'status' => false,
                'message' => 'No results found',
            ], 200);
        } else {
            return response()->json([
                'status' => true,
                'message' => $user
            ], 200);
        }
    }

    //-------------------------Funciones Device User-------------------------//

    public function tbl_device_user()
    {
        $tbl_device_user = censo_device_user_v2::all()->where('id_status', 1);
        return response()->json([
            'status' => true,
            'message' => 'Successful response.',
            'data' => $tbl_device_user
        ], 200);
    }

    public function created_device_user(Request $request)
    {
        $rules = [
            'androidSDK' => 'required',
            'iOSVersion' => 'required',
            'manufacturer' => 'required',
            'model' => 'required',
            'nameDevice' => 'required',
            'operatingSystem' => 'required',
            'osVersion' => 'required',
            'platform' => 'required',
            'id_user' => 'required'
        ];
        $validator = Validator::make($request->input(), $rules);
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->all()
            ]);
        }
        $validate_censo_device = censo_device_user_v2::where([
            'id_user' => $request->input('id_user')
        ])->get();
        if (sizeof($validate_censo_device) == 0) {
            $created_device = censo_device_user_v2::insert(
                [
                    'androidSDK' => $request->androidSDK,
                    'iOSVersion' => $request->iOSVersion,
                    'manufacturer' => $request->manufacturer,
                    'model' => $request->model,
                    'nameDevice' => $request->nameDevice,
                    'operatingSystem' => $request->operatingSystem,
                    'osVersion' => $request->osVersion,
                    'platform' => $request->platform,
                    'id_user' => $request->id_user
                ]
            );
            if ($created_device) {
                return response()->json([
                    'status' => true,
                    'message' => 'The device user has been successfully registered.',
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
                'message' => 'Device user is already registered, verify information.',
            ], 200);
        }
    }

    public function updated_device_user(Request $request)
    {
        $rules = [
            'androidSDK' => 'required',
            'iOSVersion' => 'required',
            'manufacturer' => 'required',
            'model' => 'required',
            'nameDevice' => 'required',
            'operatingSystem' => 'required',
            'osVersion' => 'required',
            'platform' => 'required',
            'id_user' => 'required'
        ];
        $validator = Validator::make($request->input(), $rules);
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->all()
            ], 200);
        }
        try {
            DB::connection('DevCenso')->update('exec updated_device_user ?,?,?,?,?,?,?,?,?', [
                $request->androidSDK,
                $request->iOSVersion,
                $request->manufacturer,
                $request->model,
                $request->nameDevice,
                $request->operatingSystem,
                $request->osVersion,
                $request->platform,
                $request->id_user
            ]);
            return response()->json([
                'status' => true,
                'message' => 'Device user updated successfully'
            ], 200);
        } catch (Exception $cb) {
            return response()->json([
                'status' => false,
                'message' =>  'An error ocurred during query: ' . $cb
            ], 200);
        }
    }

    public function updated_status_device_user(Request $request)
    {
        $rules = [
            'id_user' => 'required',
        ];
        $validator = Validator::make($request->input(), $rules);
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->all()
            ], 200);
        }
        try {
            $id_status = censo_device_user_v2::where('id_user', $request->id_user)->first();
            switch ($id_status->id_status) {
                case 1:
                    censo_device_user_v2::where('id_user', $request->id_user)->update([
                        'id_status' => 2
                    ]);
                    break;
                case 2:
                    censo_device_user_v2::where('id_user', $request->id_user)->update([
                        'id_status' => 1
                    ]);
                    break;
            }
            return response()->json([
                'status' => true,
                'message' => 'Device user status updated successfully'
            ], 200);
        } catch (Exception $cb) {
            return response()->json([
                'status' => false,
                'message' =>  'An error ocurred during query: ' . $cb
            ], 200);
        }
    }

    public function detail_device_user(Request $request)
    {
        $rules = [
            'id_user' => 'required',
        ];
        $validator = Validator::make($request->input(), $rules);
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->all()
            ], 200);
        }

        $user = DB::connection('DevCenso')->table('tbl_device_user')->where('id_user', $request->id_user)->first();
        if ($user == false) {
            return response()->json([
                'status' => false,
                'message' => 'No results found',
            ], 200);
        } else {
            return response()->json([
                'status' => true,
                'message' => $user
            ], 200);
        }
    }

    //-------------------------Funciones Registered Businesses-------------------------//

    public function tbl_registered_businesses()
    {
        $tbl_registered_businesses = censo_registered_businesses_v2::all()->where('id_status', 1);
        return response()->json([
            'status' => true,
            'message' => 'Successful response.',
            'data' => $tbl_registered_businesses
        ], 200);
    }


    public function created_businesses(Request $request)
    {
        $rules = [
            'id_type_business' => 'required',
            'establishment_name' => 'required',
            'full_address' => 'required',
            'first_street' => 'required',
            'second_street' => 'required',
            'outdoor_number' => 'required',
            'postal_code' => 'required',
            'id_states' => 'required',
            'id_municipality' => 'required',
            'picture_businesses' => 'required',
            'latitude' => 'required',
            'longitude' => 'required',
            'id_user' => 'required'
        ];
        $validator = Validator::make($request->input(), $rules);
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->all()
            ]);
        }
        $data = json_decode($request->getContent());
        $validate_business = censo_registered_businesses_v2::orwhere([
            'establishment_name' => $request->input('establishment_name')
        ])->orwhere([
            'full_address' => $request->input('full_address')
        ])->get();
        if (sizeof($validate_business) == 0) {
            $name_business = $data->establishment_name;
            $image_64F = $data->picture_businesses;
            $extends_picture = explode('/', explode(':', substr($image_64F, 0, strpos($image_64F, ';')))[1])[1];
            $replace = substr($image_64F, 0, strpos($image_64F, ',') + 1);
            $image = str_replace($replace, '', $image_64F);
            $image = str_replace(' ', '+', $image);
            $imageNameB = 'CensoApp/negocios/' . $name_business . '/picture_negocio_' . $name_business . '_' . uniqid() . '.' . $extends_picture;
            Storage::disk('public')->put($imageNameB, base64_decode($image));
            $url_image_business = $imageNameB;
            $created_device = censo_registered_businesses_v2::insert(
                [
                    'id_type_business' => $data->id_type_business,
                    'establishment_name' => $data->establishment_name,
                    'full_address' => $data->full_address,
                    'first_street' => $data->first_street,
                    'second_street' => $data->second_street,
                    'outdoor_number' => $data->outdoor_number,
                    'postal_code' => $data->postal_code,
                    'id_states' => $data->id_states,
                    'id_municipality' => $data->id_municipality,
                    'picture_businesses' => $url_image_business,
                    'latitude' => $data->latitude,
                    'longitude' => $data->longitude,
                    'id_user' => $data->id_user
                ]
            );
            if ($created_device) {
                return response()->json([
                    'status' => true,
                    'message' => 'The businesses has been successfully registered.',
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
                'message' => 'Businesses is already registered, verify information.',
            ], 200);
        }
    }

    public function detail_business(Request $request)
    {
        $rules = [
            'id_registered_businesses' => 'required',
        ];
        $validator = Validator::make($request->input(), $rules);
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->all()
            ], 200);
        }

        $user = DB::connection('DevCenso')->table('tbl_registered_businesses')->where('id_registered_businesses', $request->id_registered_businesses)->first();
        if ($user == false) {
            return response()->json([
                'status' => false,
                'message' => 'No results found',
            ], 200);
        } else {
            return response()->json([
                'status' => true,
                'message' => $user
            ], 200);
        }
    }

    public function business_users(Request $request)
    {
        $rules = [
            'id_user' => 'required',
        ];
        $validator = Validator::make($request->input(), $rules);
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->all()
            ], 200);
        }

        $user = DB::connection('DevCenso')->table('tbl_registered_businesses')->where('id_user', $request->id_user)->get();
        if ($user == false) {
            return response()->json([
                'status' => false,
                'message' => 'No results found',
            ], 200);
        } else {
            return response()->json([
                'status' => true,
                'message' => $user
            ], 200);
        }
    }
    //-------------------------Funciones Comissions-------------------------//

    public function tbl_commissions()
    {
        $tbl_commissions = censo_commissions_v2::all()->where('id_status', 1);
        return response()->json([
            'status' => true,
            'message' => 'Successful response.',
            'data' => $tbl_commissions
        ], 200);
    }

    public function comissions_user()
    {
        $tbl_commissions = censo_commissions_v2::all()->where('id_status', 1);
        return response()->json([
            'status' => true,
            'message' => 'Successful response.',
            'data' => $tbl_commissions
        ], 200);
    }

    //-------------------------Funciones Validación Servidor-------------------------//
    public function validate_service()
    {
        $tbl_commissions = censo_status_v2::all()->where('id_status', 7);
        return response()->json([
            'status' => true,
            'message' => 'Successful connection.',
            'data' => $tbl_commissions
        ], 200);
    }
    //-------------------------Funciones Iniciar sesión-------------------------//
    public function login_user(Request $request)
    {
        $rules = [
            'username' => 'required',
            'password' => 'required'
        ];
        $validator = Validator::make($request->input(), $rules);
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->all()
            ]);
        }
        $password_user = md5($request->input('password'));

        $validate_credentials = DB::connection('DevCenso')->table('view_users_global')
            ->where('username', $request->input('username'))
            ->where('password', $password_user)->get();
        if (sizeof($validate_credentials) == 0) {
            return response()->json([
                'status' => false,
                'message' => 'Incorrect username and password.',
            ], 200);
        } else {
            foreach ($validate_credentials as $data) {
                $id_users = $data->id_users;
                $name_user = $data->name_user;
                $last_name_user = $data->last_name_user;
                $mother_last_name_user = $data->mother_last_name_user;
                $email = $data->email;
                $id_lada = $data->id_lada;
                $cell_phone = $data->cell_phone;
                $id_state = $data->id_state;
                $picture_profile = $data->picture_profile;
                $name_status = $data->name_status;
                $name_role = $data->name_role;
                $id_status = $data->id_status;
                $lada_cell_phone = $data->lada_cell_phone;
                $name_state = $data->name_state;
            }
            $data_user = [
                'id_users' => $id_users,
                'name_user' => $name_user,
                'last_name_user' => $last_name_user,
                'mother_last_name_user' => $mother_last_name_user,
                'email' => $email,
                'id_lada' => $id_lada,
                'cell_phone' => $cell_phone,
                'id_state' => $id_state,
                'picture_profile' => $picture_profile,
                'name_status' => $name_status,
                'name_role' => $name_role,
                'id_status' => $id_status,
                'lada_cell_phone' => $lada_cell_phone,
                'name_state' => $name_state,
            ];
            if ($id_status == 2) {
                return response()->json([
                    'status' => false,
                    'message' => 'Blocked account.',
                ], 200);
            } else if ($id_status == 8) {
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
}
