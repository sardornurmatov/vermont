<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('categories')) {
            Schema::create('categories', function (Blueprint $table) {
                $table->id();
                $table->string('name', 80)->unique();
                $table->timestamps();
            });
        }

        // Keep categories that already exist on products when upgrading an existing shop.
        if (Schema::hasTable('products')) {
            $names = DB::table('products')->whereNotNull('category')->distinct()->pluck('category');
            foreach ($names as $name) {
                $name = trim((string) $name);
                if ($name !== '') {
                    DB::table('categories')->insertOrIgnore([
                        'name' => $name,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
