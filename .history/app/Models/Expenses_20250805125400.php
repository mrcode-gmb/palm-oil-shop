<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Expenses extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'product_id',
        'name',
        'amount',
        'date',
        'notes',
    ];


    public function product()
    {
        return $this->belongsTo(Product::class);
    }
    public function user()
    {
        return $this->belon
    }
}
