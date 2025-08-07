<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Expenses extends Model
{
    use HasFactory;

    protected $fillable = [
        user_id')->constrained()->onDelete('cascade'); // Linked user
            product_id')->constrained()->onDelete('cascade'); // Linked user
            $table->string('name'); // Expense name
            $table->decimal('amount', 15, 2); // Expense amount
            $table->date('date'); // Expense date
            $table->text('notes')->nullable(); // Optional notes
            $table->timestamps(); // created_at & updated_at
    ];
}
