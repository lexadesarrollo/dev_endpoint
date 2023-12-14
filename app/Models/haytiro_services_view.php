<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class haytiro_services_view extends Model
{
    use HasFactory;
    protected $connection = 'HayTiro';
    protected $table = "services_view";
}
