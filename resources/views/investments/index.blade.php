@extends('layouts.app')
@section('title', 'Investimentos')

@section('content')
<div class="topbar">
    <span class="topbar-title">{{ __('Investimentos') }}</span>
        @if(auth()->user()->canManageFinances())
        <button class="btn btn-primary" onclick="openModal('modal-invest')"><i class="ti ti-plus"></i>{{ __('Novo investimento') }}</button>
    @endif
</div>

<div class="content">
    <div class="metrics-row" style="margin-bottom:16px">
        <div class="metric-card">
            <div class="metric-label">{{ __('Total investido') }}</div>
            <div class="metric-value" style="color:var(--color-text-info)">
                {{ money($investments->sum('current_amount')) }}
            </div>
        </div>
        <div class="metric-card">
            <div class="metric-label">{{ __('Rendimento acumulado') }}</div>
            <div class="metric-value" style="color:var(--color-text-success)">
                {{ money($investments->sum('current_amount') - $investments->sum('initial_amount')) }}
            </div>
        </div>
    </div>

    <div class="table-wrap">
        <table>
            <thead>
                <tr><th>{{ __('Nome') }}</th><th>{{ __('Tipo') }}</th><th>{{ __('Valor inicial') }}</th><th>{{ __('Valor atual') }}</th><th>{{ __('Taxa') }}</th><th>{{ __('Início') }}</th><th>{{ __('Vencimento') }}</th></tr>
            </thead>
            <tbody>
                @forelse($investments as $inv)
                    <tr>
                        <td style="font-weight:500">{{ $inv->name }}</td>
                        <td><span class="badge badge-info">{{ $inv->type->label() }}</span></td>
                        <td>{{ money($inv->initial_amount) }}</td>
                        <td style="color:var(--color-text-success);font-weight:500">{{ money($inv->current_amount) }}</td>
                        <td>{{ number_format($inv->interest_rate, 2, ',', '.') }}% a.a.</td>
                        <td style="font-size:12px">{{ $inv->start_date->format('d/m/Y') }}</td>
                        <td style="font-size:12px">{{ $inv->end_date ? $inv->end_date->format('d/m/Y') : '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="7" style="color:var(--color-text-tertiary);text-align:center">{{ __('Nenhum investimento cadastrado.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="modal-overlay" id="modal-invest">
    <div class="modal">
        <div class="modal-header">
            <h3>{{ __('Novo investimento') }}</h3>
            <i class="ti ti-x" style="cursor:pointer;font-size:18px;color:var(--color-text-secondary)" onclick="closeModal('modal-invest')"></i>
        </div>
        <form method="POST" action="{{ route('investments.store') }}">
            @csrf
            <div class="form-group">
                <label class="form-label">{{ __('Nome') }}</label>
                <input type="text" name="name" placeholder="{{ __('Ex: CDB Banco Inter 120%') }}" required>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">{{ __('Tipo') }}</label>
                    <select name="type" required>
                        @foreach($types as $type)
                            <option value="{{ $type->value }}">{{ $type->label() }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">{{ __('Valor inicial') }}</label>
                    <input type="number" name="initial_amount" step="0.01" min="0.01" required>
                </div>
            </div>
            <div class="form-row-3">
                <div class="form-group">
                    <label class="form-label">{{ __('Taxa (% a.a.)') }}</label>
                    <input type="number" name="interest_rate" step="0.01" min="0" required>
                </div>
                <div class="form-group">
                    <label class="form-label">{{ __('Data início') }}</label>
                    <input type="date" name="start_date" required>
                </div>
                <div class="form-group">
                    <label class="form-label">{{ __('Vencimento') }}</label>
                    <input type="date" name="end_date">
                </div>
            </div>
            <div style="display:flex;justify-content:flex-end;gap:8px;margin-top:16px">
                <button type="button" class="btn" onclick="closeModal('modal-invest')">{{ __('Cancelar') }}</button>
                <button type="submit" class="btn btn-primary">{{ __('Salvar') }}</button>
            </div>
        </form>
    </div>
</div>
@endsection
