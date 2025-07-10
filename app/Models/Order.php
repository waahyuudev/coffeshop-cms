<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'table_number',
        'customer_name',
        'status',
        'payment_status',
        'channel_payment',
        'total_price',
    ];

    public function scopeFilter(Builder $query, array $filters): void
    {
        $query->when($filters['q'] ?? false, function ($query, $search) {
            return $query->where('order_id', 'like', '%' . $search . '%')
                ->orWhere('table_number', 'like', '%' . $search . '%')
                ->orWhere('customer_name', 'like', '%' . $search . '%');
        });
    }

    // Relasi: satu order punya banyak item
    public function items()
    {
        return $this->hasMany(OrderItem::class, 'order_id', 'id');
    }
}
