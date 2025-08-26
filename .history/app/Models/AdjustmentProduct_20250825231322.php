<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdjustmentProduct extends Model
{
    use HasFactory;

    protected $fillable = [
        "purchase_id",
        "adjustment",
        "reason",
    ];

    public function purchase()
    {
        return $this->belongsTo(Purchase)
    }
}
