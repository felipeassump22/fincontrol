<?php

namespace App\Policies;

use App\Models\BankAccount;
use App\Models\User;

class BankAccountPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, BankAccount $bankAccount): bool
    {
        return $user->ownsFinancialData($bankAccount);
    }

    public function create(User $user): bool
    {
        return $user->canManageFinances();
    }

    public function update(User $user, BankAccount $bankAccount): bool
    {
        return $user->canManageFinances() && $user->ownsFinancialData($bankAccount);
    }
}
