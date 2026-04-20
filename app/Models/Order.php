<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class Order extends Model implements Auditable
{
    /** @use HasFactory<\Database\Factories\OrderFactory> */
    use HasFactory, \OwenIt\Auditing\Auditable;

    protected $fillable = [
        'user_id',
        'total_amount',
        'status',
        'shipping_name',
        'shipping_address',
        'shipping_province',
        'shipping_city',
        'shipping_barangay',
        'shipping_postal_code',
        'shipping_street',
        'shipping_building_number',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }
}
