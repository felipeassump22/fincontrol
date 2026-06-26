<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Request: StoreCreditCardRequest
 *
 * RF19 — Validação do CRUD de cartões de crédito.
 */
class StoreCreditCardRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->canManageFinances() ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:100',
            'last_four_digits' => 'required|digits:4',
            'closing_day' => 'required|integer|between:1,31',
            'due_day' => 'required|integer|between:1,31',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'O nome do cartão é obrigatório.',
            'last_four_digits.required' => 'Os 4 últimos dígitos são obrigatórios.',
            'last_four_digits.digits' => 'Informe exatamente 4 dígitos numéricos.',
            'closing_day.between' => 'O dia de fechamento deve estar entre 1 e 31.',
            'due_day.between' => 'O dia de vencimento deve estar entre 1 e 31.',
        ];
    }
}
