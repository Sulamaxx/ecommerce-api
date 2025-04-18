<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'user_id',
        'shipping_address',
        'payment_method',
        'status',
        'total',
        'discount',
        'tax',
        'shipping_rate',
    ];

    /**
     * Define the relationship to the User.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Define the relationship to the OrderItems.
     */
    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }
}
