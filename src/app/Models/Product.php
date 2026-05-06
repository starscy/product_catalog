<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; // ← добавьте этот импорт
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Продукт
 */
class Product extends Model
{
    use SoftDeletes, HasFactory;

    protected $fillable = ['name', 'description', 'price', 'category_id'];

    protected $casts = [
        'price' => 'decimal:2',
        'deleted_at' => 'datetime',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}
