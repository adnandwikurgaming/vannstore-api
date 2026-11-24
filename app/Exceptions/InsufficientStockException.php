<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;

/**
 * Galat bisnis: stok tidak cukup / produk tidak tersedia saat checkout (KF-08, UC-01).
 * Service tidak tahu HTTP; exception ini yang memetakan ke 409.
 */
class InsufficientStockException extends Exception
{
    /**
     * @param  array<string, list<string>>  $errors  key mengikuti field request, mis. "items.0.qty"
     */
    public function __construct(public readonly array $errors)
    {
        parent::__construct('Stok tidak mencukupi');
    }

    public function render(): JsonResponse
    {
        return response()->json([
            'message' => $this->getMessage(),
            'errors' => $this->errors,
        ], 409);
    }
}
