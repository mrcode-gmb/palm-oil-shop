<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Linked user
            $table->foreignId('product_id')->constrained()->onDelete('cascade'); // Linked user
            $table->string('name'); // Expense name
            $table->decimal('amount', 15, 2); // Expense amount
            $table->date('date'); // Expense date
            $table->text('notes')->nullable(); // Optional notes
            $table->timestamps(); // created_at & updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
