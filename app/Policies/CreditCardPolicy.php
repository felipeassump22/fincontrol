<?php

namespace App\Policies;

use App\Models\CreditCard;
use App\Models\User;

class CreditCardPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, CreditCard $creditCard): bool
    {
        return $user->ownsFinancialData($creditCard);
    }

    public function create(User $user): bool
    {
        return $user->canManageFinances();
    }

    public function update(User $user, CreditCard $creditCard): bool
    {
        return $user->canManageFinances() && $user->ownsFinancialData($creditCard);
    }

    public function payInvoice(User $user, CreditCard $creditCard): bool
    {
        return $user->canManageFinances() && $user->ownsFinancialData($creditCard);
    }

    public function storeInstallment(User $user, CreditCard $creditCard): bool
    {
        return $user->canManageFinances() && $user->ownsFinancialData($creditCard);
    }
}
