<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    use HasFactory;

    protected $fillable = [
        "product_id",
        "user_id",
        "supplier_name",
        "supplier_phone",
        "quantity",
        "purchase_price",
        "total_cost",
        "selling_price",
        "seller_profit",
        "purchase_date",
        "notes",
    ];

    protected $casts = [
        'purchase_date' => 'date',
    ];

    public function addStock($quantity)
    {
        $this->current_stock += $quantity;
        $this->save();
    }

    /**
     * Update stock after sale
     */
    public function reduceStock($quantity)
    {
        $this->quantity -= $quantity;
        $this->save();
    }
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

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    
    public function expenses()
    {
        return $this->hasMany(Expenses::class);
    }
}
