@extends('layouts.app')
@section('title', 'Relatório mensal')

@section('content')
<div class="topbar">
    <span class="topbar-title">{{ __('Relatório mensal') }}</span>
    <div class="topbar-actions">
        <form method="GET" action="{{ route('reports.index') }}" style="display:flex;gap:8px;align-items:center">
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
        @if(auth()->user()->can('export', \App\Models\MonthlyReport::class))
            <a href="{{ route('reports.export-pdf', ['year' => $year, 'month' => $month]) }}" class="btn btn-primary" data-turbo="false" target="_blank">
                <i class="ti ti-download"></i>{{ __('Exportar PDF') }}
            </a>
        @endif
    </div>
</div>

<div class="content">
    @if($report->is_closed)
        <div class="alert alert-info">
            <i class="ti ti-lock"></i>
            {{ __('Relatório fechado em') }} {{ $report->closed_at->format('d/m/Y H:i') }}
            — {{ __('dados imutáveis.') }}
        </div>
    @endif

    <div class="metrics-row" style="margin-bottom:20px">
        <div class="metric-card">
            <div class="metric-label">{{ __('Receitas') }}</div>
            <div class="metric-value" @style(['color' => '#4ade80'])>{{ money($report->total_income) }}</div>
        </div>
        <div class="metric-card">
            <div class="metric-label">{{ __('Despesas') }}</div>
            <div class="metric-value" @style(['color' => '#f87171'])>{{ money($report->total_expense) }}</div>
        </div>
        <div class="metric-card">
            <div class="metric-label">{{ __('Resultado líquido') }}</div>
            <div class="metric-value" @style(['color' => $report->net_result >= 0 ? '#4ade80' : '#f87171'])>
                {{ money($report->net_result) }}
            </div>
        </div>
    </div>

    <div class="grid-2">
        <div class="card">
            <div class="section-title">{{ __('Receitas por categoria') }}</div>
            @foreach($incomeByCategory as $cat)
                <div class="bar-row">
                    <div class="bar-label"><span>{{ $cat['category_name'] }}</span><span style="color:#4ade80">{{ money($cat['total']) }}</span></div>
                    <div class="progress-bg"><div class="progress-fill" @style(['width' => $cat['percentage'] . '%', 'background-color' => '#4ade80'])></div></div>
                </div>
            @endforeach
        </div>
        <div class="card">
            <div class="section-title">{{ __('Despesas por categoria') }}</div>
            @foreach($expensesByCategory as $cat)
                <div class="bar-row">
                    <div class="bar-label"><span>{{ $cat['category_name'] }}</span><span style="color:#f87171">{{ money($cat['total']) }}</span></div>
                    <div class="progress-bg"><div class="progress-fill" @style(['width' => $cat['percentage'] . '%', 'background-color' => '#f87171'])></div></div>
                </div>
            @endforeach
        </div>
    </div>

    @if(!$report->is_closed && auth()->user()->can('close', $report))
        <div style="margin-top:16px;text-align:right">
            <form method="POST" action="{{ route('reports.close', ['year' => $year, 'month' => $month]) }}"
                  onsubmit="return confirm('Fechar este relatório? Ele se tornará imutável.')">
                @csrf
                <button type="submit" class="btn btn-danger">
                    <i class="ti ti-lock"></i>{{ __('Fechar relatório') }}
                </button>
            </form>
        </div>
    @endif
</div>
@endsection
