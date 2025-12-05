<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('payment_histories', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_id')->index()->nullable();
            $table->string('description')->nullable();
            $table->string('provider');
            $table->string('validated');
            $table->string('request_ip')->nullable();
            $table->string('method')->nullable();
            $table->text('payload')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        if (app()->isLocal()) {
            Schema::dropIfExists('payment_histories');
        }
    }
};
