<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use App\Models\Category;
use App\Models\RecurringExpense;
use App\Services\RecurringExpenseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

/**
 * Controller: RecurringExpenseController
 *
 * RF09 — Despesas fixas recorrentes.
 */
class RecurringExpenseController extends Controller
{
    public function __construct(
        private RecurringExpenseService $recurringExpenseService,
    ) {}

    public function index(Request $request)
    {
        $user = $request->user();
        $ownerId = $user->dataOwnerId();
        $expenses = $this->recurringExpenseService->listForUser($ownerId);
        $bankAccounts = BankAccount::where('user_id', $ownerId)->active()->orderBy('name')->get();
        $categories = Cache::remember('categories.expense', 86400, function () {
            return Category::where('type', 'EXPENSE')->get();
        });

        return view('recurring-expenses.index', compact('expenses', 'bankAccounts', 'categories'));
    }

    public function store(Request $request)
    {
        if (! $request->user()->canManageFinances()) {
            abort(403);
        }

        $data = $request->validate([
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'day_of_month' => 'required|integer|between:1,31',
            'bank_account_id' => 'required|exists:bank_accounts,id',
            'category_id' => 'nullable|exists:categories,id',
        ], [
            'description.required' => 'A descrição é obrigatória.',
            'amount.required' => 'O valor é obrigatório.',
            'day_of_month.required' => 'O dia do mês é obrigatório.',
        ]);

        $data['user_id'] = $request->user()->dataOwnerId();
        RecurringExpense::create($data);

        return redirect()->route('recurring-expenses.index')
            ->with('success', 'Despesa recorrente cadastrada com sucesso!');
    }

    public function update(Request $request, RecurringExpense $recurringExpense)
    {
        if (! $request->user()->canManageFinances() || ! $request->user()->ownsFinancialData($recurringExpense)) {
            abort(403, 'Acesso negado.');
        }

        $data = $request->validate([
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'day_of_month' => 'required|integer|between:1,31',
            'bank_account_id' => 'required|exists:bank_accounts,id',
            'category_id' => 'nullable|exists:categories,id',
            'is_active' => 'boolean',
        ]);

        $data['is_active'] = $request->boolean('is_active', true);
        $recurringExpense->update($data);

        return redirect()->route('recurring-expenses.index')
            ->with('success', 'Despesa recorrente atualizada com sucesso!');
    }

    public function destroy(Request $request, RecurringExpense $recurringExpense)
    {
        if (! $request->user()->canManageFinances() || ! $request->user()->ownsFinancialData($recurringExpense)) {
            abort(403, 'Acesso negado.');
        }

        $recurringExpense->delete();

        return redirect()->route('recurring-expenses.index')
            ->with('success', 'Despesa recorrente excluída com sucesso!');
    }
}
