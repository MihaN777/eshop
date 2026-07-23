<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cart_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('cart_id')
                ->index()
                ->constrained('carts')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreignId('product_id')
                ->index()
                ->constrained('products')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->decimal('price', 10, 2);
            $table->unsignedInteger('quantity');
            $table->string('string_option_values')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        if (app()->isLocal()) {
            Schema::dropIfExists('cart_items');
        }
    }
};
