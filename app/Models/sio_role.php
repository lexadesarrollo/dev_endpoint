<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class sio_role extends Model
{
    use HasFactory;
    protected $connection = 'SIO';
    protected $table = "ctl_role";
}
