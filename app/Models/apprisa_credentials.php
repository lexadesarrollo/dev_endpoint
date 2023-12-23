<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class apprisa_credentials extends Model
{
    use HasFactory;

    protected $connection = "Apprisa";

    protected $table = "tbl_credentials";
}
