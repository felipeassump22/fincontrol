@extends('layouts.app')
@section('title', __('Pagamentos por cliente'))

@section('content')
<div class="topbar">
    <span class="topbar-title">{{ __('Pagamentos por cliente') }}</span>
    <form method="GET" action="{{ route('reports.client-payments') }}" style="display:flex;gap:8px;align-items:center">
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
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>{{ __('Cliente') }}</th>
                    <th>{{ __('Pago') }}</th>
                    <th>{{ __('Pendente') }}</th>
                    <th>{{ __('Cancelado') }}</th>
                    <th>{{ __('Total') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($paymentsByClient as $row)
                    <tr>
                        <td style="font-weight:500">{{ $row['client_name'] }}</td>
                        <td style="color:var(--color-text-success)">
                            {{ money($row['paid']) }}
                            <span style="font-size:11px;color:var(--color-text-tertiary)">({{ $row['paid_count'] }})</span>
                        </td>
                        <td style="color:var(--color-text-warning)">
                            {{ money($row['pending']) }}
                            <span style="font-size:11px;color:var(--color-text-tertiary)">({{ $row['pending_count'] }})</span>
                        </td>
                        <td style="color:var(--color-text-danger)">
                            {{ money($row['canceled']) }}
                            <span style="font-size:11px;color:var(--color-text-tertiary)">({{ $row['canceled_count'] }})</span>
                        </td>
                        <td style="font-weight:600">{{ money($row['total']) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" style="text-align:center;color:var(--color-text-tertiary)">{{ __('Nenhum pagamento por cliente no período.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
