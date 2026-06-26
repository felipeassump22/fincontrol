<?php

namespace App\Enums;

/**
 * Tipos de transação financeira.
 */
enum TransactionType: string
{
    case INCOME = 'INCOME';
    case EXPENSE = 'EXPENSE';

    /**
     * Retorna o label em português para exibição na UI.
     */
    public function label(): string
    {
        return match ($this) {
            self::INCOME => __('Entrada'),
            self::EXPENSE => __('Saída'),
        };
    }

    public function reverse(): TransactionType
    {
        return match ($this) {
            self::INCOME => self::EXPENSE,
            self::EXPENSE => self::INCOME,
        };
    }

    /**
     * Retorna a classe CSS correspondente.
     */
    public function cssClass(): string
    {
        return match ($this) {
            self::INCOME => 'tag-in',
            self::EXPENSE => 'tag-out',
        };
    }
}
