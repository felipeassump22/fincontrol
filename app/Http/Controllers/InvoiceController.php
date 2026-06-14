<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use App\Models\Category;
use App\Models\CreditCard;
use App\Models\Invoice;
use App\Services\InstallmentService;
use Carbon\Carbon;
use Illuminate\Http\Request;

/**
 * Controller: InvoiceController
 *
 * RF07 — Compras parceladas e controle de parcelas.
 */
class InvoiceController extends Controller
{
    public function __construct(
        private InstallmentService $installmentService,
    ) {}

    /**
     * Lista faturas/invoices do usuário com parcelas.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $invoices = Invoice::where('user_id', $user->id)
            ->with(['installments.transaction'])
            ->orderBy('due_date', 'desc')
            ->paginate(20);

        // Enriquecer com dados de parcelas
        foreach ($invoices as $invoice) {
            $invoice->remaining_installments = $this->installmentService->getRemainingInstallments($invoice);
            $invoice->paid_total = $this->installmentService->getPaidTotal($invoice);
        }

        $bankAccounts = BankAccount::where('user_id', $user->id)->get();
        $categories = Category::where('type', 'EXPENSE')->get();
        $creditCards = CreditCard::where('user_id', $user->id)->get();

        return view('invoices.index', compact('invoices', 'bankAccounts', 'categories', 'creditCards'));
    }

    /**
     * Cria uma compra parcelada.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'description' => 'required|string|max:255',
            'total_amount' => 'required|numeric|min:0.01',
            'installments' => 'required|integer|min:2|max:48',
            'first_due_date' => 'required|date',
            'bank_account_id' => 'required|exists:bank_accounts,id',
            'category_id' => 'nullable|exists:categories,id',
            'credit_card_id' => 'nullable|exists:credit_cards,id',
        ], [
            'description.required' => 'A descrição é obrigatória.',
            'total_amount.required' => 'O valor total é obrigatório.',
            'installments.required' => 'O número de parcelas é obrigatório.',
            'installments.min' => 'Mínimo de 2 parcelas.',
        ]);

        $baseData = [
            'description' => $data['description'],
            'transaction_type' => 'EXPENSE',
            'user_id' => $request->user()->id,
            'bank_account_id' => $data['bank_account_id'],
            'category_id' => $data['category_id'] ?? null,
            'credit_card_id' => $data['credit_card_id'] ?? null,
        ];

        $this->installmentService->createInstallmentPurchase(
            $baseData,
            (float) $data['total_amount'],
            (int) $data['installments'],
            Carbon::parse($data['first_due_date'])
        );

        return redirect()->route('invoices.index')
            ->with('success', "Compra parcelada em {$data['installments']}x criada com sucesso!");
    }
}
