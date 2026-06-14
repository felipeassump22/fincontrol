<?php

namespace App\Http\Controllers;

use App\Services\BankAccountService;
use App\Services\CashFlowProjectionService;
use App\Services\ReportService;
use App\Services\TransactionService;
use Carbon\Carbon;
use Illuminate\Http\Request;

/**
 * Controller: DashboardController
 *
 * Página principal com métricas consolidadas, alertas e gráficos.
 */
class DashboardController extends Controller
{
    public function __construct(
        private TransactionService $transactionService,
        private BankAccountService $bankAccountService,
        private ReportService $reportService,
        private CashFlowProjectionService $cashFlowService,
    ) {}

    /**
     * Exibe o dashboard principal.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $year = $request->get('year', now()->year);
        $month = $request->get('month', now()->month);

        // Métricas do mês atual
        $totals = $this->transactionService->getMonthlyTotals($user->id, $year, $month);

        // Métricas do mês anterior para comparativo
        $prevDate = Carbon::create($year, $month, 1)->subMonth();
        $prevTotals = $this->transactionService->getMonthlyTotals($user->id, $prevDate->year, $prevDate->month);

        $calculateVariation = function ($current, $previous) {
            if ($previous == 0) {
                return $current > 0 ? 100 : 0;
            }

            return (($current - $previous) / abs($previous)) * 100;
        };

        $variations = [
            'income' => $calculateVariation($totals['total_income'], $prevTotals['total_income']),
            'expense' => $calculateVariation($totals['total_expense'], $prevTotals['total_expense']),
        ];

        // Saldo consolidado (todas as contas)
        $accounts = $user->bankAccounts;
        $consolidatedBalance = $accounts->sum('current_balance');

        // Contas com saldo negativo (alerta)
        $negativeAccounts = $this->bankAccountService->getAccountsWithNegativeBalance($user->id);

        // Receitas por categoria (para gráfico)
        $incomeByCategory = $this->reportService->getIncomeByCategory($user->id, $year, $month);

        // Receitas por cliente (tabela)
        $incomeByClient = $this->reportService->getIncomeByClient($user->id, $year, $month);

        return view('dashboard.index', compact(
            'totals',
            'variations',
            'accounts',
            'consolidatedBalance',
            'negativeAccounts',
            'incomeByCategory',
            'incomeByClient',
            'year',
            'month',
        ));
    }
}
