<?php

namespace App\Enums;

enum CreditCardInvoiceStatus: string
{
    case OPEN = 'OPEN';
    case CLOSED = 'CLOSED';
    case PAID = 'PAID';

    public function label(): string
    {
        return match ($this) {
            self::OPEN => __('Aberta'),
            self::CLOSED => __('Fechada'),
            self::PAID => __('Paga'),
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::OPEN => 'badge-warning',
            self::CLOSED => 'badge-info',
            self::PAID => 'badge-success',
        };
    }
}
