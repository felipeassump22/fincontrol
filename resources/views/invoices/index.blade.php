@extends('layouts.app')
@section('title', 'Parcelamentos')

@php
    /** @var \App\Models\User $user */
    $user = auth()->user();
    /** @var \Illuminate\Pagination\LengthAwarePaginator|\App\Models\Invoice[] $invoices */
    /** @var \App\Models\Invoice $inv */
@endphp

@section('content')
<div class="topbar">
    <span class="topbar-title">{{ __('Parcelamentos') }}</span>
    @if($user->canManageFinances())
        <button class="btn btn-primary" onclick="openModal('modal-installment')"><i class="ti ti-plus"></i>{{ __('Nova compra parcelada') }}</button>
    @endif
</div>

<div class="content">
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>{{ __('Nº Invoice') }}</th><th>{{ __('Descrição') }}</th><th>{{ __('Total') }}</th><th>{{ __('Pago') }}</th>
                    <th>{{ __('Parcelas restantes') }}</th><th>{{ __('Status') }}</th><th>{{ __('Vencimento') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($invoices as $inv)
                    <tr>
                        <td style="font-size:12px;color:var(--color-text-tertiary)">{{ $inv->invoice_number ?? '—' }}</td>
                        <td style="font-weight:500">{{ $inv->description }}</td>
                        <td>{{ money($inv->total_amount) }}</td>
                        <td style="color:var(--color-text-success)">{{ money($inv->paid_total) }}</td>
                        <td>
                            @if($inv->remaining_installments > 0)
                                <span class="badge badge-warning">{{ $inv->remaining_installments }} {{ __('restantes') }}</span>
                            @else
                                <span class="badge badge-success">{{ __('Todas pagas') }}</span>
                            @endif
                        </td>
                        <td><span class="badge {{ $inv->status->badgeClass() }}">{{ $inv->status->label() }}</span></td>
                        <td style="font-size:12px">{{ $inv->due_date->format('d/m/Y') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="7" style="color:var(--color-text-tertiary);text-align:center">{{ __('Nenhum parcelamento encontrado.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($invoices->hasPages())
        {{ $invoices->links() }}
    @endif
</div>

{{-- Modal: Nova compra parcelada --}}
<div class="modal-overlay" id="modal-installment">
    <div class="modal">
        <div class="modal-header">
            <h3>{{ __('Nova compra parcelada') }}</h3>
            <i class="ti ti-x" style="cursor:pointer;font-size:18px;color:var(--color-text-secondary)" onclick="closeModal('modal-installment')"></i>
        </div>
        <form method="POST" action="{{ route('invoices.store') }}">
            @csrf
            <div class="form-group">
                <label class="form-label">{{ __('Descrição') }}</label>
                <input type="text" name="description" placeholder="{{ __('Ex: Notebook Dell Latitude') }}" required>
            </div>
            <div class="form-row-3">
                <div class="form-group">
                    <label class="form-label">{{ __('Valor total') }}</label>
                    <input type="number" name="total_amount" step="0.01" min="0.01" placeholder="0,00" required>
                </div>
                <div class="form-group">
                    <label class="form-label">{{ __('Nº de parcelas') }}</label>
                    <input type="number" name="installments" min="2" max="48" value="3" required>
                </div>
                <div class="form-group">
                    <label class="form-label">{{ __('1ª parcela') }}</label>
                    <input type="date" name="first_due_date" required>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">{{ __('Conta bancária') }}</label>
                    <select name="bank_account_id" required>
                        <option value="">{{ __('— Selecione —') }}</option>
                        @foreach($bankAccounts as $account)
                            <option value="{{ $account->id }}">{{ $account->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">{{ __('Categoria') }}</label>
                    <select name="category_id">
                        <option value="">{{ __('— Selecione —') }}</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">{{ __('Cartão de crédito (opcional)') }}</label>
                <select name="credit_card_id">
                    <option value="">{{ __('— Nenhum —') }}</option>
                    @foreach($creditCards as $card)
                        <option value="{{ $card->id }}">{{ $card->displayName() }}</option>
                    @endforeach
                </select>
            </div>
            <div style="display:flex;justify-content:flex-end;gap:8px;margin-top:16px">
                <button type="button" class="btn" onclick="closeModal('modal-installment')">{{ __('Cancelar') }}</button>
                <button type="submit" class="btn btn-primary">{{ __('Criar parcelamento') }}</button>
            </div>
        </form>
    </div>
</div>
@endsection
