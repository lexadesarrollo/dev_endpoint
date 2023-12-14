<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ZonasModel extends Model
{
    use HasFactory;
    protected $connection = 'Apprisa';
    protected $table = "tbl_zona";

    public $timestamps = false;
}
