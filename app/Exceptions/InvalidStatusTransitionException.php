<?php

namespace App\Exceptions;

use App\Enums\OrderStatus;
use Exception;
use Illuminate\Http\JsonResponse;

/** Transisi status pesanan tidak sesuai alur (UC-02) → 422. */
class InvalidStatusTransitionException extends Exception
{
    public function __construct(
        public readonly OrderStatus $from,
        public readonly OrderStatus $to,
    ) {
        parent::__construct('Perubahan status tidak diizinkan');
    }

    public function render(): JsonResponse
    {
        $allowed = array_map(fn (OrderStatus $s) => $s->value, $this->from->allowedNext());

        return response()->json([
            'message' => $this->getMessage(),
            'errors' => [
                'status' => [
                    "Status {$this->from->value} tidak bisa diubah ke {$this->to->value}."
                    .($allowed ? ' Status yang diizinkan: '.implode(', ', $allowed).'.' : ' Pesanan sudah final.'),
                ],
            ],
        ], 422);
    }
}
