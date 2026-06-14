<?php

$dir = new RecursiveDirectoryIterator(__DIR__.'/resources/views');
$ite = new RecursiveIteratorIterator($dir);
$files = new RegexIterator($ite, '/.*\.blade\.php$/', RegexIterator::GET_MATCH);

$count = 0;
foreach ($files as $file) {
    $path = $file[0];
    $content = file_get_contents($path);

    // Pattern to match: R$ {{ number_format($variable, 2, ',', '.') }}
    $pattern = '/R\$\s*\{\{\s*number_format\((.*?),\s*2\s*,\s*\'\,\'\s*,\s*\'\.\'\s*\)\s*\}\}/';

    $newContent = preg_replace_callback($pattern, function ($matches) {
        $var = trim($matches[1]);

        return '{{ money('.$var.') }}';
    }, $content);

    $newContent = str_replace('Valor (R$)', 'Valor', $newContent);
    $newContent = str_replace('Valor inicial (R$)', 'Valor inicial', $newContent);
    $newContent = str_replace('Valor total (R$)', 'Valor total', $newContent);
    $newContent = str_replace('Saldo inicial (R$)', 'Saldo inicial', $newContent);

    if ($content !== $newContent) {
        file_put_contents($path, $newContent);
        echo "Updated: $path\n";
        $count++;
    }
}

echo "Total files updated: $count\n";
 