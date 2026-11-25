<?php

namespace App\Enums;

enum OrderStatus: string
{
    case MenungguKonfirmasi = 'menunggu_konfirmasi';
    case Diproses = 'diproses';
    case Dikirim = 'dikirim';
    case Selesai = 'selesai';
    case Dibatalkan = 'dibatalkan';

    /**
     * Alur status pesanan:
     * menunggu_konfirmasi → diproses → dikirim → selesai
     * dibatalkan hanya dari menunggu_konfirmasi atau diproses.
     *
     * @return list<self>
     */
    public function allowedNext(): array
    {
        return match ($this) {
            self::MenungguKonfirmasi => [self::Diproses, self::Dibatalkan],
            self::Diproses => [self::Dikirim, self::Dibatalkan],
            self::Dikirim => [self::Selesai],
            self::Selesai, self::Dibatalkan => [],
        };
    }

    public function canTransitionTo(self $next): bool
    {
        return in_array($next, $this->allowedNext(), true);
    }

    public function label(): string
    {
        return match ($this) {
            self::MenungguKonfirmasi => 'Menunggu Konfirmasi',
            self::Diproses => 'Diproses',
            self::Dikirim => 'Dikirim',
            self::Selesai => 'Selesai',
            self::Dibatalkan => 'Dibatalkan',
        };
    }
}
