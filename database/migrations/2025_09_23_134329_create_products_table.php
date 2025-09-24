<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('slug');
            $table->string('title');
            $table->unsignedInteger('price');
            $table->string('image')->nullable();

            $table->foreignId('brand_id')
                ->index()
                ->nullable()
                ->constrained('brands')
                ->onUpdate('cascade')
                ->nullOnDelete();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (app()->isLocal()) Schema::dropIfExists('products');
    }
};
