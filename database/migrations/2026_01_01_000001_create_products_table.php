<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void { Schema::create('products', function (Blueprint $table) { $table->id(); $table->string('name'); $table->string('slug')->unique(); $table->string('category'); $table->string('gender'); $table->text('description')->nullable(); $table->json('translations')->nullable(); $table->unsignedInteger('price'); $table->unsignedInteger('old_price')->nullable(); $table->string('image_url'); $table->json('colors')->nullable(); $table->json('sizes')->nullable(); $table->unsignedInteger('stock')->default(0); $table->boolean('featured')->default(false); $table->boolean('active')->default(true); $table->timestamps(); }); }
    public function down(): void { Schema::dropIfExists('products'); }
};
