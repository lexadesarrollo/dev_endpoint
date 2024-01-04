<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class apprisa_roles_view extends Model
{
    use HasFactory;

    protected $connection = 'DevApprisa';
    
    protected $table = 'roles_view';
}
