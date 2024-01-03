<?php

namespace App\Http\Controllers;

use App\Models\censo_users_v2;
use Illuminate\Http\Request;

class holaController extends Controller
{
    public function welcome(){
        $users = censo_users_v2::all();

        return view('welcome', compact('users'));
    }
}
