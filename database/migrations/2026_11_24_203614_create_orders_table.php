<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique(); // contoh: VS-20261125-AB12CD
            $table->foreignId('buyer_id')->constrained('users')->cascadeOnDelete();
            // satu pesanan = satu penjual (checkout dipecah per penjual)
            $table->foreignId('seller_id')->constrained('users')->cascadeOnDelete();
            $table->string('status', 30)->default('menunggu_konfirmasi');
            $table->unsignedBigInteger('total');
            $table->text('shipping_address');
            $table->string('notes', 500)->nullable();
            $table->timestamps();

            $table->index(['buyer_id', 'created_at']);
            $table->index(['seller_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
