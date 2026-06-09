<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\MonthlyReportService;
use Illuminate\Console\Command;

/**
 * Command: CloseMonthlyReport
 *
 * RF12 — Fecha o relatório do mês anterior automaticamente.
 * Agendado para executar no dia 5 de cada mês.
 *
 * Uso manual: php artisan reports:close --month=5 --year=2025
 */
class CloseMonthlyReport extends Command
{
    protected $signature = 'reports:close
                            {--month= : Mês para fechar (padrão: mês anterior)}
                            {--year= : Ano para fechar (padrão: ano do mês anterior)}';

    protected $description = 'Fecha o relatório mensal do mês anterior e gera o PDF (RF12)';

    public function handle(MonthlyReportService $reportService): int
    {
        $previousMonth = now()->subMonth();
        $year = (int) ($this->option('year') ?? $previousMonth->year);
        $month = (int) ($this->option('month') ?? $previousMonth->month);

        $this->info("Fechando relatórios de {$month}/{$year}...");

        // Fechar relatório para todos os usuários admin
        $admins = User::whereHas('role', fn ($q) => $q->where('name', 'Administrador'))->get();

        $closed = 0;
        foreach ($admins as $admin) {
            try {
                $report = $reportService->generate($year, $month, $admin->id);

                if (! $report->is_closed) {
                    $reportService->close($report, $admin->id);
                    $reportService->generatePdf($report);
                    $closed++;
                    $this->info("  ✅ Relatório de {$admin->username} fechado.");
                } else {
                    $this->info("  ⏭ Relatório de {$admin->username} já estava fechado.");
                }
            } catch (\Exception $e) {
                $this->error("  ❌ Erro ao fechar relatório de {$admin->username}: {$e->getMessage()}");
            }
        }

        $this->info("Total: {$closed} relatórios fechados.");

        return self::SUCCESS;
    }
}
