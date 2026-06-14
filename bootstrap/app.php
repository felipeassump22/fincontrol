<?php

use App\Http\Middleware\CheckRole;
use App\Http\Middleware\LanguageMiddleware;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

/**
 * Bootstrap da aplicação FinControl.
 * Laravel 11 usa este arquivo em vez de app/Http/Kernel.php.
 */
return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->web(append: [
            LanguageMiddleware::class,
            \App\Http\Middleware\TimezoneMiddleware::class,
        ]);

        // Registrar o middleware de checagem de perfil
        $middleware->alias([
            'role' => CheckRole::class,
        ]);
    })
    ->withSchedule(function (Schedule $schedule) {
        // RF09 — Recriar despesas fixas no dia 1 de cada mês às 00:05
        $schedule->command('expenses:recreate')
            ->monthlyOn(1, '00:05')
            ->withoutOverlapping()
            ->appendOutputTo(storage_path('logs/recurring-expenses.log'));

        // RF12 — Fechar relatório do mês anterior no dia 5 às 00:10
        $schedule->command('reports:close')
            ->monthlyOn(5, '00:10')
            ->withoutOverlapping()
            ->appendOutputTo(storage_path('logs/monthly-reports.log'));
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })
    ->create();
