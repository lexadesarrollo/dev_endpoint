<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class censo_types_business extends Model
{
    use HasFactory;
    protected $connection = 'Censo';
    protected $table = "ctl_types_business"; 
}
