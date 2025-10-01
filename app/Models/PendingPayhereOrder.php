<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PendingPayhereOrder extends Model
{
    protected $fillable = [
        'temp_order_id',
        'user_id',
        'order_data'
    ];

    protected $casts = [
        'order_data' => 'array'
    ];
}