<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Request: StoreCreditCardInstallmentRequest
 *
 * RF20 / RF21 — Compra parcelada no cartão de crédito.
 */
class StoreCreditCardInstallmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null && $this->user()->canManageFinances();
    }

    public function rules(): array
    {
        return [
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'installments' => 'required|integer|min:1|max:48',
            'purchase_date' => 'required|date',
            'bank_account_id' => 'required|exists:bank_accounts,id',
            'category_id' => 'nullable|exists:categories,id',
            'client_id' => 'nullable|exists:clients,id',
            'is_recurring' => 'nullable|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'description.required' => 'A descrição da compra é obrigatória.',
            'amount.min' => 'O valor deve ser maior que zero.',
            'installments.min' => 'Informe pelo menos 1 parcela.',
            'installments.max' => 'O máximo é 48 parcelas.',
            'purchase_date.required' => 'A data da compra é obrigatória.',
            'bank_account_id.required' => 'Selecione a conta para pagamento futuro da fatura.',
        ];
    }
}
