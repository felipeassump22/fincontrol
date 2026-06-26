<?php

namespace App\Http\Controllers;

use App\Services\BankAccountService;
use App\Services\CashFlowProjectionService;
use App\Models\BankAccount;
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
        $ownerId = $user->dataOwnerId();
        $year = (int) $request->get('year', now()->year);
        $month = (int) $request->get('month', now()->month);

        // Métricas do mês atual
        $totals = $this->transactionService->getMonthlyTotals($ownerId, $year, $month);

        // Métricas do mês anterior para comparativo
        $prevDate = Carbon::create($year, $month, 1)->subMonth();
        $prevTotals = $this->transactionService->getMonthlyTotals($ownerId, $prevDate->year, $prevDate->month);

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
        $accounts = BankAccount::where('user_id', $ownerId)->active()->orderBy('name')->get();
        $consolidatedBalance = $accounts->sum('current_balance');

        // Contas com saldo negativo (alerta)
        $negativeAccounts = $this->bankAccountService->getAccountsWithNegativeBalance($ownerId);

        // Receitas por categoria (para gráfico)
        $incomeByCategory = $this->reportService->getIncomeByCategory($ownerId, $year, $month);

        // Receitas por cliente (tabela)
        $incomeByClient = $this->reportService->getIncomeByClient($ownerId, $year, $month);

        // Gráfico receitas vs despesas reais do período
        $incomeExpenseChart = [
            'summary' => [
                'income' => $totals['total_income'],
                'expense' => $totals['total_expense'],
            ],
            'daily' => $this->reportService->getIncomeExpenseByDay($ownerId, $year, $month),
        ];

        // Projeção do Fluxo de Caixa (Dashboard)
        $cashFlowData = $this->cashFlowService->project($ownerId, 6);

        return view('dashboard.index', [
            'totals' => $totals,
            'variations' => $variations,
            'accounts' => $accounts,
            'consolidatedBalance' => $consolidatedBalance,
            'negativeAccounts' => $negativeAccounts,
            'incomeByCategory' => $incomeByCategory,
            'incomeByClient' => $incomeByClient,
            'year' => $year,
            'month' => $month,
            'cashFlowData' => $cashFlowData,
            'incomeExpenseChart' => $incomeExpenseChart,
        ]);
    }
}
