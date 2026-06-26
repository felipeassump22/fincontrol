<?php

namespace App\Http\Controllers;

use App\Http\Requests\PayCreditCardInvoiceRequest;
use App\Http\Requests\StoreCreditCardInstallmentRequest;
use App\Http\Requests\StoreCreditCardRequest;
use App\Models\BankAccount;
use App\Models\Category;
use App\Models\Client;
use App\Models\CreditCard;
use App\Services\CreditCardService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

/**
 * Controller: CreditCardController
 *
 * RF06, RF08, RF19, RF20, RF21
 */
class CreditCardController extends Controller
{
    public function __construct(
        private CreditCardService $creditCardService,
    ) {}

    public function index(Request $request)
    {
        $user = $request->user();
        $ownerId = $user->dataOwnerId();
        $year = (int) $request->get('year', now()->year);
        $month = (int) $request->get('month', now()->month);

        $cards = $this->creditCardService->listWithTotals($ownerId, $month, $year);
        $bankAccounts = BankAccount::where('user_id', $ownerId)->active()->orderBy('name')->get();
        $categories = Cache::remember('categories.all', 86400, fn () => Category::all());
        $clients = Client::where('user_id', $ownerId)->get();

        return view('credit-cards.index', compact(
            'cards', 'bankAccounts', 'categories', 'clients', 'year', 'month'
        ));
    }

    public function store(StoreCreditCardRequest $request)
    {
        $this->authorize('create', CreditCard::class);

        $data = $request->validated();
        $data['user_id'] = $request->user()->dataOwnerId();

        $this->creditCardService->create($data);

        return redirect()->route('credit-cards.index')
            ->with('success', 'Cartão de crédito cadastrado com sucesso!');
    }

    public function update(StoreCreditCardRequest $request, CreditCard $creditCard)
    {
        $this->authorize('update', $creditCard);

        $this->creditCardService->update($creditCard, $request->validated());

        return redirect()->route('credit-cards.index', [
            'year' => $request->get('year', now()->year),
            'month' => $request->get('month', now()->month),
        ])->with('success', 'Cartão atualizado com sucesso!');
    }

    public function show(Request $request, CreditCard $creditCard)
    {
        $this->authorize('view', $creditCard);

        $futureOnly = $request->boolean('future_only');
        $month = (int) $request->get('month', now()->month);
        $year = (int) $request->get('year', now()->year);

        $query = $creditCard->transactions()->with(['category', 'client']);

        if ($futureOnly) {
            $query->where('due_date', '>', now()->toDateString())
                  ->orderBy('due_date', 'asc');
        } else {
            // Get billing period for the selected month/year
            $billingPeriod = $this->creditCardService->getBillingPeriod($creditCard, $year, $month);
            $query->whereBetween('due_date', [$billingPeriod[0]->toDateString(), $billingPeriod[1]->toDateString()])
                  ->orderBy('due_date', 'desc');
        }

        $transactions = $query->paginate(20);

        // Keep existing query parameters when paginating
        $transactions->appends($request->all());

        return view('credit-cards.show', compact('creditCard', 'transactions', 'month', 'year', 'futureOnly'));
    }

    /**
     * RF20 / RF21 — Compra parcelada no cartão (somente transactions).
     */
    public function storeInstallment(StoreCreditCardInstallmentRequest $request, CreditCard $creditCard)
    {
        $this->authorize('storeInstallment', $creditCard);

        $data = $request->validated();
        $data['user_id'] = $request->user()->dataOwnerId();
        $data['credit_card_id'] = $creditCard->id;

        $this->assertBankAccountBelongsToUser($request->user()->dataOwnerId(), (int) $data['bank_account_id']);

        if ($request->boolean('is_recurring')) {
            \App\Models\RecurringExpense::create([
                'description' => $data['description'],
                'amount' => $data['amount'],
                'day_of_month' => \Carbon\Carbon::parse($data['purchase_date'])->day,
                'bank_account_id' => $data['bank_account_id'],
                'category_id' => $data['category_id'] ?? null,
                'user_id' => $data['user_id'],
                'credit_card_id' => $data['credit_card_id'],
                'is_active' => true,
            ]);
            $data['installments'] = 1; // Recorrente é 1 parcela por vez gerada mensalmente
        }

        $created = $this->creditCardService->processInstallmentPurchase($data);

        $message = count($created) > 1
            ? count($created).' parcelas registradas no cartão com sucesso!'
            : 'Compra registrada no cartão com sucesso!';

        return redirect()->route('credit-cards.index', [
            'year' => $request->get('year', now()->year),
            'month' => $request->get('month', now()->month),
        ])->with('success', $message);
    }

    /**
     * Paga a fatura virtual do período (RF08 + RF19).
     */
    public function payInvoice(PayCreditCardInvoiceRequest $request, CreditCard $creditCard)
    {
        $this->authorize('payInvoice', $creditCard);

        $data = $request->validated();
        $this->assertBankAccountBelongsToUser($request->user()->dataOwnerId(), (int) $data['bank_account_id']);

        $month = isset($data['month']) ? (int) $data['month'] : (int) now()->month;
        $year = isset($data['year']) ? (int) $data['year'] : (int) now()->year;

        $summary = $this->creditCardService->getInvoiceSummary($creditCard, $month, $year);

        if ($summary['status'] === \App\Enums\CreditCardInvoiceStatus::PAID) {
            return back()->with('error', 'A fatura deste período já está paga.');
        }

        if ($summary['count'] === 0) {
            return back()->with('error', 'Não há lançamentos pendentes na fatura deste período.');
        }

        $this->creditCardService->payInvoice($creditCard, (int) $data['bank_account_id'], $month, $year);

        return redirect()->route('credit-cards.index', compact('year', 'month'))
            ->with('success', 'Fatura paga! Lançamentos do período baixados automaticamente.');
    }

    private function assertBankAccountBelongsToUser(int $userId, int $bankAccountId): void
    {
        $owns = BankAccount::where('id', $bankAccountId)
            ->where('user_id', $userId)
            ->active()
            ->exists();

        if (! $owns) {
            abort(403, 'Conta bancária inválida ou inativa para este usuário.');
        }
    }
}
