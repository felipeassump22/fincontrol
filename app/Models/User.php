<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * Model: User
 *
 * @property int $id
 * @property string $username
 * @property string $email
 * @property string $password_hash
 * @property int $role_id
 * @property bool $is_active
 * @property Carbon|null $last_login_at
 */
class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'username',
        'email',
        'password_hash',
        'role_id',
        'is_active',
        'currency',
        'last_login_at',
    ];

    protected $hidden = [
        'password_hash',
        'remember_token',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_login_at' => 'datetime',
    ];

    /**
     * Sobrescreve o campo de senha padrão do Laravel.
     * A modelagem usa password_hash em vez de password.
     */
    public function getAuthPassword(): string
    {
        return $this->password_hash;
    }

    // ─── Relacionamentos ──────────────────────────

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function bankAccounts(): HasMany
    {
        return $this->hasMany(BankAccount::class);
    }

    public function clients(): HasMany
    {
        return $this->hasMany(Client::class);
    }

    public function creditCards(): HasMany
    {
        return $this->hasMany(CreditCard::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function investments(): HasMany
    {
        return $this->hasMany(Investment::class);
    }

    public function recurringExpenses(): HasMany
    {
        return $this->hasMany(RecurringExpense::class);
    }

    public function monthlyReports(): HasMany
    {
        return $this->hasMany(MonthlyReport::class);
    }

    public function companySetting(): HasOne
    {
        return $this->hasOne(CompanySetting::class);
    }

    // ─── Helpers de Perfil ────────────────────────

    /**
     * Verifica se o usuário é administrador.
     */
    public function isAdmin(): bool
    {
        return $this->role->isAdmin();
    }

    /**
     * Verifica se o usuário é visualizador.
     */
    public function isViewer(): bool
    {
        return $this->role->isViewer();
    }

    /**
     * Verifica se o usuário é do perfil financeiro.
     */
    public function isFinancial(): bool
    {
        return $this->role->isFinancial();
    }

    /**
     * Pode registrar e gerenciar dados financeiros (Admin ou Financeiro).
     */
    public function canManageFinances(): bool
    {
        return $this->isAdmin() || $this->isFinancial();
    }

    /**
     * Verifica se o usuário pode deletar transações.
     */
    public function canDeleteTransactions(): bool
    {
        return $this->role->can_delete_transactions;
    }

    /**
     * ID do titular dos dados financeiros da empresa.
     * Administrador é o dono; Financeiro e Visualizador compartilham os mesmos dados.
     */
    public function dataOwnerId(): int
    {
        if ($this->isAdmin()) {
            return $this->id;
        }

        return static::query()
            ->whereHas('role', fn ($q) => $q->where('name', 'Administrador'))
            ->where('is_active', true)
            ->orderBy('id')
            ->value('id') ?? $this->id;
    }

    /**
     * Verifica se o registro pertence ao escopo financeiro do usuário.
     */
    public function ownsFinancialData(object $record): bool
    {
        return isset($record->user_id) && (int) $record->user_id === $this->dataOwnerId();
    }

    /**
     * Retorna as iniciais do nome para o avatar.
     */
    public function initials(): string
    {
        $words = explode(' ', $this->username);
        $initials = '';
        foreach (array_slice($words, 0, 2) as $word) {
            $initials .= mb_strtoupper(mb_substr($word, 0, 1));
        }

        return $initials;
    }
}
