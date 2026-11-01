<?php

declare(strict_types=1);

namespace App\Enums;

enum BatchStatus: string
{
    case Active = 'active';
    case NearExpiry = 'near_expiry';
    case Expired = 'expired';
    case Depleted = 'depleted';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Aktif',
            self::NearExpiry => 'Segera Kedaluwarsa',
            self::Expired => 'Kedaluwarsa',
            self::Depleted => 'Habis',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Active => 'ok',
            self::NearExpiry => 'warn',
            self::Expired => 'bad',
            self::Depleted => 'muted',
        };
    }
}
