<?php

namespace App\Providers;

use App\Models\MonthlyReport;
use App\Models\Transaction;
use App\Models\User;
use App\Policies\ReportPolicy;
use App\Policies\TransactionPolicy;
use App\Policies\UserPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

/**
 * Provider: Registra as Policies do sistema.
 */
class AuthServiceProvider extends ServiceProvider
{
    /**
     * Mapeamento Model → Policy.
     */
    protected $policies = [
        Transaction::class => TransactionPolicy::class,
        User::class => UserPolicy::class,
        MonthlyReport::class => ReportPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();
    }
}
