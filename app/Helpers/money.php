<?php

if (! function_exists('money')) {
    function money($value)
    {
        $currency = auth()->check() ? auth()->user()->currency : 'BRL';

        $symbols = [
            'BRL' => 'R$',
            'USD' => '$',
            'EUR' => '€',
            'GBP' => '£',
        ];

        $symbol = $symbols[$currency] ?? 'R$';

        // Formatação simples (mantendo o separador pt-br por enquanto para evitar quebra de testes)
        // Se a linguagem fosse dinâmica, poderíamos usar NumberFormatter
        return $symbol.' '.number_format($value, 2, ',', '.');
    }
}

if (! function_exists('currency_symbol')) {
    function currency_symbol()
    {
        $currency = auth()->check() ? auth()->user()->currency : 'BRL';

        $symbols = [
            'BRL' => 'R$',
            'USD' => '$',
            'EUR' => '€',
            'GBP' => '£',
        ];

        return $symbols[$currency] ?? 'R$';
    }
}
