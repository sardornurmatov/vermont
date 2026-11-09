<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->unsignedInteger('delivery_fee')->default(0);
            $table->string('delivery_status')->default('waiting_payment')->index();
            $table->text('delivery_note')->nullable();
            $table->foreignId('courier_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('estimated_delivery_at')->nullable();
        });

        DB::table('orders')->whereIn('status', ['payment_confirmed', 'preparing'])
            ->update(['delivery_status' => 'preparing']);
        DB::table('orders')->where('status', 'delivering')
            ->update(['delivery_status' => 'on_the_way']);
        DB::table('orders')->where('status', 'completed')
            ->update(['delivery_status' => 'delivered']);
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['courier_id']);
            $table->dropIndex(['delivery_status']);
            $table->dropColumn(['delivery_fee', 'delivery_status', 'delivery_note', 'courier_id', 'estimated_delivery_at']);
        });
    }
};
