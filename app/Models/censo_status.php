<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class censo_status extends Model
{
    use HasFactory;
    protected $connection = 'Censo';
    protected $table = "ctl_status"; 
}
