<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'first_name',
        'last_name',
        'shipping_phone',
        'shipping_street',
        'shipping_city',
        'shipping_state',
        'shipping_postal_code',
        'status',
        'payment_status',
        'payment_method',
        'total_amount',
        'stripe_payment_intent_id',
        'stripe_client_secret',
        'stripe_payment_metadata',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'stripe_payment_metadata' => 'array',
        ];
    }
}
