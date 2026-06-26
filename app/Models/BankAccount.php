<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Model: BankAccount (Conta bancária)
 *
 * @property int $id
 * @property string $name
 * @property float $initial_balance
 * @property float $current_balance
 * @property int $user_id
 */
class BankAccount extends Model
{
    use Auditable;

    protected $fillable = [
        'name',
        'initial_balance',
        'current_balance',
        'user_id',
        'is_active',
        'pix_key',
        'document',
        'agency',
        'account_number',
    ];

    protected $casts = [
        'initial_balance' => 'decimal:2',
        'current_balance' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    // ─── Relacionamentos ──────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function recurringExpenses(): HasMany
    {
        return $this->hasMany(RecurringExpense::class);
    }

    // ─── Scopes ───────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // ─── Helpers ──────────────────────────────────

    /**
     * Verifica se o saldo atual é negativo.
     */
    public function isNegative(): bool
    {
        return $this->current_balance < 0;
    }

    /**
     * Simula o impacto de uma transação no saldo.
     * Retorna o saldo projetado após a operação.
     */
    public function simulateBalance(float $amount, string $type): float
    {
        if ($type === 'EXPENSE') {
            return $this->current_balance - $amount;
        }

        return $this->current_balance + $amount;
    }
}
