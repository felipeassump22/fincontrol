<?php

namespace App\Http\Requests;

use App\Models\Category;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Request: StoreTransactionRequest
 *
 * Validação server-side para criação/atualização de lançamentos.
 */
class StoreTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        if ($this->isMethod('post')) {
            return $this->user()?->can('create', \App\Models\Transaction::class) ?? false;
        }

        $transaction = $this->route('transaction');

        return $transaction && $this->user()?->can('update', $transaction);
    }

    public function rules(): array
    {
        $hasCreditCard = $this->filled('credit_card_id') && $this->input('transaction_type') === 'EXPENSE';

        return [
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'due_date' => [
                Rule::requiredIf(! $hasCreditCard),
                'nullable',
                'date',
            ],
            'competence_date' => 'nullable|date',
            'purchase_date' => [
                Rule::requiredIf($hasCreditCard),
                'nullable',
                'date',
            ],
            'payment_method' => [
                Rule::requiredIf(! $hasCreditCard),
                'nullable',
                Rule::in(['PIX', 'BOLETO', 'CARTAO', 'TRANSFERENCIA', 'DINHEIRO', 'OUTRO']),
            ],
            'installments' => [
                Rule::requiredIf($hasCreditCard),
                'nullable',
                'integer',
                'min:1',
                'max:48',
            ],
            'transaction_type' => 'required|in:INCOME,EXPENSE',
            'bank_account_id' => 'required|exists:bank_accounts,id',
            'credit_card_id' => 'nullable|exists:credit_cards,id',
            'client_id' => [
                'nullable',
                'exists:clients,id',
                function ($attribute, $value, $fail) {
                    if ($this->input('transaction_type') !== 'INCOME' || ! $this->filled('category_id')) {
                        return;
                    }

                    $category = Category::find($this->input('category_id'));
                    if ($category?->requires_client && empty($value)) {
                        $fail(__('Esta categoria exige a seleção de um cliente.'));
                    }
                },
            ],
            'category_id' => [
                'nullable',
                'exists:categories,id',
                function ($attribute, $value, $fail) {
                    if (! $value) {
                        return;
                    }

                    $category = Category::find($value);
                    if ($category && ! $category->appliesToTransactionType($this->input('transaction_type'))) {
                        $fail(__('A categoria selecionada não é compatível com o tipo do lançamento.'));
                    }
                },
            ],
            'invoice_document' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'is_recurring' => 'nullable|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'description.required' => 'A descrição é obrigatória.',
            'amount.required' => 'O valor é obrigatório.',
            'amount.min' => 'O valor deve ser maior que zero.',
            'due_date.required' => 'A data de vencimento é obrigatória.',
            'competence_date.date' => 'Informe uma data de competência válida.',
            'payment_method.required' => 'O método de pagamento é obrigatório.',
            'purchase_date.required' => 'A data da compra é obrigatória para lançamentos no cartão.',
            'installments.required' => 'Informe o número de parcelas.',
            'installments.min' => 'Informe pelo menos 1 parcela.',
            'transaction_type.required' => 'O tipo de lançamento é obrigatório.',
            'bank_account_id.required' => 'A conta bancária é obrigatória.',
            'invoice_document.max' => 'O arquivo da nota fiscal deve ter no máximo 5MB.',
        ];
    }
}
