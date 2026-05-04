<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TreePrice extends Model
{
    use HasFactory;

    protected $table = 'tree_prices';

    protected $fillable = [
        'price',
        'is_active', // 1 = Active, 0 = Inactive
    ];

    /**
     * Scope a query to only include active prices.
     * Usage: TreePrice::active()->first();
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', 1);
    }
}
