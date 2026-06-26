@extends('layouts.app')
@section('title', 'Contas bancárias')

@section('content')
<div class="topbar">
    <span class="topbar-title">{{ __('Contas bancárias') }}</span>
    @if(auth()->user()->canManageFinances())
        <button class="btn btn-primary" onclick="openModal('modal-conta')"><i class="ti ti-plus"></i>{{ __('Nova conta') }}</button>
    @endif
</div>

<div class="content">
    <div class="grid-3">
        @foreach($accounts as $account)
            <div class="card" {!! 'style="display:flex;flex-direction:column;height:100%;opacity:' . ($account->is_active ? '1' : '0.65') . ';' . ($account->isNegative() ? 'border-color:var(--color-border-danger)' : '') . '"' !!}>
                <div style="flex:1">
                    <div style="display:flex;align-items:center;gap:10px;margin-bottom:12px">
                        @php
                            $n = $account->name;
                            $domain = null;

                            if (stripos($n, 'itaú') !== false || stripos($n, 'itau') !== false) {
                                $domain = 'itau.com.br';
                            } elseif (stripos($n, 'nubank') !== false || stripos($n, 'nu ') !== false) {
                                $domain = 'nubank.com.br';
                            } elseif (stripos($n, 'bradesco') !== false) {
                                $domain = 'banco.bradesco';
                            } elseif (stripos($n, 'caixa') !== false || stripos($n, 'cef') !== false) {
                                $domain = 'caixa.gov.br';
                            } elseif (stripos($n, 'banco do brasil') !== false || stripos($n, 'bb') !== false) {
                                $domain = 'bb.com.br';
                            } elseif (stripos($n, 'santander') !== false) {
                                $domain = 'santander.com.br';
                            } elseif (stripos($n, 'inter') !== false) {
                                $domain = 'bancointer.com.br';
                            } elseif (stripos($n, 'safra') !== false) {
                                $domain = 'safra.com.br';
                            } elseif (stripos($n, 'sicoob') !== false) {
                                $domain = 'sicoob.com.br';
                            } elseif (stripos($n, 'c6') !== false) {
                                $domain = 'c6bank.com.br';
                            }
                        @endphp

                        @if($domain)
                            <div style="width:38px;height:38px;border-radius:var(--border-radius-md);background:#ffffff;display:flex;align-items:center;justify-content:center;overflow:hidden;border:0.5px solid var(--color-border-tertiary);box-shadow:0 2px 4px rgba(0,0,0,0.05)">
                                <img src="https://www.google.com/s2/favicons?domain={{ $domain }}&sz=128" style="width:24px;height:24px;object-fit:contain;border-radius:4px" alt="Logo">
                            </div>
                        @else
                            <div {!! $account->isNegative() ? 'style="width:38px;height:38px;border-radius:var(--border-radius-md);background:var(--color-background-danger);display:flex;align-items:center;justify-content:center"' : 'style="width:38px;height:38px;border-radius:var(--border-radius-md);background:var(--color-background-info);display:flex;align-items:center;justify-content:center"' !!}>
                                <i class="ti ti-building-bank" {!! $account->isNegative() ? 'style="color:var(--color-text-danger);font-size:18px"' : 'style="color:var(--color-text-info);font-size:18px"' !!}></i>
                            </div>
                        @endif
                        <div>
                            <div style="font-size:14px;font-weight:500">{{ $account->name }}</div>
                            @unless($account->is_active)
                                <span class="badge badge-warning" style="margin-top:4px">{{ __('Inativa') }}</span>
                            @endunless
                        </div>
                    </div>
                    @if($account->isNegative())
                        <div class="alert alert-danger" style="padding:6px 10px;margin-bottom:8px">
                            <i class="ti ti-alert-triangle"></i>{{ __('Saldo negativo') }}
                        </div>
                    @endif
                    @if($account->pix_key || $account->agency)
                        <div style="font-size:12px;color:var(--color-text-secondary);margin-bottom:12px;background:var(--color-background-secondary);padding:8px;border-radius:6px">
                            @if($account->agency)
                                <div><strong>{{ __('Agência:') }}</strong> {{ $account->agency }}</div>
                            @endif
                            @if($account->account_number)
                                <div><strong>{{ __('Conta:') }}</strong> {{ $account->account_number }}</div>
                            @endif
                            @if($account->pix_key)
                                <div><strong>{{ __('Pix:') }}</strong> {{ $account->pix_key }}</div>
                            @endif
                            @if($account->document)
                                <div><strong>{{ __('CPF/CNPJ:') }}</strong> {{ $account->document }}</div>
                            @endif
                        </div>
                    @endif
                </div>
                <div class="divider"></div>
                <div {!! $account->isNegative() ? 'style="font-size:22px;font-weight:500;color:var(--color-text-danger)"' : 'style="font-size:22px;font-weight:500;color:var(--color-text-success)"' !!}>
                    {{ money($account->current_balance) }}
                </div>
                <div style="font-size:12px;color:var(--color-text-tertiary)">{{ __('saldo atual') }}</div>
                <div style="margin-top:12px;display:flex;gap:8px;flex-wrap:wrap">
                    @if($account->is_active)
                        <a href="{{ route('transactions.index', ['bank_account_id' => $account->id]) }}" class="btn" style="font-size:12px;flex:1;justify-content:center">
                            <i class="ti ti-list"></i>{{ __('Extrato') }}
                        </a>
                    @endif
                    @if(auth()->user()->canManageFinances())
                        @if($account->is_active)
                            <button class="btn btn-secondary" onclick="openModal('modal-edit-{{ $account->id }}')" style="font-size:12px;padding:8px" title="{{ __('Editar') }}">
                                <i class="ti ti-edit"></i>
                            </button>
                            <form method="POST" action="{{ route('bank-accounts.deactivate', $account) }}" style="display:inline" onsubmit="return confirm('{{ __('Desativar esta conta? Os lançamentos serão preservados.') }}')">
                                @csrf
                                <button type="submit" class="btn" style="font-size:12px;padding:8px;color:var(--color-text-danger)" title="{{ __('Desativar') }}">
                                    <i class="ti ti-ban"></i>
                                </button>
                            </form>
                        @else
                            <form method="POST" action="{{ route('bank-accounts.activate', $account) }}" style="display:inline;flex:1">
                                @csrf
                                <button type="submit" class="btn btn-primary" style="font-size:12px;width:100%;justify-content:center">
                                    <i class="ti ti-check"></i>{{ __('Reativar conta') }}
                                </button>
                            </form>
                        @endif
                    @endif
                </div>
            </div>
        @endforeach
    </div>
</div>

{{-- Modal: Nova conta --}}
<div class="modal-overlay" id="modal-conta">
    <div class="modal">
        <div class="modal-header">
            <h3>{{ __('Nova conta bancária') }}</h3>
            <i class="ti ti-x" style="cursor:pointer;font-size:18px;color:var(--color-text-secondary)" onclick="closeModal('modal-conta')"></i>
        </div>
        <form method="POST" action="{{ route('bank-accounts.store') }}">
            @csrf
            <div class="form-group">
                <label class="form-label">{{ __('Nome da conta') }}</label>
                <input type="text" name="name" placeholder="{{ __('Ex: Safra Empresa') }}" required>
            </div>
            <div class="form-group">
                <label class="form-label">{{ __('Saldo inicial') }}</label>
                <input type="number" name="initial_balance" step="0.01" placeholder="0,00" required>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">{{ __('Agência') }}</label>
                    <input type="text" name="agency" placeholder="{{ __('Ex: 0001') }}">
                </div>
                <div class="form-group">
                    <label class="form-label">{{ __('Número da Conta') }}</label>
                    <input type="text" name="account_number" placeholder="{{ __('Ex: 12345-6') }}">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">{{ __('Chave Pix') }}</label>
                    <input type="text" name="pix_key" placeholder="{{ __('Telefone, CPF, E-mail ou Aleatória') }}">
                </div>
                <div class="form-group">
                    <label class="form-label">{{ __('CPF/CNPJ (Atrelado à conta)') }}</label>
                    <input type="text" name="document" placeholder="{{ __('Somente números') }}">
                </div>
            </div>
            <div style="display:flex;justify-content:flex-end;gap:8px;margin-top:16px">
                <button type="button" class="btn" onclick="closeModal('modal-conta')">{{ __('Cancelar') }}</button>
                <button type="submit" class="btn btn-primary">{{ __('Salvar') }}</button>
            </div>
        </form>
    </div>
</div>

@foreach($accounts as $account)
<div class="modal-overlay" id="modal-edit-{{ $account->id }}">
    <div class="modal">
        <div class="modal-header">
            <h3>{{ __('Editar conta bancária') }}</h3>
            <i class="ti ti-x" style="cursor:pointer;font-size:18px;color:var(--color-text-secondary)" onclick="closeModal('modal-edit-{{ $account->id }}')"></i>
        </div>
        <form method="POST" action="{{ route('bank-accounts.update', $account) }}">
            @csrf @method('PUT')
            <div class="form-group">
                <label class="form-label">{{ __('Nome da conta') }}</label>
                <input type="text" name="name" value="{{ $account->name }}" required>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">{{ __('Agência') }}</label>
                    <input type="text" name="agency" value="{{ $account->agency }}">
                </div>
                <div class="form-group">
                    <label class="form-label">{{ __('Número da Conta') }}</label>
                    <input type="text" name="account_number" value="{{ $account->account_number }}">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">{{ __('Chave Pix') }}</label>
                    <input type="text" name="pix_key" value="{{ $account->pix_key }}">
                </div>
                <div class="form-group">
                    <label class="form-label">{{ __('CPF/CNPJ') }}</label>
                    <input type="text" name="document" value="{{ $account->document }}">
                </div>
            </div>
            <div style="display:flex;justify-content:flex-end;gap:8px;margin-top:16px">
                <button type="button" class="btn" onclick="closeModal('modal-edit-{{ $account->id }}')">{{ __('Cancelar') }}</button>
                <button type="submit" class="btn btn-primary">{{ __('Salvar alterações') }}</button>
            </div>
        </form>
    </div>
</div>
@endforeach

@endsection
