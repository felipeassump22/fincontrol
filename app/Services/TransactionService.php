<?php

namespace App\Services;

use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Models\BankAccount;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Service: TransactionService
 *
 * Centraliza toda a lógica de negócio de lançamentos financeiros.
 * RF02 — Registrar entradas e saídas.
 * RF03 — Bloquear edição de lançamentos pagos.
 * RF04 — Alerta de saldo negativo.
 * RF05 — Vincular nota fiscal ao lançamento.
 */
class TransactionService
{
    public function __construct(
        private BankAccountService $bankAccountService,
        private AuditService $auditService,
    ) {}

    /**
     * Cria um novo lançamento financeiro.
     * Se for do tipo EXPENSE e status PAID, atualiza o saldo da conta.
     */
    public function create(array $data, ?UploadedFile $invoiceFile = null): Transaction
    {
        return DB::transaction(function () use ($data, $invoiceFile) {
            $data['status'] = TransactionStatus::PENDING;

            if (empty($data['competence_date']) && ! empty($data['due_date'])) {
                $data['competence_date'] = $data['due_date'];
            }

            if ($invoiceFile) {
                $data['invoice_document_url'] = $invoiceFile->store('invoices', 'public');
            }

            $transaction = Transaction::create($data);

            return $transaction;
        });
    }

    public function update(Transaction $transaction, array $data, ?UploadedFile $invoiceFile = null): Transaction
    {
        if ($transaction->isCanceled()) {
            throw new \Exception('Este lançamento não pode ser editado.');
        }

        $user = auth()->user();
        if ($user?->isFinancial() && $transaction->status !== TransactionStatus::PENDING) {
            throw new \Exception('Este lançamento não pode ser editado.');
        }

        return DB::transaction(function () use ($transaction, $data, $invoiceFile) {
            if ($invoiceFile) {
                // Remover arquivo antigo, se existir
                if ($transaction->invoice_document_url) {
                    Storage::disk('public')->delete($transaction->invoice_document_url);
                }
                $data['invoice_document_url'] = $invoiceFile->store('invoices', 'public');
            }

            $wasPending = $transaction->isPending();
            $hadBalanceImpact = $this->transactionAffectsBankBalance($transaction);
            $originalBalanceSnapshot = [
                'amount' => (float) $transaction->amount,
                'type' => $transaction->transaction_type->value,
                'bank_account_id' => $transaction->bank_account_id,
            ];

            $transaction->fill($data);

            if (
                in_array($transaction->status, [TransactionStatus::PAID, TransactionStatus::RECONCILED], true)
                && ! isset($data['payment_date'])
                && ! $transaction->payment_date
            ) {
                $transaction->payment_date = now()->toDateString();
            }

            $transaction->save();

            $hasBalanceImpact = $this->transactionAffectsBankBalance($transaction);

            if ($hadBalanceImpact && $hasBalanceImpact) {
                $balanceFieldsChanged = $originalBalanceSnapshot['amount'] !== (float) $transaction->amount
                    || $originalBalanceSnapshot['type'] !== $transaction->transaction_type->value
                    || $originalBalanceSnapshot['bank_account_id'] !== $transaction->bank_account_id;

                if ($balanceFieldsChanged) {
                    $oldAccount = BankAccount::findOrFail($originalBalanceSnapshot['bank_account_id']);
                    $this->bankAccountService->revertBalance(
                        $oldAccount,
                        $originalBalanceSnapshot['amount'],
                        $originalBalanceSnapshot['type']
                    );

                    $newAccount = BankAccount::findOrFail($transaction->bank_account_id);
                    $this->bankAccountService->adjustBalance(
                        $newAccount,
                        (float) $transaction->amount,
                        $transaction->transaction_type->value
                    );
                }
            } elseif ($hadBalanceImpact && ! $hasBalanceImpact) {
                $oldAccount = BankAccount::findOrFail($originalBalanceSnapshot['bank_account_id']);
                $this->bankAccountService->revertBalance(
                    $oldAccount,
                    $originalBalanceSnapshot['amount'],
                    $originalBalanceSnapshot['type']
                );
            } elseif (! $hadBalanceImpact && $hasBalanceImpact) {
                $newAccount = BankAccount::findOrFail($transaction->bank_account_id);
                $this->bankAccountService->adjustBalance(
                    $newAccount,
                    (float) $transaction->amount,
                    $transaction->transaction_type->value
                );

                if ($wasPending) {
                    $transaction->logCustomAudit(
                        'paid',
                        ['status' => 'PENDING'],
                        ['status' => $transaction->status->value]
                    );
                }
            }

            return $transaction->fresh();
        });
    }

    /**
     * Marca um lançamento como pago.
     * Atualiza o saldo da conta bancária vinculada.
     */
    public function markAsPaid(Transaction $transaction): Transaction
    {
        if ($transaction->status !== TransactionStatus::PENDING) {
            throw new \Exception('Somente lançamentos em aberto podem ser pagos.');
        }

        return DB::transaction(function () use ($transaction) {
            $transaction->update([
                'status' => TransactionStatus::PAID,
                'payment_date' => now()->toDateString(),
            ]);

            // Ajustar saldo da conta
            $this->bankAccountService->adjustBalance(
                $transaction->bankAccount,
                (float) $transaction->amount,
                $transaction->transaction_type->value
            );

            // Log de auditoria customizado para pagamento
            $transaction->logCustomAudit('paid', ['status' => 'PENDING'], ['status' => 'PAID']);

            return $transaction->fresh();
        });
    }

    /**
     * Marca um lançamento como conciliado.
     */
    public function markAsReconciled(Transaction $transaction): Transaction
    {
        if ($transaction->status !== TransactionStatus::PAID) {
            throw new \Exception('Somente lançamentos pagos podem ser conciliados.');
        }

        return DB::transaction(function () use ($transaction) {
            $transaction->update(['status' => TransactionStatus::RECONCILED]);
            $transaction->logCustomAudit('reconciled', ['status' => 'PAID'], ['status' => 'RECONCILED']);

            return $transaction->fresh();
        });
    }

    /**
     * Cancela um lançamento pendente.
     */
    public function cancel(Transaction $transaction): Transaction
    {
        if ($transaction->status !== TransactionStatus::PENDING) {
            throw new \Exception('Somente lançamentos em aberto podem ser cancelados.');
        }

        return DB::transaction(function () use ($transaction) {
            $transaction->update(['status' => TransactionStatus::CANCELED]);
            $transaction->logCustomAudit('canceled', ['status' => 'PENDING'], ['status' => 'CANCELED']);

            return $transaction->fresh();
        });
    }

    /**
     * Estorna um lançamento pago/conciliado criando transação inversa.
     */
    public function reverse(Transaction $transaction): Transaction
    {
        if (! in_array($transaction->status, [TransactionStatus::PAID, TransactionStatus::RECONCILED], true)) {
            throw new \Exception('Somente lançamentos pagos ou conciliados podem ser estornados.');
        }

        if ($transaction->wasReversed()) {
            throw new \Exception('Este lançamento já foi estornado.');
        }

        if ($transaction->credit_card_id) {
            throw new \Exception('Lançamentos de cartão devem ser estornados na fatura do cartão.');
        }

        return DB::transaction(function () use ($transaction) {
            $inverseType = $transaction->transaction_type->reverse();

            $reversal = Transaction::create([
                'description' => __('Estorno: :description', ['description' => $transaction->description]),
                'amount' => $transaction->amount,
                'due_date' => now()->toDateString(),
                'competence_date' => $transaction->competence_date ?? $transaction->due_date,
                'payment_date' => now()->toDateString(),
                'transaction_type' => $inverseType,
                'status' => TransactionStatus::PAID,
                'payment_method' => $transaction->payment_method,
                'user_id' => $transaction->user_id,
                'bank_account_id' => $transaction->bank_account_id,
                'category_id' => $transaction->category_id,
                'client_id' => $transaction->client_id,
                'reversal_of_id' => $transaction->id,
            ]);

            $this->bankAccountService->adjustBalance(
                $reversal->bankAccount,
                (float) $reversal->amount,
                $reversal->transaction_type->value
            );

            $transaction->logCustomAudit('reversed', [], ['reversal_id' => $reversal->id]);
            $reversal->logCustomAudit('reversal_created', [], ['original_id' => $transaction->id]);

            return $reversal->fresh(['bankAccount', 'category', 'client', 'reversalOf']);
        });
    }

    /**
     * Deleta um lançamento.
     * RF01 — Somente usuários com permissão.
     * Se estava pago, reverte o saldo.
     */
    public function delete(Transaction $transaction): void
    {
        DB::transaction(function () use ($transaction) {
            // Se estava pago, reverter o saldo da conta
            if ($transaction->isPaid() || $transaction->isReconciled()) {
                $this->bankAccountService->revertBalance(
                    $transaction->bankAccount,
                    (float) $transaction->amount,
                    $transaction->transaction_type->value
                );
            }

            // Remover arquivo de nota fiscal, se existir
            if ($transaction->invoice_document_url) {
                Storage::disk('public')->delete($transaction->invoice_document_url);
            }

            $transaction->delete();
        });
    }

    /**
     * Simula o impacto de um lançamento no saldo da conta.
     * RF04 — Alerta de saldo negativo.
     */
    public function checkBalanceImpact(int $bankAccountId, float $amount, string $type): array
    {
        $account = BankAccount::findOrFail($bankAccountId);

        return $this->bankAccountService->simulateImpact($account, $amount, $type);
    }

    /**
     * Lista lançamentos com filtros.
     * Suporte a filtros por período, conta, categoria, cliente, status.
     */
    public function list(int $userId, array $filters = [], int $perPage = 20)
    {
        $filters = $this->resolvePeriodFilters($filters);

        $query = Transaction::with(['bankAccount', 'category', 'client', 'creditCard'])
            ->where('user_id', $userId);

        // Sorting: Prioritizing today and future dates (Item 5)
        $query->orderByRaw('CASE WHEN due_date >= CURDATE() THEN 0 ELSE 1 END')
              ->orderBy('due_date', 'asc');

        if (empty($filters['credit_card_id'])) {
            $query->whereNull('credit_card_id');
        } else {
            $query->where('credit_card_id', $filters['credit_card_id']);
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['transaction_type'])) {
            $query->where('transaction_type', $filters['transaction_type']);
        }

        if (! empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        if (! empty($filters['period'])) {
            $days = (int) $filters['period'];
            $query->whereBetween('due_date', [now()->toDateString(), now()->addDays($days)->toDateString()]);
        } else {
            if (! empty($filters['date_from'])) {
                $query->where('due_date', '>=', $filters['date_from']);
            }
            if (! empty($filters['date_to'])) {
                $query->where('due_date', '<=', $filters['date_to']);
            }
        }

        if (! empty($filters['bank_account_id'])) {
            $query->forAccount($filters['bank_account_id']);
        }

        if (! empty($filters['client_id'])) {
            $query->forClient($filters['client_id']);
        }

        return $query->paginate($perPage);
    }

    /**
     * Retorna totais do mês para o dashboard.
     */
    public function getMonthlyTotals(int $userId, int $year, int $month): array
    {
        $startDate = Carbon::create($year, $month, 1)->startOfMonth()->toDateString();
        $endDate = Carbon::create($year, $month, 1)->endOfMonth()->toDateString();

        $totals = Transaction::where('user_id', $userId)
            ->whereNull('credit_card_id')
            ->whereBetween('due_date', [$startDate, $endDate])
            ->selectRaw("
                COALESCE(SUM(CASE WHEN transaction_type = 'INCOME' THEN amount ELSE 0 END), 0) as total_income,
                COALESCE(SUM(CASE WHEN transaction_type = 'EXPENSE' THEN amount ELSE 0 END), 0) as total_expense,
                COALESCE(SUM(CASE WHEN status = 'PENDING' THEN amount ELSE 0 END), 0) as pending_amount,
                SUM(CASE WHEN status = 'PENDING' THEN 1 ELSE 0 END) as pending_count
            ")
            ->first();

        $totalIncome = (float) $totals->total_income;
        $totalExpense = (float) $totals->total_expense;

        return [
            'total_income' => $totalIncome,
            'total_expense' => $totalExpense,
            'net_result' => $totalIncome - $totalExpense,
            'pending_amount' => (float) $totals->pending_amount,
            'pending_count' => (int) $totals->pending_count,
        ];
    }

    /**
     * Resolve atalhos de período (Hoje, esta semana, este mês, mês anterior).
     */
    public function resolvePeriodFilters(array $filters): array
    {
        if (empty($filters['quick_period'])) {
            return $filters;
        }

        $today = now();

        switch ($filters['quick_period']) {
            case 'today':
                $filters['date_from'] = $today->toDateString();
                $filters['date_to'] = $today->toDateString();
                break;
            case 'this_week':
                $filters['date_from'] = $today->copy()->startOfWeek()->toDateString();
                $filters['date_to'] = $today->copy()->endOfWeek()->toDateString();
                break;
            case 'this_month':
                $filters['date_from'] = $today->copy()->startOfMonth()->toDateString();
                $filters['date_to'] = $today->copy()->endOfMonth()->toDateString();
                break;
            case 'last_month':
                $prev = $today->copy()->subMonth();
                $filters['date_from'] = $prev->startOfMonth()->toDateString();
                $filters['date_to'] = $prev->endOfMonth()->toDateString();
                break;
        }

        unset($filters['period']);

        return $filters;
    }

    private function transactionAffectsBankBalance(Transaction $transaction): bool
    {
        if (! $transaction->bank_account_id || $transaction->credit_card_id) {
            return false;
        }

        return in_array($transaction->status, [TransactionStatus::PAID, TransactionStatus::RECONCILED], true);
    }
}
