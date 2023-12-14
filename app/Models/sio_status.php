<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class sio_status extends Model
{
    use HasFactory;
    protected $connection = 'DevSio';
    protected $table = "ctl_status";
}
