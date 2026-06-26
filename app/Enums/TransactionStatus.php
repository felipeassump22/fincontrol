<?php

namespace App\Enums;

/**
 * Status possíveis de um lançamento financeiro.
 */
enum TransactionStatus: string
{
    case PENDING = 'PENDING';
    case PAID = 'PAID';
    case CANCELED = 'CANCELED';
    case RECONCILED = 'RECONCILED';

    /**
     * Retorna o label em português para exibição na UI.
     */
    public function label(): string
    {
        return match ($this) {
            self::PENDING => __('Em aberto'),
            self::PAID => __('Pago'),
            self::CANCELED => __('Cancelado'),
            self::RECONCILED => __('Conciliado'),
        };
    }

    /**
     * Retorna a classe CSS do badge correspondente.
     */
    public function badgeClass(): string
    {
        return match ($this) {
            self::PENDING => 'badge-warning',
            self::PAID => 'badge-success',
            self::CANCELED => 'badge-danger',
            self::RECONCILED => 'badge-info',
        };
    }

    /**
     * Status que bloqueiam edição pelo perfil Financeiro.
     */
    public function isLockedForFinancial(): bool
    {
        return in_array($this, [self::PAID, self::RECONCILED], true);
    }

    /**
     * Status que bloqueiam edição por qualquer perfil (exceto regras específicas de admin).
     */
    public function isLockedForEdit(): bool
    {
        return in_array($this, [self::PAID, self::RECONCILED, self::CANCELED], true);
    }
}
