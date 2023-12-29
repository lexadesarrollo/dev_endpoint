<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class apprisa_user_credential extends Model
{
    use HasFactory;
    protected $connection = "DevApprisa";

    protected $table = "users_global_view";
}
