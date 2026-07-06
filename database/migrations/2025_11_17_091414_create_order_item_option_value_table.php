<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('order_item_option_value', function (Blueprint $table) {
            $table->id();
            $table->unique(['order_item_id', 'option_value_id']);

            $table->foreignId('order_item_id')
                ->constrained('order_items')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreignId('option_value_id')
                ->constrained('option_values')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        if (app()->isLocal()) {
            Schema::dropIfExists('order_item_option_value');
        }
    }
};
