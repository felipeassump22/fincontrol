<?php

namespace App\Services;

use App\Models\BankAccount;
use App\Models\Investment;
use App\Models\Transaction;
use Carbon\Carbon;

/**
 * Service: CashFlowProjectionService
 *
 * RF11 — Projeção do fluxo de caixa para 6 meses, incluindo investimentos em CDB.
 */
class CashFlowProjectionService
{
    /**
     * Gera a projeção de fluxo de caixa para os próximos N meses.
     *
     * @param  int  $userId  ID do usuário
     * @param  int  $months  Número de meses a projetar (padrão: 6)
     * @return array Dados de projeção por mês
     */
    public function project(int $userId, int $months = 6): array
    {
        $startDate = Carbon::now()->startOfMonth();
        $projection = [];

        // Saldo atual consolidado
        $currentBalance = BankAccount::where('user_id', $userId)->sum('current_balance');

        // Investimentos CDB ativos
        $cdbInvestments = Investment::where('user_id', $userId)
            ->where('type', 'CDB')
            ->where(function ($query) {
                $query->whereNull('end_date')
                    ->orWhere('end_date', '>', now());
            })
            ->get();

        // Média de receitas/despesas dos últimos 3 meses (para projeção)
        $avgIncome = $this->getMonthlyAverage($userId, 'INCOME', 3);
        $avgExpense = $this->getMonthlyAverage($userId, 'EXPENSE', 3);

        $endProjectionDate = (clone $startDate)->addMonths($months - 1)->endOfMonth();

        // Buscar transações agendadas dos próximos X meses em uma única query
        $scheduledTransactions = Transaction::where('user_id', $userId)
            ->where('status', 'PENDING')
            ->whereBetween('due_date', [$startDate->toDateString(), $endProjectionDate->toDateString()])
            ->selectRaw('
                YEAR(due_date) as year, 
                MONTH(due_date) as month, 
                transaction_type, 
                SUM(amount) as total
            ')
            ->groupByRaw('YEAR(due_date), MONTH(due_date), transaction_type')
            ->get();

        $scheduledByMonth = [];
        foreach ($scheduledTransactions as $st) {
            $typeStr = $st->transaction_type instanceof \BackedEnum ? $st->transaction_type->value : (string) $st->transaction_type;
            $key = "{$st->year}-{$st->month}-{$typeStr}";
            $scheduledByMonth[$key] = (float) $st->total;
        }

        $runningBalance = (float) $currentBalance;

        for ($i = 0; $i < $months; $i++) {
            $monthDate = (clone $startDate)->addMonths($i);
            $year = $monthDate->year;
            $month = $monthDate->month;

            $incomeKey = "{$year}-{$month}-INCOME";
            $expenseKey = "{$year}-{$month}-EXPENSE";

            $scheduledIncome = $scheduledByMonth[$incomeKey] ?? 0;
            $scheduledExpense = $scheduledByMonth[$expenseKey] ?? 0;

            // Se não há transações agendadas, usar média
            $projectedIncome = $scheduledIncome > 0 ? (float) $scheduledIncome : $avgIncome;
            $projectedExpense = $scheduledExpense > 0 ? (float) $scheduledExpense : $avgExpense;

            // Projeção de rendimentos CDB
            $cdbYield = 0;
            foreach ($cdbInvestments as $investment) {
                $targetDate = $monthDate->endOfMonth();
                $projectedValue = $investment->projectValueAt($targetDate);
                $cdbYield += ($projectedValue - (float) $investment->current_amount);
            }

            $runningBalance += $projectedIncome - $projectedExpense;

            $projection[] = [
                'month' => $monthDate->format('Y-m'),
                'label' => $this->getMonthLabel($month, $year),
                'projected_income' => $projectedIncome,
                'projected_expense' => $projectedExpense,
                'net' => $projectedIncome - $projectedExpense,
                'cdb_yield' => round($cdbYield, 2),
                'running_balance' => round($runningBalance, 2),
                'total_with_investments' => round($runningBalance + $cdbYield, 2),
            ];
        }

        return [
            'current_balance' => (float) $currentBalance,
            'cdb_total' => (float) $cdbInvestments->sum('current_amount'),
            'months' => $projection,
        ];
    }

    /**
     * Calcula a média mensal de um tipo de transação nos últimos N meses.
     */
    private function getMonthlyAverage(int $userId, string $type, int $months): float
    {
        $from = Carbon::now()->subMonths($months)->startOfMonth();
        $to = Carbon::now()->subMonth()->endOfMonth();

        $total = Transaction::where('user_id', $userId)
            ->where('transaction_type', $type)
            ->whereBetween('due_date', [$from, $to])
            ->sum('amount');

        return $months > 0 ? round((float) $total / $months, 2) : 0;
    }

    /**
     * Retorna label do mês.
     */
    private function getMonthLabel(int $month, int $year): string
    {
        $names = [1 => 'Jan', 2 => 'Fev', 3 => 'Mar', 4 => 'Abr', 5 => 'Mai', 6 => 'Jun',
            7 => 'Jul', 8 => 'Ago', 9 => 'Set', 10 => 'Out', 11 => 'Nov', 12 => 'Dez'];

        return ($names[$month] ?? '')."/{$year}";
    }
}
