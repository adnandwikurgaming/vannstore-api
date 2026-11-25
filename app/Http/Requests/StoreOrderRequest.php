<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/** Kontrak request POST /api/v1/orders (isi keranjang dari Flutter). */
class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // hak akses ditangani OrderPolicy::create di route
    }

    public function rules(): array
    {
        return [
            'items' => ['required', 'array', 'min:1', 'max:50'],
            'items.*.product_id' => ['required', 'integer', 'distinct', 'exists:products,id'],
            'items.*.qty' => ['required', 'integer', 'min:1', 'max:100'],
            'shipping_address' => ['required', 'string', 'min:10', 'max:500'],
            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'required' => ':attribute wajib diisi.',
            'items.min' => 'Keranjang minimal berisi 1 produk.',
            'items.*.product_id.exists' => 'Produk tidak ditemukan.',
            'items.*.product_id.distinct' => 'Produk yang sama tidak boleh muncul dua kali.',
            'items.*.qty.min' => 'Jumlah minimal 1.',
            'shipping_address.min' => 'Alamat pengiriman terlalu pendek.',
        ];
    }

    public function attributes(): array
    {
        return [
            'items' => 'item pesanan',
            'items.*.product_id' => 'produk',
            'items.*.qty' => 'jumlah',
            'shipping_address' => 'alamat pengiriman',
        ];
    }
}
