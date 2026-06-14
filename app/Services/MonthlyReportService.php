<?php

namespace App\Services;

use App\Models\MonthlyReport;
use App\Models\Transaction;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Service: MonthlyReportService
 *
 * RF12 — Gerar relatório mensal em PDF, imutável após fechamento.
 */
class MonthlyReportService
{
    public function __construct(
        private ReportService $reportService,
    ) {}

    /**
     * Gera ou atualiza o relatório de um mês específico.
     */
    public function generate(int $year, int $month, int $userId): MonthlyReport
    {
        $report = MonthlyReport::firstOrNew([
            'user_id' => $userId,
            'year' => $year,
            'month' => $month,
        ]);

        // Se já está fechado, não pode ser regenerado
        if ($report->exists && $report->is_closed) {
            return $report;
        }

        $startDate = Carbon::create($year, $month, 1)->startOfMonth()->toDateString();
        $endDate = Carbon::create($year, $month, 1)->endOfMonth()->toDateString();

        // Calcular totais
        $totalIncome = (float) Transaction::where('user_id', $userId)
            ->where('transaction_type', 'INCOME')
            ->whereBetween('due_date', [$startDate, $endDate])
            ->sum('amount');

        $totalExpense = (float) Transaction::where('user_id', $userId)
            ->where('transaction_type', 'EXPENSE')
            ->whereBetween('due_date', [$startDate, $endDate])
            ->sum('amount');

        // Dados detalhados para o PDF
        $reportData = [
            'income_by_client' => $this->reportService->getIncomeByClient($userId, $year, $month),
            'expenses_by_category' => $this->reportService->getExpensesByCategory($userId, $year, $month),
            'income_by_category' => $this->reportService->getIncomeByCategory($userId, $year, $month),
            'transactions' => Transaction::where('user_id', $userId)
                ->whereBetween('due_date', [$startDate, $endDate])
                ->with(['bankAccount', 'category', 'client'])
                ->orderBy('due_date')
                ->get()
                ->toArray(),
        ];

        $report->fill([
            'total_income' => $totalIncome,
            'total_expense' => $totalExpense,
            'net_result' => $totalIncome - $totalExpense,
            'report_data' => $reportData,
        ]);

        $report->save();

        return $report;
    }

    /**
     * Fecha o relatório, tornando-o imutável.
     */
    public function close(MonthlyReport $report, int $closedByUserId): MonthlyReport
    {
        if ($report->is_closed) {
            throw new \Exception('Relatório já está fechado.');
        }

        $report->update([
            'is_closed' => true,
            'closed_by' => $closedByUserId,
            'closed_at' => now(),
        ]);

        return $report->fresh();
    }

    /**
     * Gera o PDF do relatório mensal.
     * RF12 — Layout profissional para envio ao contador.
     */
    public function generatePdf(MonthlyReport $report): string
    {
        $pdf = Pdf::loadView('reports.pdf.monthly', [
            'report' => $report,
            'data' => $report->report_data,
        ]);

        $filename = "reports/relatorio_{$report->year}_{$report->month}_{$report->user_id}.pdf";
        Storage::disk('public')->put($filename, $pdf->output());

        $report->update(['pdf_path' => $filename]);

        return $filename;
    }

    /**
     * Retorna o PDF como download.
     */
    public function downloadPdf(MonthlyReport $report)
    {
        if (! $report->pdf_path || ! Storage::disk('public')->exists($report->pdf_path)) {
            $this->generatePdf($report);
        }

        $safeName = Str::slug($report->periodLabel(), '_');

        return response()->download(
            Storage::disk('public')->path($report->pdf_path),
            "FinControl_Relatorio_{$safeName}.pdf",
            ['Content-Type' => 'application/pdf']
        );
    }
}
