<?php

namespace App\Traits;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

/**
 * Trait Auditable
 *
 * Registra automaticamente alterações em modelos no audit_logs.
 * Adicione `use Auditable;` em qualquer Model que precise de auditoria.
 *
 * @mixin Model
 *
 * @method static void created(callable $callback)
 * @method static void updated(callable $callback)
 * @method static void deleted(callable $callback)
 */
trait Auditable
{
    /**
     * Boot do trait: registra observers para created, updated e deleted.
     */
    public static function bootAuditable(): void
    {
        static::created(function ($model) {
            $model->logAudit('created', null, $model->getAttributes());
        });

        static::updated(function ($model) {
            $original = $model->getOriginal();
            $changes = $model->getChanges();

            // Remove timestamps das mudanças para não poluir o log
            unset($changes['updated_at'], $changes['created_at']);

            if (! empty($changes)) {
                $oldValues = array_intersect_key($original, $changes);
                $model->logAudit('updated', $oldValues, $changes);
            }
        });

        static::deleted(function ($model) {
            $model->logAudit('deleted', $model->getAttributes(), null);
        });
    }

    /**
     * Registra uma ação customizada no log de auditoria.
     */
    public function logCustomAudit(string $action, ?array $oldValues = null, ?array $newValues = null): void
    {
        $this->logAudit($action, $oldValues, $newValues);
    }

    /**
     * Cria o registro de auditoria no banco.
     */
    protected function logAudit(string $action, ?array $oldValues, ?array $newValues): void
    {
        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => $action,
            'auditable_type' => static::class,
            'auditable_id' => $this->getKey(),
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => Request::ip(),
            'created_at' => now(),
        ]);
    }
}
