<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'product_id',
        'supplier_name',
        'supplier_phone',
        'quantity',
        'purchase_price',
        'selling_price',
        'seller_profit',
        'total_cost',
        'purchase_date',
        'notes',
    ];

    protected $casts = [
        'purchase_date' => 'date',
    ];

    /**
     * Get the product that was purchased
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the user who recorded this purchase
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function expenses()
    {
        return $this->hasMany(Expenses::class);
    }
}
