<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class haytiro_type_services extends Model
{
    use HasFactory;
    protected $connection = 'HayTiro';
    protected $table = "tbl_type_services";
}
