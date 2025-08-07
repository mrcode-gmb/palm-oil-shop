<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Expenses extends Model
{
    use HasFactory;

    protected $fillable = [
        user_id
            product_id')->constrained()->onDelete('cascade'); // Linked user
            'name'); // Expense name
            'amount', 15, 2); // Expense amount
            'date'); // Expense date
            notes'
    ];
}
