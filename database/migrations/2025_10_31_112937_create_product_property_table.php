<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('product_property', function (Blueprint $table) {
            $table->id();
            $table->unique(['product_id', 'property_id']);

            $table->foreignId('product_id')
                ->index()
                ->constrained('products')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreignId('property_id')
                ->index()
                ->constrained('properties')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->string('value');
        });
    }

    public function down(): void
    {
        if (app()->isLocal()) {
            Schema::dropIfExists('product_property');
        }
    }
};
