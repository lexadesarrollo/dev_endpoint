<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class censo_user_device extends Model
{
    use HasFactory;
    protected $connection = 'Censo';
    protected $guarded = [];
    protected $table = "tbl_user_device"; 
}
