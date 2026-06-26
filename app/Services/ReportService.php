<?php

namespace App\Services;

use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Service: ReportService
 *
 * RF10 — Receitas por cliente e despesas por categoria.
 */
class ReportService
{
    /**
     * Receitas agrupadas por cliente no período.
     * RF10
     */
    public function getIncomeByClient(int $userId, int $year, int $month): array
    {
        $startDate = Carbon::create($year, $month, 1)->startOfMonth()->toDateString();
        $endDate = Carbon::create($year, $month, 1)->endOfMonth()->toDateString();

        $results = Transaction::where('user_id', $userId)
            ->where('transaction_type', 'INCOME')
            ->whereBetween('due_date', [$startDate, $endDate])
            ->whereNotNull('client_id')
            ->select('client_id', DB::raw('SUM(amount) as total'), DB::raw('COUNT(*) as count'))
            ->groupBy('client_id')
            ->with('client')
            ->orderByDesc('total')
            ->get();

        $grandTotal = $results->sum('total');

        return $results->map(function ($row) use ($grandTotal) {
            return [
                'client_name' => $row->client->name ?? 'Sem cliente',
                'total' => (float) $row->total,
                'count' => $row->count,
                'percentage' => $grandTotal > 0 ? round(($row->total / $grandTotal) * 100) : 0,
            ];
        })->toArray();
    }

    /**
     * Despesas agrupadas por categoria no período.
     * RF10
     */
    public function getExpensesByCategory(int $userId, int $year, int $month): array
    {
        $startDate = Carbon::create($year, $month, 1)->startOfMonth()->toDateString();
        $endDate = Carbon::create($year, $month, 1)->endOfMonth()->toDateString();

        $results = Transaction::where('user_id', $userId)
            ->where('transaction_type', 'EXPENSE')
            ->whereBetween('due_date', [$startDate, $endDate])
            ->select('category_id', DB::raw('SUM(amount) as total'), DB::raw('COUNT(*) as count'))
            ->groupBy('category_id')
            ->with('category')
            ->orderByDesc('total')
            ->get();

        $grandTotal = $results->sum('total');

        return $results->map(function ($row) use ($grandTotal) {
            return [
                'category_name' => $row->category->name ?? 'Sem categoria',
                'total' => (float) $row->total,
                'count' => $row->count,
                'percentage' => $grandTotal > 0 ? round(($row->total / $grandTotal) * 100) : 0,
            ];
        })->toArray();
    }

    /**
     * Receitas agrupadas por categoria no período (para gráficos).
     */
    public function getIncomeByCategory(int $userId, int $year, int $month): array
    {
        $startDate = Carbon::create($year, $month, 1)->startOfMonth()->toDateString();
        $endDate = Carbon::create($year, $month, 1)->endOfMonth()->toDateString();

        $results = Transaction::where('user_id', $userId)
            ->where('transaction_type', 'INCOME')
            ->whereBetween('due_date', [$startDate, $endDate])
            ->select('category_id', DB::raw('SUM(amount) as total'), DB::raw('COUNT(*) as count'))
            ->groupBy('category_id')
            ->with('category')
            ->orderByDesc('total')
            ->get();

        $grandTotal = $results->sum('total');

        return $results->map(function ($row) use ($grandTotal) {
            return [
                'category_name' => $row->category->name ?? 'Sem categoria',
                'total' => (float) $row->total,
                'count' => $row->count,
                'percentage' => $grandTotal > 0 ? round(($row->total / $grandTotal) * 100) : 0,
            ];
        })->toArray();
    }

    /**
     * Receitas e despesas agrupadas por dia (dados reais do período).
     */
    public function getIncomeExpenseByDay(int $userId, int $year, int $month): array
    {
        $startDate = Carbon::create($year, $month, 1)->startOfMonth()->toDateString();
        $endDate = Carbon::create($year, $month, 1)->endOfMonth()->toDateString();

        return Transaction::where('user_id', $userId)
            ->whereNull('credit_card_id')
            ->whereBetween('due_date', [$startDate, $endDate])
            ->where('status', '!=', TransactionStatus::CANCELED->value)
            ->selectRaw("DATE(due_date) as chart_date, transaction_type, SUM(amount) as total")
            ->groupBy('chart_date', 'transaction_type')
            ->orderBy('chart_date')
            ->get()
            ->map(fn ($row) => [
                'date' => Carbon::parse($row->chart_date)->toDateString(),
                'transaction_type' => $row->transaction_type instanceof \App\Enums\TransactionType
                    ? $row->transaction_type->value
                    : (string) $row->getAttributes()['transaction_type'],
                'total' => (float) $row->total,
            ])
            ->values()
            ->toArray();
    }

    /**
     * Pagamentos por cliente segmentados por status.
     */
    public function getPaymentsByClient(int $userId, int $year, int $month): array
    {
        $startDate = Carbon::create($year, $month, 1)->startOfMonth()->toDateString();
        $endDate = Carbon::create($year, $month, 1)->endOfMonth()->toDateString();

        $rows = Transaction::where('user_id', $userId)
            ->where('transaction_type', 'INCOME')
            ->whereNotNull('client_id')
            ->whereBetween('due_date', [$startDate, $endDate])
            ->select('client_id', 'status', DB::raw('SUM(amount) as total'), DB::raw('COUNT(*) as count'))
            ->groupBy('client_id', 'status')
            ->with('client')
            ->get();

        $grouped = [];

        foreach ($rows as $row) {
            $clientId = $row->client_id;
            if (! isset($grouped[$clientId])) {
                $grouped[$clientId] = [
                    'client_name' => $row->client->name ?? __('Sem cliente'),
                    'paid' => 0.0,
                    'pending' => 0.0,
                    'canceled' => 0.0,
                    'paid_count' => 0,
                    'pending_count' => 0,
                    'canceled_count' => 0,
                    'total' => 0.0,
                ];
            }

            $bucket = match ($row->status) {
                TransactionStatus::PAID, TransactionStatus::RECONCILED => 'paid',
                TransactionStatus::CANCELED => 'canceled',
                default => 'pending',
            };

            $grouped[$clientId][$bucket] += (float) $row->total;
            $grouped[$clientId][$bucket.'_count'] += (int) $row->count;
            $grouped[$clientId]['total'] += (float) $row->total;
        }

        return collect($grouped)->sortByDesc('total')->values()->toArray();
    }

    /**
     * Relatório contábil com filtro por tipo de movimento.
     */
    public function getAccountingReport(int $userId, int $year, int $month, string $type = 'BOTH'): array
    {
        $startDate = Carbon::create($year, $month, 1)->startOfMonth()->toDateString();
        $endDate = Carbon::create($year, $month, 1)->endOfMonth()->toDateString();

        $query = Transaction::where('user_id', $userId)
            ->whereNull('credit_card_id')
            ->whereBetween('due_date', [$startDate, $endDate])
            ->with(['category', 'client', 'bankAccount']);

        if ($type === 'INCOME') {
            $query->where('transaction_type', 'INCOME');
        } elseif ($type === 'EXPENSE') {
            $query->where('transaction_type', 'EXPENSE');
        }

        $transactions = $query->orderBy('due_date')->orderBy('id')->get();

        return [
            'transactions' => $transactions,
            'total_income' => (float) $transactions->where('transaction_type', TransactionType::INCOME)->sum('amount'),
            'total_expense' => (float) $transactions->where('transaction_type', TransactionType::EXPENSE)->sum('amount'),
            'net_result' => (float) $transactions->where('transaction_type', TransactionType::INCOME)->sum('amount')
                - (float) $transactions->where('transaction_type', TransactionType::EXPENSE)->sum('amount'),
        ];
    }
}
