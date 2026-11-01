<?php

declare(strict_types=1);

namespace App\Enums;

enum DocumentStatus: string
{
    case Draft = 'draft';
    case Posted = 'posted';
    case Void = 'void';
    case PendingApproval = 'pending_approval';
    case Approved = 'approved';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Posted => 'Posted',
            self::Void => 'Void',
            self::PendingApproval => 'Menunggu Persetujuan',
            self::Approved => 'Disetujui',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Draft => 'warn',
            self::Posted => 'ok',
            self::Void => 'bad',
            self::PendingApproval => 'warn',
            self::Approved => 'ok',
        };
    }
}
