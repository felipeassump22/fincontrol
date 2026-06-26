<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTransactionRequest;
use App\Models\BankAccount;
use App\Models\Category;
use App\Models\Client;
use App\Models\CreditCard;
use App\Models\Transaction;
use App\Services\CreditCardService;
use App\Services\TransactionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

/**
 * Controller: TransactionController
 *
 * CRUD completo de lançamentos financeiros.
 * RF20 / RF21 — Compras parceladas no cartão via transactions.
 */
class TransactionController extends Controller
{
    public function __construct(
        private TransactionService $transactionService,
        private CreditCardService $creditCardService,
    ) {}

    /**
     * Lista lançamentos com filtros.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $ownerId = $user->dataOwnerId();
        $filters = $request->only([
            'status', 'transaction_type', 'bank_account_id', 'category_id',
            'client_id', 'date_from', 'date_to', 'period', 'quick_period',
        ]);

        $filters = $this->transactionService->resolvePeriodFilters($filters);

        $transactions = $this->transactionService->list($ownerId, $filters);
        $transactions->getCollection()->load(['reversals']);
        $bankAccounts = BankAccount::where('user_id', $ownerId)->active()->orderBy('name')->get();
        $categories = Cache::remember('categories.all', 86400, function () {
            return Category::all();
        });
        $clients = Client::where('user_id', $ownerId)->get();
        $creditCards = CreditCard::where('user_id', $ownerId)->get();

        // Dados para o Gráfico do Topo (Agrupado por data)
        $chartQuery = Transaction::where('user_id', $ownerId)
            ->whereNull('credit_card_id');
        
        if (! empty($filters['period'])) {
            $days = (int) $filters['period'];
            $chartQuery->whereBetween('due_date', [now()->toDateString(), now()->addDays($days)->toDateString()]);
        } else {
            $chartQuery->whereBetween('due_date', [
                $filters['date_from'] ?? now()->startOfMonth()->toDateString(),
                $filters['date_to'] ?? now()->endOfMonth()->toDateString()
            ]);
        }
        
        $chartData = $chartQuery->selectRaw('DATE(due_date) as date, transaction_type, SUM(amount) as total')
            ->groupBy('date', 'transaction_type')
            ->orderBy('date', 'asc')
            ->get();

        return view('transactions.index', [
            'transactions' => $transactions,
            'bankAccounts' => $bankAccounts,
            'categories' => $categories,
            'clients' => $clients,
            'creditCards' => $creditCards,
            'filters' => $filters,
            'chartData' => $chartData,
        ]);
    }

    /**
     * Armazena um novo lançamento.
     */
    public function store(StoreTransactionRequest $request)
    {
        $data = $request->validated();
        $ownerId = $request->user()->dataOwnerId();
        $data['user_id'] = $ownerId;

        $this->assertBankAccountBelongsToUser($ownerId, (int) $data['bank_account_id']);

        if (! empty($data['credit_card_id']) && $data['transaction_type'] === 'EXPENSE') {
            $this->assertCreditCardBelongsToUser($ownerId, (int) $data['credit_card_id']);

            $purchaseData = [
                'user_id' => $data['user_id'],
                'credit_card_id' => $data['credit_card_id'],
                'description' => $data['description'],
                'amount' => $data['amount'],
                'installments' => (int) ($data['installments'] ?? 1),
                'purchase_date' => $data['purchase_date'],
                'bank_account_id' => $data['bank_account_id'],
                'category_id' => $data['category_id'] ?? null,
                'client_id' => $data['client_id'] ?? null,
            ];

            $created = $this->creditCardService->processInstallmentPurchase($purchaseData);

            $message = count($created) > 1
                ? count($created).' parcelas criadas no cartão com sucesso!'
                : 'Lançamento no cartão criado com sucesso!';

            return redirect()->route('transactions.index')->with('success', $message);
        }

        $invoiceFile = $request->file('invoice_document');
        $this->transactionService->create($data, $invoiceFile);

        return redirect()->route('transactions.index')
            ->with('success', 'Lançamento criado com sucesso!');
    }

    /**
     * Atualiza um lançamento existente.
     */
    public function update(StoreTransactionRequest $request, Transaction $transaction)
    {
        $this->authorize('update', $transaction);

        try {
            $data = $request->validated();
            $invoiceFile = $request->file('invoice_document');

            $this->assertBankAccountBelongsToUser(
                $request->user()->dataOwnerId(),
                (int) $data['bank_account_id'],
                ! $transaction->bank_account_id || (int) $data['bank_account_id'] !== $transaction->bank_account_id
            );

            $this->transactionService->update($transaction, $data, $invoiceFile);

            return redirect()->route('transactions.index')
                ->with('success', 'Lançamento atualizado com sucesso!');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Marca um lançamento como pago.
     */
    public function pay(Transaction $transaction)
    {
        $this->authorize('pay', $transaction);

        try {
            $this->transactionService->markAsPaid($transaction);

            return redirect()->route('transactions.index')
                ->with('success', 'Lançamento marcado como pago!');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function reconcile(Transaction $transaction)
    {
        $this->authorize('reconcile', $transaction);

        try {
            $this->transactionService->markAsReconciled($transaction);

            return redirect()->route('transactions.index')
                ->with('success', 'Lançamento conciliado!');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function cancel(Transaction $transaction)
    {
        $this->authorize('cancel', $transaction);

        try {
            $this->transactionService->cancel($transaction);

            return redirect()->route('transactions.index')
                ->with('success', 'Lançamento cancelado!');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function reverse(Transaction $transaction)
    {
        $this->authorize('reverse', $transaction);

        try {
            $this->transactionService->reverse($transaction);

            return redirect()->route('transactions.index')
                ->with('success', 'Lançamento estornado com sucesso!');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Remove um lançamento.
     */
    public function destroy(Transaction $transaction)
    {
        $this->authorize('delete', $transaction);

        $this->transactionService->delete($transaction);

        return redirect()->route('transactions.index')
            ->with('success', 'Lançamento excluído com sucesso!');
    }

    /**
     * Verifica o impacto no saldo da conta (AJAX).
     * RF04 — Alerta de saldo negativo.
     */
    public function checkImpact(Request $request)
    {
        if (! $request->user()->canManageFinances()) {
            abort(403, 'Você não tem permissão para esta ação.');
        }

        $ownerId = $request->user()->dataOwnerId();

        $request->validate([
            'bank_account_id' => 'required|exists:bank_accounts,id',
            'amount' => 'required|numeric|min:0.01',
            'transaction_type' => 'required|in:INCOME,EXPENSE',
        ]);

        $this->assertBankAccountBelongsToUser($ownerId, (int) $request->bank_account_id);

        $impact = $this->transactionService->checkBalanceImpact(
            $request->bank_account_id,
            $request->amount,
            $request->transaction_type
        );

        return response()->json($impact);
    }

    private function assertBankAccountBelongsToUser(int $userId, int $bankAccountId, bool $mustBeActive = true): void
    {
        $query = BankAccount::where('id', $bankAccountId)->where('user_id', $userId);

        if ($mustBeActive) {
            $query->active();
        }

        if (! $query->exists()) {
            abort(403, 'Conta bancária inválida ou inativa para este usuário.');
        }
    }

    private function assertCreditCardBelongsToUser(int $userId, int $creditCardId): void
    {
        if (! CreditCard::where('id', $creditCardId)->where('user_id', $userId)->exists()) {
            abort(403, 'Cartão de crédito inválido para este usuário.');
        }
    }
}
