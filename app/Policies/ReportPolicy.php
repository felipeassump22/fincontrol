<?php

namespace App\Policies;

use App\Models\MonthlyReport;
use App\Models\User;

/**
 * Policy: ReportPolicy
 *
 * Ambos os perfis podem visualizar e exportar relatórios.
 * Somente admin pode fechar o relatório mensal.
 */
class ReportPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, MonthlyReport $report): bool
    {
        return $user->ownsFinancialData($report);
    }

    public function export(User $user): bool
    {
        return true;
    }

    public function close(User $user, MonthlyReport $report): bool
    {
        return $user->isAdmin() && $user->ownsFinancialData($report);
    }
}
