@extends('layouts.app')
@section('title', __('Relatório contábil'))

@section('content')
<div class="topbar">
    <span class="topbar-title">{{ __('Relatório contábil') }}</span>
    <form method="GET" action="{{ route('reports.accounting') }}" style="display:flex;gap:8px;align-items:center;flex-wrap:wrap">
        <select name="type" style="width:auto;font-size:12px;padding:5px 8px" onchange="this.form.submit()">
            <option value="BOTH" {{ $type === 'BOTH' ? 'selected' : '' }}>{{ __('Ambos') }}</option>
            <option value="INCOME" {{ $type === 'INCOME' ? 'selected' : '' }}>{{ __('Entradas') }}</option>
            <option value="EXPENSE" {{ $type === 'EXPENSE' ? 'selected' : '' }}>{{ __('Saídas') }}</option>
        </select>
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
</div>

<div class="content">
    <div class="metrics-row" style="margin-bottom:20px">
        <div class="metric-card">
            <div class="metric-label">{{ __('Receitas') }}</div>
            <div class="metric-value" style="color:#4ade80">{{ money($accounting['total_income']) }}</div>
        </div>
        <div class="metric-card">
            <div class="metric-label">{{ __('Despesas') }}</div>
            <div class="metric-value" style="color:#f87171">{{ money($accounting['total_expense']) }}</div>
        </div>
        <div class="metric-card">
            <div class="metric-label">{{ __('Resultado') }}</div>
            <div class="metric-value" style="color:{{ $accounting['net_result'] >= 0 ? '#4ade80' : '#f87171' }}">{{ money($accounting['net_result']) }}</div>
        </div>
    </div>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>{{ __('Data') }}</th>
                    <th>{{ __('Tipo') }}</th>
                    <th>{{ __('Descrição') }}</th>
                    <th>{{ __('Categoria') }}</th>
                    <th>{{ __('Cliente') }}</th>
                    <th>{{ __('Status') }}</th>
                    <th>{{ __('Valor') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($accounting['transactions'] as $tx)
                    <tr>
                        <td>{{ $tx->due_date->format('d/m/Y') }}</td>
                        <td><span class="badge {{ $tx->isIncome() ? 'badge-success' : 'badge-danger' }}">{{ $tx->transaction_type->label() }}</span></td>
                        <td>{{ $tx->description }}</td>
                        <td>{{ $tx->category?->name ?? '—' }}</td>
                        <td>{{ $tx->client?->name ?? '—' }}</td>
                        <td><span class="badge {{ $tx->status->badgeClass() }}">{{ $tx->status->label() }}</span></td>
                        <td style="font-weight:600;color:{{ $tx->isIncome() ? 'var(--color-text-success)' : 'var(--color-text-danger)' }}">{{ money($tx->amount) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="7" style="text-align:center;color:var(--color-text-tertiary)">{{ __('Nenhum lançamento no período.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
