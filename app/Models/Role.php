<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Model: Role (Perfil de acesso)
 *
 * @property int $id
 * @property string $name
 * @property string|null $description
 * @property bool $can_delete_transactions
 */
class Role extends Model
{
    protected $fillable = [
        'name',
        'description',
        'can_delete_transactions',
    ];

    protected $casts = [
        'can_delete_transactions' => 'boolean',
    ];

    // ─── Relacionamentos ──────────────────────────

    /**
     * Um perfil possui muitos usuários.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    // ─── Helpers ──────────────────────────────────

    /**
     * Verifica se é o perfil de administrador.
     */
    public function isAdmin(): bool
    {
        return $this->name === 'Administrador';
    }

    /**
     * Verifica se é o perfil de visualizador.
     */
    public function isViewer(): bool
    {
        return $this->name === 'Visualizador';
    }

    /**
     * Verifica se é o perfil financeiro.
     */
    public function isFinancial(): bool
    {
        return $this->name === 'Financeiro';
    }
}
