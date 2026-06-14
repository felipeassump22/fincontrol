@extends('layouts.app')
@section('title', 'Dashboard')

@push('styles')
<style>
    /* Premium Dashboard Styles */
    .dashboard-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 20px;
        margin-bottom: 24px;
    }

    .metric-card-premium {
        background: rgba(255, 255, 255, 0.03);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border: 1px solid rgba(255, 255, 255, 0.05);
        border-radius: 20px;
        padding: 24px;
        position: relative;
        overflow: hidden;
        box-shadow: 0 10px 30px -10px rgba(0, 0, 0, 0.2);
        transition: transform 0.4s cubic-bezier(0.34, 1.56, 0.64, 1), box-shadow 0.4s ease;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }

    .metric-card-premium:hover {
        transform: translateY(-5px);
        box-shadow: 0 20px 40px -15px rgba(0, 0, 0, 0.4);
        border-color: rgba(255, 255, 255, 0.1);
    }

    .metric-card-premium::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3px;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
        opacity: 0;
        transition: opacity 0.4s;
    }

    .metric-card-premium:hover::before {
        opacity: 1;
    }

    .metric-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 16px;
    }

    .metric-title {
        font-size: 13px;
        color: var(--color-text-secondary);
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .metric-icon-wrap {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
    }

    .icon-balance { background: rgba(59, 130, 246, 0.1); color: #3b82f6; }
    .icon-income { background: rgba(34, 197, 94, 0.1); color: #22c55e; }
    .icon-expense { background: rgba(239, 68, 68, 0.1); color: #ef4444; }
    .icon-card { background: rgba(168, 85, 247, 0.1); color: #a855f7; }

    .metric-value-large {
        font-size: 28px;
        font-weight: 700;
        letter-spacing: -0.5px;
        margin-bottom: 4px;
        display: flex;
        align-items: baseline;
        gap: 4px;
    }

    .metric-currency {
        font-size: 16px;
        font-weight: 500;
        opacity: 0.7;
    }

    .metric-footer {
        font-size: 12px;
        color: var(--color-text-tertiary);
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .pill-badge {
        padding: 4px 10px;
        border-radius: 12px;
        font-size: 11px;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }

    .pill-positive { background: rgba(34, 197, 94, 0.15); color: #4ade80; border: 1px solid rgba(34, 197, 94, 0.2); }
    .pill-negative { background: rgba(239, 68, 68, 0.15); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.2); }

    /* Topbar Premium Selects */
    .premium-select-group {
        display: flex;
        gap: 8px;
        background: rgba(255, 255, 255, 0.03);
        padding: 4px;
        border-radius: 12px;
        border: 1px solid rgba(255, 255, 255, 0.05);
    }

    .premium-select {
        background: transparent;
        border: none;
        color: var(--color-text-primary);
        font-size: 13px;
        font-weight: 500;
        padding: 6px 24px 6px 12px;
        border-radius: 8px;
        cursor: pointer;
        appearance: none;
        -webkit-appearance: none;
        background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23888780' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
        background-repeat: no-repeat;
        background-position: right 8px center;
        background-size: 14px;
        transition: all 0.2s ease;
    }

    .premium-select:hover {
        background-color: rgba(255, 255, 255, 0.05);
    }

    .premium-select:focus {
        outline: none;
        box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.3);
    }

    .premium-select option {
        background: var(--color-background-secondary);
        color: var(--color-text-primary);
    }

    /* Alert Banner Premium */
    .alert-premium-danger {
        background: linear-gradient(90deg, rgba(239, 68, 68, 0.1) 0%, rgba(239, 68, 68, 0.02) 100%);
        border-left: 4px solid #ef4444;
        border-radius: 12px;
        padding: 16px 20px;
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 24px;
        color: #fca5a5;
        font-size: 14px;
        box-shadow: 0 4px 15px -5px rgba(239, 68, 68, 0.1);
        backdrop-filter: blur(8px);
        animation: slideInDown 0.5s cubic-bezier(0.34, 1.56, 0.64, 1);
    }

    @keyframes slideInDown {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .alert-premium-danger i {
        font-size: 20px;
        color: #ef4444;
        animation: gentlePulse 2s infinite ease-in-out;
    }

    @keyframes gentlePulse {
        0%, 100% { transform: scale(1); opacity: 1; }
        50% { transform: scale(1.1); opacity: 0.8; }
    }
</style>
@endpush

@section('content')
<div class="topbar">
    <span class="topbar-title">Dashboard</span>
    <div class="topbar-actions">
        <form method="GET" action="{{ route('dashboard') }}" class="premium-select-group">
            <select name="month" class="premium-select" onchange="this.form.submit()">
                @for($m = 1; $m <= 12; $m++)
                    <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                        {{ \Carbon\Carbon::create(null, $m)->translatedFormat('F') }}
                    </option>
                @endfor
            </select>
            <select name="year" class="premium-select" onchange="this.form.submit()">
                @for($y = now()->year - 2; $y <= now()->year + 1; $y++)
                    <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                @endfor
            </select>
        </form>
    </div>
</div>

<div class="content">
    {{-- Alerta de saldo negativo --}}
    @foreach($negativeAccounts as $account)
        <div class="alert-premium-danger">
            <i class="ti ti-alert-circle"></i>
            <div>
                {{ __('Atenção: A conta') }} <strong style="color:#fff">{{ $account->name }}</strong> {{ __('encontra-se negativa com saldo de') }} <strong style="color:#ef4444">{{ money($account->current_balance) }}</strong>
            </div>
        </div>
    @endforeach

    {{-- Métricas do mês Premium --}}
    <div class="dashboard-grid">
        <div class="metric-card-premium">
            <div class="metric-header">
                <span class="metric-title">{{ __('Saldo Consolidado') }}</span>
                <div class="metric-icon-wrap icon-balance"><i class="ti ti-wallet"></i></div>
            </div>
            <div class="metric-value-large" {!! 'style="color:' . ($consolidatedBalance >= 0 ? '#4ade80' : '#f87171') . '"' !!}>
                {{ money($consolidatedBalance) }}
            </div>
            <div class="metric-footer">
                <i class="ti ti-building-bank"></i> {{ __('Soma de todas as contas') }}
            </div>
        </div>

        <div class="metric-card-premium">
            <div class="metric-header">
                <span class="metric-title">{{ __('Receitas no mês') }}</span>
                <div class="metric-icon-wrap icon-income"><i class="ti ti-trending-up"></i></div>
            </div>
            <div class="metric-value-large" style="color:#fff">
                {{ money($totals['total_income'] ?? 0) }}
            </div>
            <div class="metric-footer">
                @if(isset($variations['income']))
                    <span class="pill-badge {{ $variations['income'] >= 0 ? 'pill-positive' : 'pill-negative' }}">
                        <i class="ti {{ $variations['income'] >= 0 ? 'ti-arrow-up-right' : 'ti-arrow-down-right' }}"></i>
                        {{ number_format(abs($variations['income']), 1, ',', '.') }}%
                    </span>
                    {{ __('vs. mês anterior') }}
                @else
                    {{ __('Sem histórico anterior') }}
                @endif
            </div>
        </div>

        <div class="metric-card-premium">
            <div class="metric-header">
                <span class="metric-title">{{ __('Despesas no mês') }}</span>
                <div class="metric-icon-wrap icon-expense"><i class="ti ti-trending-down"></i></div>
            </div>
            <div class="metric-value-large" style="color:#fff">
                {{ money($totals['total_expense'] ?? 0) }}
            </div>
            <div class="metric-footer">
                @if(isset($variations['expense']))
                    {{-- Para despesa, queda é bom (positivo), aumento é ruim (negativo) --}}
                    <span class="pill-badge {{ $variations['expense'] <= 0 ? 'pill-positive' : 'pill-negative' }}">
                        <i class="ti {{ $variations['expense'] <= 0 ? 'ti-arrow-down-right' : 'ti-arrow-up-right' }}"></i>
                        {{ number_format(abs($variations['expense']), 1, ',', '.') }}%
                    </span>
                    {{ __('vs. mês anterior') }}
                @else
                    {{ __('Sem histórico anterior') }}
                @endif
            </div>
        </div>

        <div class="metric-card-premium">
            <div class="metric-header">
                <span class="metric-title">{{ __('Contas Bancárias') }}</span>
                <div class="metric-icon-wrap icon-card"><i class="ti ti-credit-card"></i></div>
            </div>
            <div class="metric-value-large" style="color:#fff">
                {{ count($accounts) }}
            </div>
            <div class="metric-footer">
                <i class="ti ti-calendar-event"></i> {{ __('Contas cadastradas ativas') }}
            </div>
        </div>
    </div>

    {{-- Restante do Dashboard - Gráficos e Tabelas --}}
    <div class="dashboard-grid" style="grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); margin-top: 24px;">
        {{-- Receitas por categoria --}}
        <div class="card" style="border-radius: 20px; border-color: rgba(255,255,255,0.05); background: rgba(255,255,255,0.02); display: flex; flex-direction: column;">
            <div class="section-title">{{ __('Receitas por categoria') }}</div>
            @foreach($incomeByCategory as $cat)
                <div class="bar-row">
                    <div class="bar-label">
                        <span style="font-weight: 500; color: var(--color-text-secondary)">{{ $cat['category_name'] }}</span>
                        <span style="font-weight: 600">{{ money($cat['total']) }}</span>
                    </div>
                    <div class="progress-bg" style="background: rgba(255,255,255,0.05)">
                        <div class="progress-fill animate-bar" {!! 'style="width:' . $cat['percentage'] . '%;background:linear-gradient(90deg, #4ade80, #22c55e);box-shadow: 0 0 10px rgba(34, 197, 94, 0.4)"' !!}></div>
                    </div>
                </div>
            @endforeach
            @if(empty($incomeByCategory))
                <div style="font-size:13px;color:var(--color-text-tertiary);text-align:center;padding:20px 0;flex:1;display:flex;align-items:center;justify-content:center;">{{ __('Nenhuma receita no período.') }}</div>
            @endif
        </div>

        {{-- Receitas por cliente --}}
        <div class="card" style="border-radius: 20px; border-color: rgba(255,255,255,0.05); background: rgba(255,255,255,0.02); display: flex; flex-direction: column;">
        <div class="section-title" style="display:flex;justify-content:space-between;align-items:center;padding-bottom:16px">
            {{ __('Receitas por cliente') }}
            <a href="{{ route('reports.export-pdf', ['year' => $year, 'month' => $month]) }}" class="btn" style="font-size:12px; border-radius:10px; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1)" data-turbo="false" target="_blank">
                <i class="ti ti-download"></i>{{ __('Exportar PDF') }}
            </a>
        </div>
        <div class="table-wrap" style="border:none;border-radius:0;background:transparent">
            <table style="width:100%; border-collapse: separate; border-spacing: 0 8px;">
                <thead>
                    <tr>
                        <th style="color:var(--color-text-tertiary);font-weight:500">{{ __('Cliente') }}</th>
                        <th style="color:var(--color-text-tertiary);font-weight:500">{{ __('Receita') }}</th>
                        <th style="color:var(--color-text-tertiary);font-weight:500">{{ __('Lançamentos') }}</th>
                        <th style="color:var(--color-text-tertiary);font-weight:500">{{ __('Participação') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($incomeByClient as $client)
                        <tr style="background: rgba(255,255,255,0.02); transition: transform 0.2s;">
                            <td style="padding:12px 16px; border-radius: 8px 0 0 8px;">{{ $client['client_name'] }}</td>
                            <td style="padding:12px 16px; color:#4ade80; font-weight:600">{{ money($client['total']) }}</td>
                            <td style="padding:12px 16px;">
                                <span style="background:rgba(255,255,255,0.05);padding:4px 10px;border-radius:6px;font-size:12px">{{ $client['count'] }}</span>
                            </td>
                            <td style="padding:12px 16px; border-radius: 0 8px 8px 0;">
                                <div style="display:flex;align-items:center;gap:8px">
                                    <span style="font-size:13px;font-weight:600;width:35px">{{ $client['percentage'] }}%</span>
                                    <div style="flex:1;height:4px;background:rgba(255,255,255,0.1);border-radius:2px;overflow:hidden">
                                        <div {!! 'style="width:' . $client['percentage'] . '%;height:100%;background:#3b82f6"' !!}></div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" style="color:var(--color-text-tertiary);text-align:center;padding:24px">{{ __('Nenhuma receita por cliente no período.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
    </div>
</div>
@endsection
