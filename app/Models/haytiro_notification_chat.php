<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class haytiro_notification_chat extends Model
{
    use HasFactory;
    protected $connection = 'HayTiro';
    protected $table = "tbl_chats";
}
