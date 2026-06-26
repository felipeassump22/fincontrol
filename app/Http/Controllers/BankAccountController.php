<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use App\Services\BankAccountService;
use Illuminate\Http\Request;

/**
 * Controller: BankAccountController
 */
class BankAccountController extends Controller
{
    public function __construct(
        private BankAccountService $bankAccountService,
    ) {}

    public function index(Request $request)
    {
        $accounts = BankAccount::where('user_id', $request->user()->dataOwnerId())
            ->orderByDesc('is_active')
            ->orderBy('name')
            ->get();

        return view('bank-accounts.index', compact('accounts'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', BankAccount::class);

        $data = $request->validate([
            'name' => 'required|string|max:100',
            'initial_balance' => 'required|numeric',
            'agency' => 'nullable|string|max:20',
            'account_number' => 'nullable|string|max:30',
            'pix_key' => 'nullable|string|max:100',
            'document' => 'nullable|string|max:18',
        ], [
            'name.required' => 'O nome da conta é obrigatório.',
        ]);

        $data['user_id'] = $request->user()->dataOwnerId();
        $data['is_active'] = true;
        $this->bankAccountService->create($data);

        return redirect()->route('bank-accounts.index')
            ->with('success', 'Conta bancária criada com sucesso!');
    }

    public function update(Request $request, BankAccount $bankAccount)
    {
        $this->authorize('update', $bankAccount);

        $data = $request->validate([
            'name' => 'required|string|max:100',
            'agency' => 'nullable|string|max:20',
            'account_number' => 'nullable|string|max:30',
            'pix_key' => 'nullable|string|max:100',
            'document' => 'nullable|string|max:18',
        ]);

        $this->bankAccountService->update($bankAccount, $data);

        return redirect()->route('bank-accounts.index')
            ->with('success', 'Conta bancária atualizada com sucesso!');
    }

    public function deactivate(Request $request, BankAccount $bankAccount)
    {
        $this->authorize('update', $bankAccount);

        if (! $bankAccount->is_active) {
            return back()->with('error', 'Esta conta já está inativa.');
        }

        $this->bankAccountService->deactivate($bankAccount);

        return redirect()->route('bank-accounts.index')
            ->with('success', 'Conta bancária desativada. Os lançamentos foram preservados.');
    }

    public function activate(Request $request, BankAccount $bankAccount)
    {
        $this->authorize('update', $bankAccount);

        if ($bankAccount->is_active) {
            return back()->with('error', 'Esta conta já está ativa.');
        }

        $this->bankAccountService->activate($bankAccount);

        return redirect()->route('bank-accounts.index')
            ->with('success', 'Conta bancária reativada com sucesso!');
    }
}
