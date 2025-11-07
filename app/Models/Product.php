<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'unit_type',
        'current_stock',
        'selling_price',
        'seller_profit',
        'description',
        'purchase_price',
        'business_id',
    ];

    /**
     * Get purchases for this product
     */
    public function purchase()
    {
        return $this->hasMany(Purchase::class);
    }


    public function expensive()
    {
        return $this->hasMany(Expenses::class);
    }
    /**
     * Get sales for this product
     */
    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    /**
     * Get average cost price for profit calculation
     */
    public function getAverageCostPrice()
    {
        $totalCost = $this->purchases()->sum('total_cost');
        $totalQuantity = $this->purchases()->sum('quantity');
        
        return $totalQuantity > 0 ? $totalCost / $totalQuantity : 0;
    }

    /**
     * Update stock after purchase
     */
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
     * Get the business this product belongs to
     */
    public function business()
    {
        return $this->belongsTo(Business::class);
    }
}
