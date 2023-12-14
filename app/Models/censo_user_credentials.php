<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class censo_user_credentials extends Model
{
    use HasFactory;
    protected $connection = 'Censo';
    protected $table = "tbl_user_credentials"; 
}
