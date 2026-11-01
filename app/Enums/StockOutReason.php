<?php

declare(strict_types=1);

namespace App\Enums;

enum StockOutReason: string
{
    case Usage = 'usage';
    case Sale = 'sale';
    case Return = 'return';
    case Expired = 'expired';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Usage => 'Pemakaian',
            self::Sale => 'Penjualan',
            self::Return => 'Retur',
            self::Expired => 'Pemusnahan/Retur (Expired)',
            self::Other => 'Lainnya',
        };
    }
}
