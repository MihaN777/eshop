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
            $table->string('slug')->unique();
            $table->string('title');
            $table->decimal('price')->index();
            $table->unsignedInteger('quantity')->index()->default(0);
            $table->text('text')->nullable();
            $table->json('json_properties')->nullable();
            $table->boolean('on_home_page')->default(false);
            $table->integer('sorting')->default(999);
            $table->fullText(['title', 'text']);

            $table->foreignId('brand_id')
                ->index()
                ->nullable()
                ->constrained('brands')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (app()->isLocal()) {
            Schema::dropIfExists('products');
        }
    }
};
