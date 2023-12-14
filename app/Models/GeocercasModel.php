<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GeocercasModel extends Model
{
    use HasFactory;
    protected $connection = 'Apprisa';
    protected $table = "tbl_geocerca";

    public $timestamps = false;
}
