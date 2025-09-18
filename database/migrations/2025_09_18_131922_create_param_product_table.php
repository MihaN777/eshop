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
        Schema::create('param_product', function (Blueprint $table) {
            $table->id()->from(101);
            $table->string('value');
            $table->foreignId('product_id')->index()->constrained('products');
            $table->foreignId('param_id')->index()->constrained('params');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('param_product');
    }
};
