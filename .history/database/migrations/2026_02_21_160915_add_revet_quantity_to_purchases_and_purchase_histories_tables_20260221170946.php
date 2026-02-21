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
        Schema::table('purchases_and_purchase_histories_tables', function (Blueprint $table) {
            //
            Schema::table('purchases', function (Blueprint $table) {
                $table->string('public_id')->nullable()->after('id');
                // $table->string('public_id')->unique()->nullable()->after('id'); // if you want unique
            });

            Schema::table('purchase_histories', function (Blueprint $table) {
                $table->string('public_id')->nullable()->after('id');
            });
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchases_and_purchase_histories_tables', function (Blueprint $table) {
            //
            Schema::table('purchases', function (Blueprint $table) {
                $table->dropColumn('public_id');
            });

            Schema::table('purchase_histories', function (Blueprint $table) {
                $table->dropColumn('public_id');
            });
        });
    }
};
