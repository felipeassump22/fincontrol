<?php

namespace App\Policies;

use App\Models\Client;
use App\Models\User;

class ClientPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Client $client): bool
    {
        return $user->ownsFinancialData($client);
    }

    public function create(User $user): bool
    {
        return $user->canManageFinances();
    }

    public function update(User $user, Client $client): bool
    {
        return $user->canManageFinances() && $user->ownsFinancialData($client);
    }

    public function delete(User $user, Client $client): bool
    {
        return $user->canManageFinances() && $user->ownsFinancialData($client);
    }
}
