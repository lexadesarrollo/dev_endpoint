<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class sio_role extends Model
{
    use HasFactory;
    protected $connection = 'DevSio';
    protected $table = "ctl_role";
}
