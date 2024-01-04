<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class apprisa_ladas_view extends Model
{
    use HasFactory;

    protected $connection = 'DevApprisa';
    
    protected $table = 'ladas_view';
}
