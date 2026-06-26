<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case PIX = 'PIX';
    case BOLETO = 'BOLETO';
    case CARTAO = 'CARTAO';
    case TRANSFERENCIA = 'TRANSFERENCIA';
    case DINHEIRO = 'DINHEIRO';
    case OUTRO = 'OUTRO';

    public function label(): string
    {
        return match ($this) {
            self::PIX => __('PIX'),
            self::BOLETO => __('Boleto'),
            self::CARTAO => __('Cartão'),
            self::TRANSFERENCIA => __('Transferência'),
            self::DINHEIRO => __('Dinheiro'),
            self::OUTRO => __('Outro'),
        };
    }
}
