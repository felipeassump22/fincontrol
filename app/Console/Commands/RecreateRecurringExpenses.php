<?php

namespace App\Console\Commands;

use App\Services\RecurringExpenseService;
use Illuminate\Console\Command;

/**
 * Command: RecreateRecurringExpenses
 *
 * RF09 — Automatizar a recriação das despesas fixas mensais.
 * Agendado para executar no dia 1 de cada mês.
 *
 * Uso manual: php artisan expenses:recreate --month=6 --year=2025
 */
class RecreateRecurringExpenses extends Command
{
    protected $signature = 'expenses:recreate
                            {--month= : Mês para recriar (padrão: mês atual)}
                            {--year= : Ano para recriar (padrão: ano atual)}';

    protected $description = 'Recria despesas fixas recorrentes para o mês especificado (RF09)';

    public function handle(RecurringExpenseService $service): int
    {
        $year = (int) ($this->option('year') ?? now()->year);
        $month = (int) ($this->option('month') ?? now()->month);

        $this->info("Recriando despesas fixas para {$month}/{$year}...");

        $created = $service->recreateForMonth($year, $month);

        $this->info("✅ {$created} lançamentos recorrentes criados para {$month}/{$year}.");

        return self::SUCCESS;
    }
}
