<?php

namespace App\Http\Controllers;

use App\Models\censo_status_v2;
use Illuminate\Http\Request;

class censo_controller_v2 extends Controller
{
    protected $connection = 'DevCenso';
    public function ctl_status()
    {
        $ctl_status = censo_status_v2::all();
        return response()->json([
            'status' => true,
            'message' => 'Successful query.',
            'data' => $ctl_status
        ], 200);
    }
}
