<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class apprisa_type_vehicle_view extends Model
{
    use HasFactory;

    protected $connection = 'DevApprisa';
    
    protected $table = 'type_vehicle_view';
}
