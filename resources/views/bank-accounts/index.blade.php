@extends('layouts.app')
@section('title', 'Contas bancárias')

@section('content')
<div class="topbar">
    <span class="topbar-title">{{ __('Contas bancárias') }}</span>
    @if(auth()->user()->isAdmin())
        <button class="btn btn-primary" onclick="openModal('modal-conta')"><i class="ti ti-plus"></i>{{ __('Nova conta') }}</button>
    @endif
</div>

<div class="content">
    <div class="grid-3">
        @foreach($accounts as $account)
            <div class="card" {!! 'style="display:flex;flex-direction:column;height:100%;' . ($account->isNegative() ? 'border-color:var(--color-border-danger)' : '') . '"' !!}>
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
                        </div>
                    </div>
                    @if($account->isNegative())
                        <div class="alert alert-danger" style="padding:6px 10px;margin-bottom:8px">
                            <i class="ti ti-alert-triangle"></i>{{ __('Saldo negativo') }}
                        </div>
                    @endif
                </div>
                <div class="divider"></div>
                <div {!! $account->isNegative() ? 'style="font-size:22px;font-weight:500;color:var(--color-text-danger)"' : 'style="font-size:22px;font-weight:500;color:var(--color-text-success)"' !!}>
                    {{ money($account->current_balance) }}
                </div>
                <div style="font-size:12px;color:var(--color-text-tertiary)">{{ __('saldo atual') }}</div>
                <div style="margin-top:12px;display:flex;gap:8px">
                    <a href="{{ route('transactions.index', ['bank_account_id' => $account->id]) }}" class="btn" style="font-size:12px;flex:1;justify-content:center">
                        <i class="ti ti-list"></i>{{ __('Extrato') }}
                    </a>
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
            <div style="display:flex;justify-content:flex-end;gap:8px;margin-top:16px">
                <button type="button" class="btn" onclick="closeModal('modal-conta')">{{ __('Cancelar') }}</button>
                <button type="submit" class="btn btn-primary">{{ __('Salvar') }}</button>
            </div>
        </form>
    </div>
</div>
@endsection
