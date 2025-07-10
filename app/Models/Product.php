<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'category',
        'price',
        'image_path',
        'is_available',
    ];

    public function scopeFilter(Builder $query, array $filters): void
    {
        $query->when($filters['q'] ?? false, function ($query, $search) {
            return $query->where('name', 'like', '%' . $search . '%');
        });
        $query->when(isset($filters['isAvailable']), function ($query) use ($filters) {
            return $query->where('is_available', $filters['isAvailable']);
        });
        $query->when($filters['category'] ?? false, function ($query, $category) {
            return $query->where('category', $category);
        });
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class, 'product_id', 'id');
    }
}
