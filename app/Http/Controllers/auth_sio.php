<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

use Illuminate\Http\Request;

class auth_sio extends Controller
{
    public function create(Request $request)
    {
        $rules = [
            'name' => 'required|string|max:100',
            'email' => 'required|string|email|max:100',
            'password' => 'required|string|min:8'
        ];
        $validator = Validator::make($request->input(), $rules);
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->all()
            ], 200);
        }
        $user = User::create([
            'name' => $request->name, 'email' => $request->email,
            'password' => Hash::make($request->password)
        ]);
        return response()->json([
            'status' => true,
            'message' => 'User created successfully',
            'token' => $user->createToken('Api token')->plainTextToken
        ], 200);
    }

    public function login(Request $request)
    {
        $rules = [
            'email' => 'required|string|max:100',
            'password' => 'required|string'
        ];
        $validator = Validator::make($request->input(), $rules);
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->all()
            ], 200);
        }
        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'status' => false,
                'message' => ['Unauthorized']
            ], 200);
        }
        $user = User::where('email', $request->email)->first();
        return response()->json([
            'status' => true,
            'message' => 'User logged in successfully',
            'data' => $user,
            'token' => $user->createToken('Api token')->plainTextToken
        ], 200);
    }

    public function logout(){
        auth()->user()->tokens()->delete();
        return response()->json([
            'status' => true,
            'message' => 'User logged out successfully' 
        ], 200);
    }

    public function סימען(Request $request){
        $rules = [
            'שליסל' => 'required|string|max:100'
        ];
        $validator = Validator::make($request->input(), $rules);
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->all()
            ], 200);
        }



    }
}
