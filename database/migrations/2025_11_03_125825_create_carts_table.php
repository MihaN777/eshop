<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Корзина принадлежит либо гостю (storage_id), либо аккаунту (user_id),
        // но никогда обоим сразу. Уникальность владельца гарантирует, что
        // запись и чтение всегда разрешаются в одну и ту же строку.
        Schema::create('carts', function (Blueprint $table) {
            $table->id();
            $table->string('storage_id')
                ->nullable()
                ->unique();

            $table->foreignId('user_id')
                ->nullable()
                ->unique()
                ->constrained('users')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        if (app()->isLocal()) {
            Schema::dropIfExists('carts');
        }
    }
};
