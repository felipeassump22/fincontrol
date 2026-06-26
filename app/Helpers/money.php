<?php

if (! function_exists('money')) {
    function money($value)
    {
        $symbol = currency_symbol();

        // Formatação simples (mantendo o separador pt-br por enquanto para evitar quebra de testes)
        // Se a linguagem fosse dinâmica, poderíamos usar NumberFormatter
        return $symbol.' '.number_format($value, 2, ',', '.');
    }
}

if (! function_exists('user_currency')) {
    function user_currency(): string
    {
        return auth()->check() ? (auth()->user()->currency ?: 'BRL') : 'BRL';
    }
}

if (! function_exists('chart_locale')) {
    function chart_locale(): string
    {
        return match (user_currency()) {
            'USD' => 'en-US',
            'EUR' => 'de-DE',
            'GBP' => 'en-GB',
            default => 'pt-BR',
        };
    }
}

if (! function_exists('currency_symbol')) {
    function currency_symbol()
    {
        $symbols = [
            'BRL' => 'R$',
            'USD' => '$',
            'EUR' => '€',
            'GBP' => '£',
        ];

        return $symbols[user_currency()] ?? 'R$';
    }
}
