<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Request: PayCreditCardInvoiceRequest
 *
 * RF19 — Validação do pagamento da fatura virtual do cartão.
 */
class PayCreditCardInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->canManageFinances() ?? false;
    }

    public function rules(): array
    {
        return [
            'bank_account_id' => 'required|exists:bank_accounts,id',
            'month' => 'nullable|integer|between:1,12',
            'year' => 'nullable|integer|min:2000|max:2100',
        ];
    }

    public function messages(): array
    {
        return [
            'bank_account_id.required' => 'Selecione a conta para débito da fatura.',
            'bank_account_id.exists' => 'Conta bancária inválida.',
        ];
    }
}
