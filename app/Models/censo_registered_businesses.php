<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class censo_registered_businesses extends Model
{
    use HasFactory;
    protected $connection = 'Censo';
    protected $table = "tbl_registered_businesses"; 
}
