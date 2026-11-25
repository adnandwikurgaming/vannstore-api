<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seller_id')->constrained('users')->cascadeOnDelete();
            // kategori yang masih dipakai produk tidak boleh dihapus
            $table->foreignId('category_id')->constrained()->restrictOnDelete();
            $table->string('name', 150);
            $table->text('description');
            $table->unsignedInteger('price'); // rupiah, tanpa desimal
            $table->unsignedInteger('stock')->default(0);
            $table->string('photo_path')->nullable();
            $table->boolean('is_active')->default(true); // false = dinonaktifkan penjual/admin
            $table->timestamps();

            $table->index(['category_id', 'is_active']);
            $table->index('name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
