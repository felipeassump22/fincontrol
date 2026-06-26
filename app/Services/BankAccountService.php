<?php

namespace App\Services;

use App\Models\BankAccount;

/**
 * Service: BankAccountService
 *
 * Gerencia contas bancárias e recálculo de saldos.
 */
class BankAccountService
{
    /**
     * Cria uma nova conta bancária.
     */
    public function create(array $data): BankAccount
    {
        $data['current_balance'] = $data['initial_balance'] ?? 0;

        return BankAccount::create($data);
    }

    /**
     * Atualiza uma conta bancária.
     */
    public function update(BankAccount $account, array $data): BankAccount
    {
        $account->update($data);

        return $account->fresh();
    }

    public function deactivate(BankAccount $account): BankAccount
    {
        $account->update(['is_active' => false]);

        return $account->fresh();
    }

    public function activate(BankAccount $account): BankAccount
    {
        $account->update(['is_active' => true]);

        return $account->fresh();
    }

    /**
     * Recalcula o saldo de uma conta baseado nas transações pagas.
     */
    public function recalculateBalance(BankAccount $account): void
    {
        $income = $account->transactions()
            ->where('status', 'PAID')
            ->where('transaction_type', 'INCOME')
            ->sum('amount');

        $expense = $account->transactions()
            ->where('status', 'PAID')
            ->where('transaction_type', 'EXPENSE')
            ->sum('amount');

        $account->update([
            'current_balance' => $account->initial_balance + $income - $expense,
        ]);
    }

    /**
     * Atualiza o saldo da conta após um pagamento.
     * Incrementa para entradas, decrementa para saídas.
     */
    public function adjustBalance(BankAccount $account, float $amount, string $type): void
    {
        if ($type === 'INCOME') {
            $account->increment('current_balance', $amount);
        } else {
            $account->decrement('current_balance', $amount);
        }
    }

    /**
     * Reverte o ajuste de saldo (usado ao cancelar um pagamento).
     */
    public function revertBalance(BankAccount $account, float $amount, string $type): void
    {
        if ($type === 'INCOME') {
            $account->decrement('current_balance', $amount);
        } else {
            $account->increment('current_balance', $amount);
        }
    }

    /**
     * Retorna contas com saldo negativo para alertas no dashboard.
     */
    public function getAccountsWithNegativeBalance(int $userId)
    {
        return BankAccount::where('user_id', $userId)
            ->active()
            ->where('current_balance', '<', 0)
            ->get();
    }

    /**
     * Simula o impacto de uma transação no saldo.
     * Retorna array com saldo projetado e flag de alerta.
     * RF04 — Alerta de saldo negativo.
     */
    public function simulateImpact(BankAccount $account, float $amount, string $type): array
    {
        $projectedBalance = $account->simulateBalance($amount, $type);

        return [
            'current_balance' => (float) $account->current_balance,
            'projected_balance' => $projectedBalance,
            'will_be_negative' => $projectedBalance < 0,
            'account_name' => $account->name,
        ];
    }
}
