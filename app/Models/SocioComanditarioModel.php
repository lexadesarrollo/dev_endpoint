<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SocioComanditarioModel extends Model
{
    use HasFactory;
    protected $connection = 'DevSio';
    protected $table = 'tbl_socioComanditario';
    public $timestamps = false;
}
