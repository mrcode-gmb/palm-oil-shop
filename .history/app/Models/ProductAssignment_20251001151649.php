<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'purchase_id',
        'assigned_quantity',
        'sold_quantity',
        'returned_quantity',
        'expected_selling_price',
        'commission_rate',
        'commission_amount',
        'actual_total_sales',
        'profit_collected',
        'status',
        'assigned_date',
        'due_date',
        'returned_date',
        'notes',
    ];

    protected $casts = [
        'assigned_date' => 'date',
        'due_date' => 'date',
        'returned_date' => 'date',
    ];

    /**
     * Get the staff member assigned to this product
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the purchase/product assigned
     */
    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }

    /**
     * Get all sales made from this assignment
     */
    public function sales()
    {
        return $this->hasMany(Sale::class, 'assignment_id');
    }

    /**
     * Calculate remaining quantity
     */
    public function getRemainingQuantityAttribute()
    {
        return $this->assigned_quantity - $this->sold_quantity - $this->returned_quantity;
    }

    /**
     * Calculate expected profit
     */
    public function getExpectedProfitAttribute()
    {
        $costPrice = $this->purchase->cost_price_per_unit ?? 0;
        return ($this->expected_selling_price - $costPrice) * $this->assigned_quantity;
    }

    /**
     * Calculate actual profit
     */
    public function getActualProfitAttribute()
    {
        $costPrice = $this->purchase->cost_price_per_unit ?? 0;
        return $this->actual_total_sales - ($costPrice * $this->sold_quantity);
    }

    /**
     * Check if assignment is overdue
     */
    public function isOverdue()
    {
        return $this->due_date && $this->due_date->isPast() && $this->status !== 'completed';
    }

    public function purchase()
    {
        return $this->hasMany(ProductAssignment::class);
    }
}
