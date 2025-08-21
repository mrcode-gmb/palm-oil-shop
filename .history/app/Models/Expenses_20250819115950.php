<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Expenses extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'date',
        'name',
        'today_profit',
        'amount',
        'today_net_profit',
        'notes',
    ];


    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
