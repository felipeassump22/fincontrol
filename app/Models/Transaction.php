<?php

namespace App\Models;

use App\Enums\PaymentMethod;
use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Traits\Auditable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Model: Transaction (Lançamento financeiro)
 *
 * Tabela central do sistema. Cada lançamento representa uma entrada ou saída
 * vinculada a uma conta bancária, com possibilidade de vínculo a cartão,
 * cliente, categoria e nota fiscal.
 *
 * @property int $id
 * @property string $description
 * @property float $amount
 * @property Carbon $due_date
 * @property Carbon|null $competence_date
 * @property Carbon|null $payment_date
 * @property TransactionType $transaction_type
 * @property TransactionStatus $status
 * @property PaymentMethod|null $payment_method
 * @property bool $is_recurring
 * @property int $user_id
 * @property int $bank_account_id
 * @property int|null $credit_card_id
 * @property int|null $client_id
 * @property int|null $category_id
 * @property string|null $invoice_document_url
 */
class Transaction extends Model
{
    use Auditable;

    protected $fillable = [
        'description',
        'amount',
        'due_date',
        'competence_date',
        'payment_date',
        'transaction_type',
        'status',
        'payment_method',
        'is_recurring',
        'user_id',
        'bank_account_id',
        'credit_card_id',
        'client_id',
        'category_id',
        'invoice_document_url',
        'reversal_of_id',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'due_date' => 'date',
        'competence_date' => 'date',
        'payment_date' => 'date',
        'transaction_type' => TransactionType::class,
        'status' => TransactionStatus::class,
        'payment_method' => PaymentMethod::class,
        'is_recurring' => 'boolean',
    ];

    // ─── Relacionamentos ──────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class);
    }

    public function creditCard(): BelongsTo
    {
        return $this->belongsTo(CreditCard::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function reversalOf(): BelongsTo
    {
        return $this->belongsTo(Transaction::class, 'reversal_of_id');
    }

    public function reversals(): HasMany
    {
        return $this->hasMany(Transaction::class, 'reversal_of_id');
    }

    public function wasReversed(): bool
    {
        if ($this->relationLoaded('reversals')) {
            return $this->reversals->isNotEmpty();
        }

        return $this->reversals()->exists();
    }

    public function installments(): HasMany
    {
        return $this->hasMany(Installment::class);
    }

    // ─── Scopes ───────────────────────────────────

    public function scopePaid($query)
    {
        return $query->where('status', TransactionStatus::PAID);
    }

    public function scopePending($query)
    {
        return $query->where('status', TransactionStatus::PENDING);
    }

    public function scopeIncome($query)
    {
        return $query->where('transaction_type', TransactionType::INCOME);
    }

    public function scopeExpense($query)
    {
        return $query->where('transaction_type', TransactionType::EXPENSE);
    }

    public function scopeInPeriod($query, $startDate, $endDate)
    {
        return $query->whereBetween('due_date', [$startDate, $endDate]);
    }

    public function scopeForAccount($query, $bankAccountId)
    {
        return $query->where('bank_account_id', $bankAccountId);
    }

    public function scopeForCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    public function scopeForClient($query, $clientId)
    {
        return $query->where('client_id', $clientId);
    }

    // ─── Helpers ──────────────────────────────────

    /**
     * Verifica se o lançamento está pago (RF03 — bloqueio de edição).
     */
    public function isPaid(): bool
    {
        return $this->status === TransactionStatus::PAID;
    }

    public function isReconciled(): bool
    {
        return $this->status === TransactionStatus::RECONCILED;
    }

    public function isCanceled(): bool
    {
        return $this->status === TransactionStatus::CANCELED;
    }

    /**
     * Bloqueia edição (pago, conciliado ou cancelado).
     */
    public function isLockedForEdit(): bool
    {
        return $this->status->isLockedForEdit();
    }

    /**
     * Bloqueia edição pelo perfil Financeiro (pago ou conciliado).
     */
    public function isLockedForFinancial(): bool
    {
        return $this->status->isLockedForFinancial();
    }

    /**
     * Verifica se o lançamento está pendente.
     */
    public function isPending(): bool
    {
        return $this->status === TransactionStatus::PENDING;
    }

    /**
     * Verifica se é uma entrada.
     */
    public function isIncome(): bool
    {
        return $this->transaction_type === TransactionType::INCOME;
    }

    /**
     * Verifica se é uma saída.
     */
    public function isExpense(): bool
    {
        return $this->transaction_type === TransactionType::EXPENSE;
    }
}
