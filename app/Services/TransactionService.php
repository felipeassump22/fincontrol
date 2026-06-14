<?php

namespace App\Services;

use App\Enums\TransactionStatus;
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
            // Upload da nota fiscal (RF05)
            if ($invoiceFile) {
                $data['invoice_document_url'] = $invoiceFile->store('invoices', 'public');
            }

            $transaction = Transaction::create($data);

            // Se já está pago na criação, ajustar saldo da conta
            if ($transaction->isPaid()) {
                $this->bankAccountService->adjustBalance(
                    $transaction->bankAccount,
                    (float) $transaction->amount,
                    $transaction->transaction_type->value
                );
            }

            return $transaction;
        });
    }

    /**
     * Atualiza um lançamento.
     * RF03 — Bloquear edição de lançamentos pagos.
     *
     * @throws \Exception se o lançamento estiver pago
     */
    public function update(Transaction $transaction, array $data, ?UploadedFile $invoiceFile = null): Transaction
    {
        if ($transaction->isPaid()) {
            throw new \Exception('Lançamento pago não pode ser editado.');
        }

        return DB::transaction(function () use ($transaction, $data, $invoiceFile) {
            if ($invoiceFile) {
                // Remover arquivo antigo, se existir
                if ($transaction->invoice_document_url) {
                    Storage::disk('public')->delete($transaction->invoice_document_url);
                }
                $data['invoice_document_url'] = $invoiceFile->store('invoices', 'public');
            }

            $wasPending = ! $transaction->isPaid();

            $transaction->fill($data);
            $isNowPaid = $transaction->status->value === 'PAID';

            if ($isNowPaid && ! isset($data['payment_date'])) {
                $transaction->payment_date = now()->toDateString();
            }

            $transaction->save();

            // Se mudou de PENDING para PAID durante a edição, ajustar saldo da conta
            if ($wasPending && $isNowPaid) {
                $this->bankAccountService->adjustBalance(
                    $transaction->bankAccount,
                    (float) $transaction->amount,
                    $transaction->transaction_type->value
                );

                $transaction->logCustomAudit('paid', ['status' => 'PENDING'], ['status' => 'PAID']);
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
        if ($transaction->isPaid()) {
            throw new \Exception('Lançamento já está pago.');
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
     * Deleta um lançamento.
     * RF01 — Somente usuários com permissão.
     * Se estava pago, reverte o saldo.
     */
    public function delete(Transaction $transaction): void
    {
        DB::transaction(function () use ($transaction) {
            // Se estava pago, reverter o saldo da conta
            if ($transaction->isPaid()) {
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
        $query = Transaction::with(['bankAccount', 'category', 'client', 'creditCard'])
            ->where('user_id', $userId)
            ->orderBy('due_date', 'desc');

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['transaction_type'])) {
            $query->where('transaction_type', $filters['transaction_type']);
        }

        if (! empty($filters['bank_account_id'])) {
            $query->forAccount($filters['bank_account_id']);
        }

        if (! empty($filters['category_id'])) {
            $query->forCategory($filters['category_id']);
        }

        if (! empty($filters['client_id'])) {
            $query->forClient($filters['client_id']);
        }

        if (! empty($filters['date_from'])) {
            $query->where('due_date', '>=', $filters['date_from']);
        }

        if (! empty($filters['date_to'])) {
            $query->where('due_date', '<=', $filters['date_to']);
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
}
