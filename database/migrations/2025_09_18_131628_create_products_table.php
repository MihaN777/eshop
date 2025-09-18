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
            $table->id()->from(101);
            $table->string('title');
            $table->text('description');
            $table->text('content');
            $table->decimal('price');
            $table->decimal('old_price')->nullable();
            $table->unsignedInteger('qty');
            $table->foreignId('category_id')->index()->constrained('categories');
            $table->foreignId('product_parent_id')->index()->constrained('product_parents');
            $table->timestamps();
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
