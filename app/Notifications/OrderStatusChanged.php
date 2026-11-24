<?php

namespace App\Notifications;

use App\Enums\OrderStatus;
use App\Models\Order;
use Illuminate\Notifications\Notification;

/**
 * KF-11: notifikasi ke pembeli saat status pesanan berubah.
 * v1 disimpan ke tabel notifications; channel FCM ditambahkan saat fase Flutter.
 */
class OrderStatusChanged extends Notification
{
    public function __construct(
        public readonly Order $order,
        public readonly OrderStatus $previous,
    ) {}

    /** @return list<string> */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /** @return array<string, mixed> */
    public function toArray(object $notifiable): array
    {
        return [
            'order_id' => $this->order->id,
            'order_code' => $this->order->code,
            'previous_status' => $this->previous->value,
            'status' => $this->order->status->value,
            'message' => "Pesanan {$this->order->code} sekarang berstatus {$this->order->status->label()}.",
        ];
    }
}
