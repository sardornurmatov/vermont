<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('orders', function (Blueprint $table) { $table->id(); $table->string('order_number')->unique(); $table->foreignId('user_id')->constrained()->cascadeOnDelete(); $table->string('phone'); $table->text('address'); $table->unsignedInteger('total'); $table->string('status')->default('awaiting_payment'); $table->string('payment_method')->default('card_transfer'); $table->string('receipt_path')->nullable(); $table->text('admin_note')->nullable(); $table->timestamps(); });
        Schema::create('order_items', function (Blueprint $table) { $table->id(); $table->foreignId('order_id')->constrained()->cascadeOnDelete(); $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete(); $table->string('product_name'); $table->string('variant')->nullable(); $table->unsignedInteger('price'); $table->unsignedInteger('quantity'); $table->timestamps(); });
    }
    public function down(): void { Schema::dropIfExists('order_items'); Schema::dropIfExists('orders'); }
};
