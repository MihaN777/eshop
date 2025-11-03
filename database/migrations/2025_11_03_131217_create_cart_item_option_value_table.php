<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cart_item_option_value', function (Blueprint $table) {
            $table->id();

            $table->foreignId('cart_item_id')
                ->index()
                ->constrained('cart_items')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreignId('option_value_id')
                ->index()
                ->constrained('option_values')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        if (app()->isLocal()) {
            Schema::dropIfExists('cart_item_option_value');
        }
    }
};
