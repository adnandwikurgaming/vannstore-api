<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            // produk yang sudah pernah dipesan tidak bisa dihapus (riwayat tetap utuh)
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->string('product_name', 150); // snapshot saat pesan
            $table->unsignedInteger('price');     // snapshot harga saat pesan
            $table->unsignedInteger('qty');
            $table->unsignedBigInteger('subtotal');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
