<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Model: Category (Categoria financeira)
 *
 * @property int $id
 * @property string $name
 * @property string $type INCOME|EXPENSE
 */
class Category extends Model
{
    protected $fillable = [
        'name',
        'type',
        'requires_client',
    ];

    protected $casts = [
        'requires_client' => 'boolean',
    ];

    // ─── Relacionamentos ──────────────────────────

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    // ─── Scopes ───────────────────────────────────

    public function scopeIncome($query)
    {
        return $query->where('type', 'INCOME');
    }

    public function scopeExpense($query)
    {
        return $query->where('type', 'EXPENSE');
    }

    /**
     * Categorias aplicáveis a um tipo de lançamento.
     */
    public function scopeForTransactionType($query, string $transactionType)
    {
        return $query->where(function ($q) use ($transactionType) {
            $q->where('type', $transactionType)->orWhere('type', 'BOTH');
        });
    }

    public function appliesToTransactionType(string $transactionType): bool
    {
        return $this->type === 'BOTH' || $this->type === $transactionType;
    }
}
