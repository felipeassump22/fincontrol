@extends('layouts.app')
@section('title', 'Cartões de crédito')

@section('content')
<div class="topbar">
    <span class="topbar-title">{{ __('Cartões de crédito') }}</span>
    <div class="topbar-actions" style="display:flex;gap:8px;align-items:center">
        <form method="GET" action="{{ route('credit-cards.index') }}" style="display:flex;gap:8px;align-items:center">
            <select name="month" style="width:auto;font-size:12px;padding:5px 8px" onchange="this.form.submit()">
                @for($m = 1; $m <= 12; $m++)
                    <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                        {{ \Carbon\Carbon::create(null, $m)->translatedFormat('F') }}
                    </option>
                @endfor
            </select>
            <select name="year" style="width:auto;font-size:12px;padding:5px 8px" onchange="this.form.submit()">
                @for($y = now()->year - 2; $y <= now()->year + 1; $y++)
                    <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                @endfor
            </select>
        </form>
        @if(auth()->user()->canManageFinances())
            <button class="btn btn-primary" onclick="openModal('modal-card')"><i class="ti ti-plus"></i>{{ __('Novo cartão') }}</button>
        @endif
    </div>
</div>

<div class="content">
    @if($cards->isEmpty())
        <div style="text-align:center;padding:40px 20px;margin-top:20px;">
            <div style="width:64px;height:64px;border-radius:50%;background:var(--color-background-secondary);display:flex;align-items:center;justify-content:center;margin:0 auto 16px;">
                <i class="ti ti-credit-card-off" style="font-size:32px;color:var(--color-text-tertiary);"></i>
            </div>
            <h3 style="margin-bottom:8px;color:var(--color-text-primary);">{{ __('Nenhum cartão cadastrado') }}</h3>
            <p style="color:var(--color-text-secondary);margin-bottom:24px;font-size:14px;max-width:400px;margin-left:auto;margin-right:auto;">
                {{ __('Cadastre seu primeiro cartão para gerenciar faturas virtuais e compras parceladas.') }}
            </p>
            @if(auth()->user()->canManageFinances())
                <button class="btn btn-primary" onclick="openModal('modal-card')" style="margin: 0 auto;">
                    <i class="ti ti-plus"></i>{{ __('Cadastrar Cartão') }}
                </button>
            @endif
        </div>
    @else
        <div class="grid-3">
            @foreach($cards as $card)
                @php $summary = $card->invoice_summary; @endphp
                <div class="card">
                    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px">
                        <div style="display:flex;align-items:center;gap:10px">
                            <div style="width:38px;height:38px;border-radius:var(--border-radius-md);background:var(--color-background-warning);display:flex;align-items:center;justify-content:center">
                                <i class="ti ti-credit-card" style="color:var(--color-text-warning);font-size:18px"></i>
                            </div>
                            <div>
                                <div style="font-size:14px;font-weight:500">{{ $card->name }}</div>
                                <div style="font-size:12px;color:var(--color-text-secondary)">•••• {{ $card->last_four_digits }}</div>
                            </div>
                        </div>
                        @if(auth()->user()->canManageFinances())
                            <i class="ti ti-edit action-icon" onclick="openModal('modal-edit-{{ $card->id }}')" title="{{ __('Editar') }}"></i>
                        @endif
                    </div>
                    <div class="divider"></div>
                    <div style="display:flex;justify-content:space-between;font-size:12px;color:var(--color-text-secondary);margin-bottom:8px">
                        <span>{{ __('Fecha dia') }} {{ $card->closing_day }}</span>
                        <span>{{ __('Vence dia') }} {{ $card->due_day }}</span>
                    </div>
                    <div style="font-size:11px;color:var(--color-text-tertiary);margin-bottom:4px">
                        {{ __('Fatura virtual') }} — {{ $summary['period_start']->format('d/m') }} {{ __('a') }} {{ $summary['period_end']->format('d/m/Y') }}
                    </div>
                    @php $invoiceStatus = $card->invoice_status ?? $summary['status']; @endphp
                    <div style="margin-bottom:8px">
                        <span class="badge {{ $invoiceStatus->badgeClass() }}">{{ $invoiceStatus->label() }}</span>
                    </div>
                    <div style="font-size:20px;font-weight:500;color:{{ $card->open_invoice_total > 0 ? 'var(--color-text-warning)' : 'var(--color-text-success)' }}">
                        {{ money($card->open_invoice_total) }}
                    </div>
                    <div style="font-size:11px;color:var(--color-text-tertiary);margin-bottom:12px">{{ $card->pending_count }} {{ __('lançamentos pendentes no período') }}</div>

                    <button class="btn btn-sm" onclick="openModal('modal-invoice-{{ $card->id }}')" style="width:100%;justify-content:center;margin-bottom:8px">
                        <i class="ti ti-list-details"></i>{{ __('Ver detalhes da fatura') }}
                    </button>
                    <a href="{{ route('credit-cards.show', $card) }}" class="btn btn-sm" style="width:100%;justify-content:center;background:var(--color-background-secondary);color:var(--color-text-primary);text-decoration:none">
                        <i class="ti ti-filter"></i>{{ __('Lançamentos detalhados') }}
                    </a>

                    @if(auth()->user()->canManageFinances())
                        <div style="display:flex;flex-direction:column;gap:8px;margin-top:8px">
                            <button class="btn btn-sm" onclick="openModal('modal-installment-{{ $card->id }}')" style="width:100%;justify-content:center">
                                <i class="ti ti-shopping-cart"></i>{{ __('Compra parcelada') }}
                            </button>
                            @if($card->open_invoice_total > 0 && ($invoiceStatus->value ?? '') !== 'PAID')
                                <button class="btn btn-sm btn-success" onclick="openModal('modal-pay-{{ $card->id }}')" style="width:100%;justify-content:center">
                                    <i class="ti ti-check"></i>{{ __('Pagar fatura') }}
                                </button>
                            @endif
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    @endif
</div>

{{-- Modal: Novo cartão --}}
<div class="modal-overlay" id="modal-card">
    <div class="modal">
        <div class="modal-header">
            <h3>{{ __('Novo cartão de crédito') }}</h3>
            <i class="ti ti-x" style="cursor:pointer;font-size:18px;color:var(--color-text-secondary)" onclick="closeModal('modal-card')"></i>
        </div>
        <form method="POST" action="{{ route('credit-cards.store') }}">
            @csrf
            <div class="form-group">
                <label class="form-label">{{ __('Nome do cartão') }}</label>
                <input type="text" name="name" placeholder="{{ __('Ex: Visa Empresarial') }}" required>
            </div>
            <div class="form-row-3">
                <div class="form-group">
                    <label class="form-label">{{ __('4 últimos dígitos') }}</label>
                    <input type="text" name="last_four_digits" maxlength="4" pattern="[0-9]{4}" inputmode="numeric" placeholder="1234" required>
                </div>
                <div class="form-group">
                    <label class="form-label">{{ __('Limite do Cartão') }}</label>
                    <input type="number" step="0.01" name="credit_limit" min="0" placeholder="Ex: 5000.00">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">{{ __('Dia fechamento') }}</label>
                    <input type="number" name="closing_day" min="1" max="31" required>
                </div>
                <div class="form-group">
                    <label class="form-label">{{ __('Dia vencimento') }}</label>
                    <input type="number" name="due_day" min="1" max="31" required>
                </div>
            </div>
            <div style="display:flex;justify-content:flex-end;gap:8px;margin-top:16px">
                <button type="button" class="btn" onclick="closeModal('modal-card')">{{ __('Cancelar') }}</button>
                <button type="submit" class="btn btn-primary">{{ __('Salvar') }}</button>
            </div>
        </form>
    </div>
</div>

@foreach($cards as $card)
{{-- Modal: Editar cartão --}}
<div class="modal-overlay" id="modal-edit-{{ $card->id }}">
    <div class="modal">
        <div class="modal-header">
            <h3>{{ __('Editar cartão') }}</h3>
            <i class="ti ti-x" style="cursor:pointer;font-size:18px;color:var(--color-text-secondary)" onclick="closeModal('modal-edit-{{ $card->id }}')"></i>
        </div>
        <form method="POST" action="{{ route('credit-cards.update', $card) }}">
            @csrf @method('PUT')
            <input type="hidden" name="year" value="{{ $year }}">
            <input type="hidden" name="month" value="{{ $month }}">
            <div class="form-group">
                <label class="form-label">{{ __('Nome do cartão') }}</label>
                <input type="text" name="name" value="{{ $card->name }}" required>
            </div>
            <div class="form-row-3">
                <div class="form-group">
                    <label class="form-label">{{ __('4 últimos dígitos') }}</label>
                    <input type="text" name="last_four_digits" maxlength="4" pattern="[0-9]{4}" value="{{ $card->last_four_digits }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label">{{ __('Limite do Cartão') }}</label>
                    <input type="number" step="0.01" name="credit_limit" min="0" value="{{ $card->credit_limit }}">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">{{ __('Dia fechamento') }}</label>
                    <input type="number" name="closing_day" min="1" max="31" value="{{ $card->closing_day }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label">{{ __('Dia vencimento') }}</label>
                    <input type="number" name="due_day" min="1" max="31" value="{{ $card->due_day }}" required>
                </div>
            </div>
            <div style="display:flex;justify-content:flex-end;gap:8px;margin-top:16px">
                <button type="button" class="btn" onclick="closeModal('modal-edit-{{ $card->id }}')">{{ __('Cancelar') }}</button>
                <button type="submit" class="btn btn-primary">{{ __('Salvar alterações') }}</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal: Detalhes da fatura virtual --}}
@php $summary = $card->invoice_summary; @endphp
<div class="modal-overlay" id="modal-invoice-{{ $card->id }}">
    <div class="modal" style="max-width:640px">
        <div class="modal-header">
            <h3>{{ __('Detalhes da fatura') }} — {{ $card->name }}</h3>
            <i class="ti ti-x" style="cursor:pointer;font-size:18px;color:var(--color-text-secondary)" onclick="closeModal('modal-invoice-{{ $card->id }}')"></i>
        </div>
        <p style="font-size:13px;color:var(--color-text-secondary);margin-bottom:16px">
            {{ __('Período') }}: {{ $summary['period_start']->format('d/m/Y') }} {{ __('a') }} {{ $summary['period_end']->format('d/m/Y') }}
            · <strong>{{ money($summary['total']) }}</strong>
            ({{ $summary['count'] }} {{ __('lançamentos') }})
        </p>
        @if($summary['transactions']->isEmpty())
            <div style="text-align:center;padding:24px 12px;color:var(--color-text-tertiary);font-size:13px">
                <i class="ti ti-receipt-off" style="font-size:28px;display:block;margin-bottom:8px"></i>
                {{ __('Nenhum lançamento pendente neste período.') }}
            </div>
        @else
            <div class="table-wrap" style="max-height:320px;overflow-y:auto">
                <table class="android-list-table">
                    <thead>
                        <tr>
                            <th>{{ __('Vencimento') }}</th>
                            <th>{{ __('Descrição') }}</th>
                            <th>{{ __('Categoria') }}</th>
                            <th style="text-align:right">{{ __('Valor') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($summary['transactions'] as $tx)
                            <tr>
                                <td data-label="{{ __('Vencimento') }}" style="font-size:12px;white-space:nowrap">{{ $tx->due_date->format('d/m/Y') }}</td>
                                <td data-label="{{ __('Descrição') }}">{{ $tx->description }}</td>
                                <td data-label="{{ __('Categoria') }}" style="font-size:12px;color:var(--color-text-secondary)">{{ $tx->category->name ?? '—' }}</td>
                                <td data-label="{{ __('Valor') }}" style="text-align:right;font-weight:500;color:var(--color-text-danger)">{{ money($tx->amount) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3" style="font-weight:500;padding-top:12px">{{ __('Total da fatura') }}</td>
                            <td style="text-align:right;font-weight:600;color:var(--color-text-warning);padding-top:12px">{{ money($summary['total']) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        @endif
        <div style="display:flex;justify-content:flex-end;margin-top:16px">
            <button type="button" class="btn" onclick="closeModal('modal-invoice-{{ $card->id }}')">{{ __('Fechar') }}</button>
        </div>
    </div>
</div>

{{-- Modal: Pagar fatura --}}
<div class="modal-overlay" id="modal-pay-{{ $card->id }}">
    <div class="modal">
        <div class="modal-header">
            <h3>{{ __('Pagar fatura') }} — {{ $card->name }}</h3>
            <i class="ti ti-x" style="cursor:pointer;font-size:18px;color:var(--color-text-secondary)" onclick="closeModal('modal-pay-{{ $card->id }}')"></i>
        </div>
        <p style="font-size:13px;color:var(--color-text-secondary);margin-bottom:16px">
            {{ __('Valor da fatura virtual:') }}
            <strong style="color:var(--color-text-warning)">{{ money($card->open_invoice_total) }}</strong>
            ({{ $card->pending_count }} {{ __('lançamentos') }})
        </p>
        <form method="POST" action="{{ route('credit-cards.pay-invoice', $card) }}">
            @csrf
            <input type="hidden" name="month" value="{{ $month }}">
            <input type="hidden" name="year" value="{{ $year }}">
            <div class="form-group">
                <label class="form-label">{{ __('Conta para débito') }}</label>
                <select name="bank_account_id" required>
                    @foreach($bankAccounts as $account)
                        <option value="{{ $account->id }}">{{ $account->name }} ({{ money($account->current_balance) }})</option>
                    @endforeach
                </select>
            </div>
            <div style="display:flex;justify-content:flex-end;gap:8px;margin-top:16px">
                <button type="button" class="btn" onclick="closeModal('modal-pay-{{ $card->id }}')">{{ __('Cancelar') }}</button>
                <button type="submit" class="btn btn-success">{{ __('Confirmar pagamento') }}</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal: Compra parcelada no cartão (RF20 / RF21) --}}
<div class="modal-overlay" id="modal-installment-{{ $card->id }}">
    <div class="modal">
        <div class="modal-header">
            <h3>{{ __('Compra parcelada') }} — {{ $card->name }}</h3>
            <i class="ti ti-x" style="cursor:pointer;font-size:18px;color:var(--color-text-secondary)" onclick="closeModal('modal-installment-{{ $card->id }}')"></i>
        </div>
        <form method="POST" action="{{ route('credit-cards.installments.store', $card) }}">
            @csrf
            <input type="hidden" name="year" value="{{ $year }}">
            <input type="hidden" name="month" value="{{ $month }}">
            <div class="form-group">
                <label class="form-label">{{ __('Descrição') }}</label>
                <input type="text" name="description" placeholder="{{ __('Ex: Notebook Dell') }}" required>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">{{ __('Valor total') }}</label>
                    <input type="number" name="amount" step="0.01" min="0.01" required>
                </div>
                <div class="form-group">
                    <label class="form-label">{{ __('Número de parcelas') }}</label>
                    <input type="number" name="installments" min="1" max="48" value="3" required>
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">{{ __('Data da compra') }}</label>
                <input type="date" name="purchase_date" value="{{ now()->format('Y-m-d') }}" required>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">{{ __('Conta para pagar a fatura') }}</label>
                    <select name="bank_account_id" required>
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
            <div class="form-group" style="display:flex;align-items:center;gap:8px;margin-top:8px">
                <input type="checkbox" name="is_recurring" id="is_recurring_{{ $card->id }}" value="1" style="width:auto;margin:0">
                <label for="is_recurring_{{ $card->id }}" style="margin:0;font-size:14px">{{ __('Assinatura/Plano Recorrente (Cobrado mensalmente)') }}</label>
            </div>
            <div style="font-size:12px;color:var(--color-text-tertiary);margin-bottom:12px;margin-top:12px">
                <i class="ti ti-info-circle"></i>
                {{ __('As parcelas serão geradas automaticamente em lançamentos, respeitando fechamento e vencimento do cartão.') }}
            </div>
            <div style="display:flex;justify-content:flex-end;gap:8px;margin-top:16px">
                <button type="button" class="btn" onclick="closeModal('modal-installment-{{ $card->id }}')">{{ __('Cancelar') }}</button>
                <button type="submit" class="btn btn-primary">{{ __('Registrar compra') }}</button>
            </div>
        </form>
    </div>
</div>
@endforeach
@endsection
