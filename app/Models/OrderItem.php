<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'product_id',
        'quantity',
        'total_price',
        'subtotal_price',
        'note',
    ];

    // Relasi: item ini milik satu order
    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id', 'id');
    }

    // Relasi: item ini adalah salah satu produk
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'id')->select(['id', 'name', 'image_path']);
    }
}
