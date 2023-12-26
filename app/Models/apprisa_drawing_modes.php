<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class apprisa_drawing_modes extends Model
{
    use HasFactory;

    protected $connection = "Apprisa";
    
    protected $table = "ctl_drawing_modes";
}
