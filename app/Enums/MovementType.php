<?php

declare(strict_types=1);

namespace App\Enums;

enum MovementType: string
{
    case In = 'in';
    case Out = 'out';
    case Adjust = 'adjust';
    case Void = 'void';

    public function label(): string
    {
        return match ($this) {
            self::In => 'Masuk',
            self::Out => 'Keluar',
            self::Adjust => 'Penyesuaian',
            self::Void => 'Pembatalan',
        };
    }
}
