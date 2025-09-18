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
        Schema::create('baskets', function (Blueprint $table) {
            $table->id()->from(101);
            $table->unsignedInteger('qty');
            $table->unsignedSmallInteger('status')->default(1);
            $table->foreignId('product_id')->index()->constrained('products');
            $table->foreignId('user_id')->index()->constrained('users');
            $table->foreignId('order_id')->index()->nullable()->constrained('orders');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('baskets');
    }
};
