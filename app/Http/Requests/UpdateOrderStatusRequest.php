<?php

namespace App\Http\Requests;

use App\Enums\OrderStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** Kontrak request PATCH /api/v1/seller/orders/{order}/status. */
class UpdateOrderStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // kepemilikan pesanan dicek OrderPolicy::updateStatus di route
    }

    public function rules(): array
    {
        return [
            'status' => ['required', Rule::enum(OrderStatus::class)],
        ];
    }

    public function messages(): array
    {
        return [
            'status.required' => 'Status wajib diisi.',
            'status.enum' => 'Status harus salah satu dari: '
                .implode(', ', array_column(OrderStatus::cases(), 'value')).'.',
        ];
    }

    public function status(): OrderStatus
    {
        return OrderStatus::from($this->validated('status'));
    }
}
