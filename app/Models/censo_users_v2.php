<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class censo_users_v2 extends Model
{
    use HasFactory;
    protected $connection = "DevCenso";
    protected $table = "tbl_users";
}
