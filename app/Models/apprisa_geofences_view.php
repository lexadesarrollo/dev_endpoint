<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class apprisa_geofences_view extends Model
{
    use HasFactory;

    protected $connection = "DevApprisa";
    
    protected $table = "geofences_view";
}
