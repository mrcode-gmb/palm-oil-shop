<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'user_id',
        'quantity',
        'selling_price_per_unit',
        'cost_price_per_unit',
        'total_amount',
        'total_cost',
        'profit',
        'customer_name',
        'customer_phone',
        'sale_date',
        'notes',
    ];

    protected $casts = [
        'sale_date' => 'date',
    ];

    /**
     * Get the product that was sold
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the user who made this sale
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
