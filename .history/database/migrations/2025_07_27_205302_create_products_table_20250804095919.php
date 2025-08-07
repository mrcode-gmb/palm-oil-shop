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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('unit_type', ['litre', 'jerrycan']);
            $table->decimal('current_stock', 100, 4)->default(0);
            $table->decimal('selling_price', 100, 4);
            $table->text('supplier_name')->nullable();
            $table->text('description')->nullable();
            $table->text('description')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();


            "name": "Palm Oil – Big Jerrycan",
            "unit_type": "jerrycan",
            "": "25000",
            "selling_price": "26000",
            "current_stock": "250",
            "": "Muhammad Garba",
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
