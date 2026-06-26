<?php

namespace App\Http\Controllers;

use App\Services\CashFlowProjectionService;
use App\Services\MonthlyReportService;
use App\Services\ReportService;
use Illuminate\Http\Request;

/**
 * Controller: ReportController
 *
 * RF10, RF11, RF12 + relatórios Deveria (15, 16)
 */
class ReportController extends Controller
{
    public function __construct(
        private ReportService $reportService,
        private MonthlyReportService $monthlyReportService,
        private CashFlowProjectionService $cashFlowService,
    ) {}

    public function index(Request $request)
    {
        $user = $request->user();
        $ownerId = $user->dataOwnerId();
        $year = (int) $request->get('year', now()->year);
        $month = (int) $request->get('month', now()->month);

        $report = $this->monthlyReportService->generate($year, $month, $ownerId);
        $incomeByCategory = $this->reportService->getIncomeByCategory($ownerId, $year, $month);
        $expensesByCategory = $this->reportService->getExpensesByCategory($ownerId, $year, $month);
        $incomeByClient = $this->reportService->getIncomeByClient($ownerId, $year, $month);

        return view('reports.index', compact(
            'report', 'incomeByCategory', 'expensesByCategory', 'incomeByClient', 'year', 'month'
        ));
    }

    public function exportPdf(Request $request)
    {
        $this->authorize('export', \App\Models\MonthlyReport::class);

        $ownerId = $request->user()->dataOwnerId();
        $year = (int) $request->get('year', now()->year);
        $month = (int) $request->get('month', now()->month);

        $report = $this->monthlyReportService->generate($year, $month, $ownerId);

        return $this->monthlyReportService->downloadPdf($report);
    }

    public function close(Request $request)
    {
        $user = $request->user();
        $ownerId = $user->dataOwnerId();
        $year = (int) $request->get('year', now()->year);
        $month = (int) $request->get('month', now()->month);

        $report = $this->monthlyReportService->generate($year, $month, $ownerId);
        $this->authorize('close', $report);

        try {
            $this->monthlyReportService->close($report, $user->id);

            return redirect()->route('reports.index', compact('year', 'month'))
                ->with('success', 'Relatório fechado com sucesso!');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function cashFlow(Request $request)
    {
        $ownerId = $request->user()->dataOwnerId();
        $months = (int) $request->get('months', 6);
        $projection = $this->cashFlowService->project($ownerId, $months);

        return view('reports.cash-flow', compact('projection', 'months'));
    }

    public function clientPayments(Request $request)
    {
        $ownerId = $request->user()->dataOwnerId();
        $year = (int) $request->get('year', now()->year);
        $month = (int) $request->get('month', now()->month);

        $paymentsByClient = $this->reportService->getPaymentsByClient($ownerId, $year, $month);

        return view('reports.client-payments', compact('paymentsByClient', 'year', 'month'));
    }

    public function accounting(Request $request)
    {
        $ownerId = $request->user()->dataOwnerId();
        $year = (int) $request->get('year', now()->year);
        $month = (int) $request->get('month', now()->month);
        $type = $request->get('type', 'BOTH');

        if (! in_array($type, ['INCOME', 'EXPENSE', 'BOTH'], true)) {
            $type = 'BOTH';
        }

        $accounting = $this->reportService->getAccountingReport($ownerId, $year, $month, $type);

        return view('reports.accounting', compact('accounting', 'year', 'month', 'type'));
    }
}
