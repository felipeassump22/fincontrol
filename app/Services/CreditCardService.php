<?php

namespace App\Services;

use App\Enums\CreditCardInvoiceStatus;
use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Models\CreditCard;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Service: CreditCardService
 *
 * RF06, RF08 — Cartões e pagamento de fatura.
 * RF19 — Controle de cartões (fatura virtual por ciclo de fechamento).
 * RF20 / RF21 — Compras parceladas e geração automática de parcelas em transactions.
 */
class CreditCardService
{
    public function __construct(
        private TransactionService $transactionService,
    ) {}

    /**
     * Cria um novo cartão de crédito.
     */
    public function create(array $data): CreditCard
    {
        return CreditCard::create($data);
    }

    /**
     * Atualiza um cartão de crédito.
     */
    public function update(CreditCard $card, array $data): CreditCard
    {
        $card->update($data);

        return $card->fresh();
    }

    /**
     * RF19 — Resumo da fatura virtual do cartão em um mês/ano de referência.
     *
     * Soma transactions EXPENSE + PENDING com due_date entre o fechamento
     * do mês anterior e o fechamento do mês atual.
     *
     * @return array{total: float, count: int, transactions: Collection, period_start: Carbon, period_end: Carbon, month: int, year: int}
     */
    public function getInvoiceSummary(CreditCard $card, int $month, int $year): array
    {
        [$periodStart, $periodEnd] = $this->getBillingPeriod($card, $year, $month);

        $transactions = $card->transactions()
            ->where('transaction_type', TransactionType::EXPENSE)
            ->where('status', TransactionStatus::PENDING)
            ->whereBetween('due_date', [$periodStart->toDateString(), $periodEnd->toDateString()])
            ->with('category')
            ->orderBy('due_date')
            ->get();

        return [
            'total' => (float) $transactions->sum('amount'),
            'count' => $transactions->count(),
            'transactions' => $transactions,
            'period_start' => $periodStart,
            'period_end' => $periodEnd,
            'month' => $month,
            'year' => $year,
            'status' => $this->getInvoiceStatus($card, $month, $year),
        ];
    }

    /**
     * Req 13 — Status da fatura virtual: ABERTA, FECHADA ou PAGA.
     */
    public function getInvoiceStatus(CreditCard $card, int $month, int $year): CreditCardInvoiceStatus
    {
        [$periodStart, $periodEnd] = $this->getBillingPeriod($card, $year, $month);

        $transactions = $card->transactions()
            ->where('transaction_type', TransactionType::EXPENSE)
            ->whereBetween('due_date', [$periodStart->toDateString(), $periodEnd->toDateString()])
            ->where('status', '!=', TransactionStatus::CANCELED)
            ->get();

        $hasPending = $transactions->contains(fn (Transaction $t) => $t->status === TransactionStatus::PENDING);

        if (! $hasPending) {
            return CreditCardInvoiceStatus::PAID;
        }

        if (now()->startOfDay()->gt($periodEnd)) {
            return CreditCardInvoiceStatus::CLOSED;
        }

        return CreditCardInvoiceStatus::OPEN;
    }

    /**
     * Período de faturamento: [fechamento mês anterior, fechamento mês atual].
     *
     * @return array{0: Carbon, 1: Carbon}
     */
    public function getBillingPeriod(CreditCard $card, int $year, int $month): array
    {
        $currentClosing = $this->closingDateFor($card, $year, $month);
        $previousMonth = Carbon::create($year, $month, 1)->subMonth();
        $previousClosing = $this->closingDateFor($card, $previousMonth->year, $previousMonth->month);

        return [$previousClosing, $currentClosing];
    }

    /**
     * RF20 / RF21 — Registra compra parcelada diretamente em transactions.
     *
     * @return Transaction[]
     */
    public function processInstallmentPurchase(array $data): array
    {
        return DB::transaction(function () use ($data) {
            $card = CreditCard::findOrFail($data['credit_card_id']);
            $totalAmount = (float) $data['amount'];
            $installments = (int) $data['installments'];
            $purchaseDate = Carbon::parse($data['purchase_date'])->startOfDay();

            $installmentAmount = round($totalAmount / $installments, 2);
            $lastInstallmentAmount = $totalAmount - ($installmentAmount * ($installments - 1));

            $created = [];

            for ($i = 0; $i < $installments; $i++) {
                $amount = ($i === $installments - 1) ? $lastInstallmentAmount : $installmentAmount;
                $dueDate = $this->calculateInstallmentDueDate($card, $purchaseDate, $i);

                $description = $data['description'];
                if ($installments > 1) {
                    $description .= ' ('.($i + 1)."/{$installments})";
                }

                $created[] = Transaction::create([
                    'description' => $description,
                    'amount' => $amount,
                    'due_date' => $dueDate->toDateString(),
                    'competence_date' => $purchaseDate->toDateString(),
                    'transaction_type' => TransactionType::EXPENSE,
                    'status' => TransactionStatus::PENDING,
                    'payment_method' => 'CARTAO',
                    'user_id' => $data['user_id'],
                    'bank_account_id' => $data['bank_account_id'],
                    'credit_card_id' => $card->id,
                    'category_id' => $data['category_id'] ?? null,
                    'client_id' => $data['client_id'] ?? null,
                ]);
            }

            return $created;
        });
    }

    /**
     * Calcula o vencimento da primeira parcela com base na data da compra.
     */
    public function calculateFirstInstallmentDueDate(CreditCard $card, Carbon $purchaseDate): Carbon
    {
        if ($purchaseDate->day < $card->closing_day) {
            $targetYear = $purchaseDate->year;
            $targetMonth = $purchaseDate->month;
        } else {
            $next = $purchaseDate->copy()->addMonth();

            return $this->dueDateFor($card, $next->year, $next->month);
        }

        return $this->dueDateFor($card, $targetYear, $targetMonth);
    }

    /**
     * Calcula o due_date de cada parcela (índice zero-based).
     */
    public function calculateInstallmentDueDate(CreditCard $card, Carbon $purchaseDate, int $installmentIndex): Carbon
    {
        $firstDue = $this->calculateFirstInstallmentDueDate($card, $purchaseDate);

        if ($installmentIndex === 0) {
            return $firstDue;
        }

        $target = $firstDue->copy()->addMonths($installmentIndex);

        return $this->dueDateFor($card, $target->year, $target->month);
    }

    /**
     * Paga a fatura virtual do período: marca transações pendentes como pagas.
     * RF08 — Baixa automática ao pagar a fatura.
     */
    public function payInvoice(CreditCard $card, int $bankAccountId, ?int $month = null, ?int $year = null): void
    {
        $month = $month ?? (int) now()->month;
        $year = $year ?? (int) now()->year;

        $summary = $this->getInvoiceSummary($card, $month, $year);

        DB::transaction(function () use ($card, $bankAccountId, $summary, $month, $year) {
            $totalAmount = $summary['total'];

            if ($totalAmount > 0) {
                // Cria uma transação única para o total da fatura
                $invoiceTransaction = Transaction::create([
                    'description' => 'Pagamento Fatura: ' . $card->name . ' (' . str_pad($month, 2, '0', STR_PAD_LEFT) . '/' . $year . ')',
                    'amount' => $totalAmount,
                    'due_date' => now()->toDateString(),
                    'payment_date' => now()->toDateString(),
                    'transaction_type' => TransactionType::EXPENSE,
                    'status' => TransactionStatus::PAID,
                    'user_id' => $card->user_id,
                    'bank_account_id' => $bankAccountId,
                    'credit_card_id' => null, // Deixa nulo para aparecer no fluxo de caixa geral
                ]);

                // Ajusta o saldo da conta pelo total da fatura
                app(BankAccountService::class)->adjustBalance(
                    $invoiceTransaction->bankAccount,
                    $totalAmount,
                    TransactionType::EXPENSE->value
                );
            }

            foreach ($summary['transactions'] as $transaction) {
                // Atualiza o banco e marca como pago sem ajustar o saldo individualmente
                $transaction->update([
                    'bank_account_id' => $bankAccountId,
                    'status' => TransactionStatus::PAID,
                    'payment_date' => now()->toDateString()
                ]);
                $transaction->logCustomAudit('paid', ['status' => 'PENDING'], ['status' => 'PAID']);
            }
        });
    }

    /**
     * @deprecated Use getInvoiceSummary() para fatura por ciclo.
     */
    public function getOpenInvoiceTotal(CreditCard $card): float
    {
        return $this->getInvoiceSummary($card, (int) now()->month, (int) now()->year)['total'];
    }

    /**
     * Lista cartões com resumo da fatura virtual do período selecionado.
     */
    public function listWithTotals(int $userId, ?int $month = null, ?int $year = null)
    {
        $month = $month ?? (int) now()->month;
        $year = $year ?? (int) now()->year;

        $cards = CreditCard::where('user_id', $userId)->get();

        return $cards->map(function (CreditCard $card) use ($month, $year) {
            $summary = $this->getInvoiceSummary($card, $month, $year);

            $card->open_invoice_total = $summary['total'];
            $card->pending_count = $summary['count'];
            $card->invoice_summary = $summary;
            $card->invoice_status = $summary['status'];

            return $card;
        });
    }

    /**
     * Data de fechamento em um mês (ajusta dias inexistentes, ex.: 31 em fevereiro).
     */
    public function closingDateFor(CreditCard $card, int $year, int $month): Carbon
    {
        $daysInMonth = Carbon::create($year, $month, 1)->daysInMonth;
        $day = min($card->closing_day, $daysInMonth);

        return Carbon::create($year, $month, $day)->startOfDay();
    }

    /**
     * Data de vencimento em um mês (ajusta dias inexistentes).
     */
    public function dueDateFor(CreditCard $card, int $year, int $month): Carbon
    {
        $daysInMonth = Carbon::create($year, $month, 1)->daysInMonth;
        $day = min($card->due_day, $daysInMonth);

        return Carbon::create($year, $month, $day)->startOfDay();
    }
}
