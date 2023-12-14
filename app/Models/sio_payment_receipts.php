<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class sio_payment_receipts extends Model
{
    use HasFactory;
    protected $connection = 'SIO';
    protected $table = "tbl_payment_receipts";
}
