<?php

use App\Domains\Order\Enums\OrderStatuses;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->enum('status', array_column(OrderStatuses::cases(), 'value'))
                ->default(OrderStatuses::New->value);

            $table->foreignId('user_id')
                ->index()
                ->nullable()
                ->constrained('users')
                ->nullOnDelete()
                ->cascadeOnUpdate();

            $table->foreignId('delivery_type_id')
                ->index()
                ->constrained('delivery_types');

            $table->foreignId('payment_method_id')
                ->index()
                ->constrained('payment_methods');

            $table->decimal('amount', 10, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        if (app()->isLocal()) {
            Schema::dropIfExists('orders');
        }
    }
};
