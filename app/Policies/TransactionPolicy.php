<?php

namespace App\Policies;

use App\Enums\TransactionStatus;
use App\Models\Transaction;
use App\Models\User;

/**
 * Policy: TransactionPolicy
 *
 * RF01 — Controle de acesso.
 * RF03 / Req 6 — Bloquear edição de lançamentos pagos/conciliados para Financeiro.
 */
class TransactionPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Transaction $transaction): bool
    {
        return $user->ownsFinancialData($transaction);
    }

    public function create(User $user): bool
    {
        return $user->canManageFinances();
    }

    public function update(User $user, Transaction $transaction): bool
    {
        if (! $user->ownsFinancialData($transaction) || ! $user->canManageFinances()) {
            return false;
        }

        if ($transaction->status === TransactionStatus::CANCELED) {
            return false;
        }

        if ($user->isFinancial() && $transaction->status !== TransactionStatus::PENDING) {
            return false;
        }

        return true;
    }

    public function delete(User $user, Transaction $transaction): bool
    {
        return $user->isAdmin()
            && $user->ownsFinancialData($transaction);
    }

    public function pay(User $user, Transaction $transaction): bool
    {
        if ($transaction->status !== TransactionStatus::PENDING) {
            return false;
        }

        return $user->canManageFinances() && $user->ownsFinancialData($transaction);
    }

    public function reconcile(User $user, Transaction $transaction): bool
    {
        if ($transaction->status !== TransactionStatus::PAID) {
            return false;
        }

        return $user->canManageFinances() && $user->ownsFinancialData($transaction);
    }

    public function cancel(User $user, Transaction $transaction): bool
    {
        if ($transaction->status !== TransactionStatus::PENDING) {
            return false;
        }

        return $user->canManageFinances() && $user->ownsFinancialData($transaction);
    }

    public function reverse(User $user, Transaction $transaction): bool
    {
        if (! $user->ownsFinancialData($transaction) || ! $user->canManageFinances()) {
            return false;
        }

        if (! in_array($transaction->status, [TransactionStatus::PAID, TransactionStatus::RECONCILED], true)) {
            return false;
        }

        if ($transaction->wasReversed() || $transaction->credit_card_id) {
            return false;
        }

        return true;
    }
}
