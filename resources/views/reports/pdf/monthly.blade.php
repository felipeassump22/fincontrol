<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <title>{{ __('Relatório Mensal') }} — FinControl</title>
    <style>
        /* ─── Page Setup ───────────────────────────────── */
        @page {
            margin: 30px 35px 60px 35px;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 10px;
            color: #1e293b;
            line-height: 1.5;
            background: #ffffff;
        }

        /* ─── Header Premium ───────────────────────────── */
        .header {
            background: linear-gradient(135deg, #0f172a 0%, #1e3a5f 100%);
            color: #ffffff;
            padding: 24px 28px;
            border-radius: 8px;
            margin-bottom: 24px;
            position: relative;
            overflow: hidden;
        }

        .header::after {
            content: '';
            position: absolute;
            top: 0; right: 0;
            width: 150px; height: 100%;
            background: linear-gradient(135deg, transparent 50%, rgba(59,130,246,0.15) 100%);
        }

        .header-top {
            width: 100%;
            border-collapse: collapse;
        }

        .header h1 {
            font-size: 22px;
            font-weight: 800;
            letter-spacing: -0.5px;
            margin-bottom: 2px;
            color: #ffffff;
        }

        .header .subtitle {
            font-size: 11px;
            color: #93c5fd;
            font-weight: 400;
        }

        .header-badge {
            background: rgba(59, 130, 246, 0.3);
            border: 1px solid rgba(147, 197, 253, 0.3);
            padding: 6px 14px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 700;
            color: #93c5fd;
            text-transform: uppercase;
            letter-spacing: 1px;
            display: inline-block;
            margin-bottom: 4px;
        }

        .header-meta {
            font-size: 9px;
            color: #93c5fd;
            opacity: 0.8;
        }

        /* ─── KPI Cards ────────────────────────────────── */
        .kpi-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 8px 0;
            margin: 0 -8px 24px -8px;
        }

        .kpi-table td {
            width: 25%;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 16px 14px;
            text-align: center;
            vertical-align: top;
        }

        .kpi-label {
            font-size: 8px;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            font-weight: 700;
            color: #64748b;
            margin-bottom: 6px;
        }

        .kpi-value {
            font-size: 20px;
            font-weight: 800;
            letter-spacing: -0.5px;
        }

        .kpi-sub {
            font-size: 8px;
            color: #94a3b8;
            margin-top: 4px;
        }

        /* ─── Colors ───────────────────────────────────── */
        .c-green { color: #16a34a; }
        .c-red   { color: #dc2626; }
        .c-blue  { color: #2563eb; }
        .c-amber { color: #d97706; }
        .c-muted { color: #94a3b8; }
        .bg-green-light { background: #f0fdf4 !important; border-color: #bbf7d0 !important; }
        .bg-red-light   { background: #fef2f2 !important; border-color: #fecaca !important; }
        .bg-blue-light  { background: #eff6ff !important; border-color: #bfdbfe !important; }
        .bg-amber-light { background: #fffbeb !important; border-color: #fde68a !important; }

        /* ─── Sections ─────────────────────────────────── */
        .section {
            margin-bottom: 20px;
        }

        .section-title {
            font-size: 12px;
            font-weight: 700;
            color: #0f172a;
            text-transform: uppercase;
            letter-spacing: 1px;
            padding-bottom: 6px;
            margin-bottom: 12px;
            border-bottom: 2px solid #e2e8f0;
            display: flex;
            align-items: center;
        }

        .section-title .dot {
            display: inline-block;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            margin-right: 8px;
        }

        .dot-green { background: #16a34a; }
        .dot-red   { background: #dc2626; }
        .dot-blue  { background: #2563eb; }
        .dot-amber { background: #d97706; }

        /* ─── Executive Summary ────────────────────────── */
        .exec-summary {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-left: 4px solid #2563eb;
            border-radius: 0 8px 8px 0;
            padding: 16px 20px;
            margin-bottom: 24px;
            font-size: 11px;
            color: #334155;
            line-height: 1.7;
        }

        /* ─── Data Tables ──────────────────────────────── */
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9px;
            margin-bottom: 8px;
        }

        table.data-table th {
            background: #f1f5f9;
            padding: 8px 10px;
            text-align: left;
            font-weight: 700;
            font-size: 8px;
            color: #475569;
            border-bottom: 2px solid #cbd5e1;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        table.data-table td {
            padding: 7px 10px;
            border-bottom: 1px solid #e2e8f0;
            color: #334155;
        }

        table.data-table tr:nth-child(even) td {
            background: #fafbfc;
        }

        table.data-table tfoot td {
            background: #f1f5f9 !important;
            font-weight: 700;
            border-top: 2px solid #cbd5e1;
            border-bottom: none;
        }

        /* ─── Progress Bars ────────────────────────────── */
        .progress-bar-container {
            width: 100%;
            background: #e2e8f0;
            border-radius: 4px;
            height: 8px;
            overflow: hidden;
        }

        .progress-bar {
            height: 100%;
            border-radius: 4px;
            min-width: 2px;
        }

        .progress-green { background: #16a34a; }
        .progress-red   { background: #dc2626; }
        .progress-blue  { background: #2563eb; }

        /* ─── Status Badges ────────────────────────────── */
        .badge {
            display: inline-block;
            font-size: 8px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 2px 8px;
            border-radius: 4px;
        }

        .badge-paid {
            background: #dcfce7;
            color: #166534;
        }

        .badge-pending {
            background: #fef3c7;
            color: #92400e;
        }

        .badge-income {
            background: #dcfce7;
            color: #166534;
        }

        .badge-expense {
            background: #fee2e2;
            color: #991b1b;
        }

        /* ─── Layout ───────────────────────────────────── */
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .font-bold { font-weight: 700; }
        .font-mono { font-family: 'Courier New', monospace; }

        table.layout-table { width: 100%; border-collapse: collapse; }
        table.layout-table td { vertical-align: top; }
        .half-td { width: 48%; }
        .spacer-td { width: 4%; }

        .page-break { page-break-before: always; }

        .empty-state {
            text-align: center;
            padding: 20px;
            background: #f8fafc;
            color: #94a3b8;
            font-style: italic;
            border: 1px dashed #e2e8f0;
            border-radius: 8px;
        }

        /* ─── Footer ───────────────────────────────────── */
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            padding: 10px 35px;
            font-size: 8px;
            color: #94a3b8;
            border-top: 1px solid #e2e8f0;
        }

        .footer-table {
            width: 100%;
            border-collapse: collapse;
        }

        /* ─── Alert Box ────────────────────────────────── */
        .alert-box {
            background: #fffbeb;
            border: 1px solid #fde68a;
            border-left: 4px solid #d97706;
            border-radius: 0 8px 8px 0;
            padding: 14px 18px;
            margin-bottom: 20px;
            font-size: 10px;
            color: #92400e;
        }
    </style>
</head>
<body>

    {{-- ════════════════════════════════════════════════ --}}
    {{-- PÁGINA 1 — RESUMO EXECUTIVO                    --}}
    {{-- ════════════════════════════════════════════════ --}}

    {{-- Header Premium --}}
    <div class="header">
        <table class="header-top">
            <tr>
                <td style="width: 60%; vertical-align: middle;">
                    <h1>{{ $company?->displayName() ?? 'FinControl' }}</h1>
                    <p class="subtitle">{{ $company?->company_name && $company?->trade_name ? $company->company_name : __('Relatório de Gestão Financeira') }}</p>
                    @if($company?->document)
                        <p style="font-size:11px;color:#64748b;margin-top:4px;">CNPJ/CPF: {{ $company->document }}</p>
                    @endif
                    @if($company?->formattedAddress())
                        <p style="font-size:11px;color:#64748b;margin-top:2px;">{{ $company->formattedAddress() }}</p>
                    @endif
                    @if($company?->email || $company?->phone)
                        <p style="font-size:11px;color:#64748b;margin-top:2px;">
                            {{ $company->email }}{{ $company->email && $company->phone ? ' · ' : '' }}{{ $company->phone }}
                        </p>
                    @endif
                </td>
                <td style="width: 40%; text-align: right; vertical-align: middle;">
                    <div class="header-badge">{{ $report->periodLabel() }}</div>
                    <div class="header-meta">Emitido em {{ now()->format('d/m/Y \à\s H:i') }}</div>
                </td>
            </tr>
        </table>
    </div>

    {{-- Resumo Executivo --}}
    <div class="exec-summary">
        @php
            $margin = $data['margin'] ?? 0;
            $isProfit = $report->net_result >= 0;
        @endphp
        No período de <strong>{{ $report->periodLabel() }}</strong>, a empresa registrou
        <strong class="c-green">{{ money($report->total_income) }}</strong> em receitas e
        <strong class="c-red">{{ money($report->total_expense) }}</strong> em despesas,
        resultando em um saldo
        <strong class="{{ $isProfit ? 'c-blue' : 'c-red' }}">{{ $isProfit ? 'positivo' : 'negativo' }} de {{ money(abs($report->net_result)) }}</strong>.
        A margem operacional do mês foi de <strong class="{{ $margin >= 0 ? 'c-green' : 'c-red' }}">{{ $margin }}%</strong>.
        @if(($data['pending_count'] ?? 0) > 0)
            <br>⚠️ Atenção: existem <strong class="c-amber">{{ $data['pending_count'] }} lançamentos pendentes</strong> que ainda não foram pagos neste período.
        @endif
    </div>

    {{-- KPI Cards --}}
    <table class="kpi-table">
        <tr>
            <td class="bg-green-light">
                <div class="kpi-label">Receita Total</div>
                <div class="kpi-value c-green">{{ money($report->total_income) }}</div>
                <div class="kpi-sub">{{ ($data['income_by_category'] ? count($data['income_by_category']) : 0) }} categorias</div>
            </td>
            <td class="bg-red-light">
                <div class="kpi-label">Despesa Total</div>
                <div class="kpi-value c-red">{{ money($report->total_expense) }}</div>
                <div class="kpi-sub">{{ ($data['expenses_by_category'] ? count($data['expenses_by_category']) : 0) }} categorias</div>
            </td>
            <td class="bg-blue-light">
                <div class="kpi-label">Resultado Líquido</div>
                <div class="kpi-value {{ $isProfit ? 'c-blue' : 'c-red' }}">{{ money($report->net_result) }}</div>
                <div class="kpi-sub">{{ $data['total_transactions'] ?? 0 }} movimentações</div>
            </td>
            <td class="bg-amber-light">
                <div class="kpi-label">Margem de Lucro</div>
                <div class="kpi-value {{ $margin >= 0 ? 'c-green' : 'c-red' }}">{{ $margin }}%</div>
                <div class="kpi-sub">{{ $data['paid_count'] ?? 0 }} pagos · {{ $data['pending_count'] ?? 0 }} pendentes</div>
            </td>
        </tr>
    </table>

    {{-- Receitas x Despesas por Categoria (Lado a Lado) --}}
    <table class="layout-table">
        <tr>
            <td class="half-td">
                <div class="section">
                    <div class="section-title"><span class="dot dot-green"></span> {{ __('Receitas por Categoria') }}</div>
                    @if(!empty($data['income_by_category']))
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>{{ __('Categoria') }}</th>
                                    <th class="text-right">{{ __('Total') }}</th>
                                    <th style="width: 80px;">%</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($data['income_by_category'] as $cat)
                                    <tr>
                                        <td>{{ $cat['category_name'] }}</td>
                                        <td class="text-right c-green font-bold font-mono">{{ money($cat['total']) }}</td>
                                        <td>
                                            <div style="display: flex; align-items: center; gap: 4px;">
                                                <div class="progress-bar-container">
                                                    <div class="progress-bar progress-green" style="width: {{ $cat['percentage'] }}%;"></div>
                                                </div>
                                                <span style="font-size: 8px; width: 28px; text-align: right;">{{ $cat['percentage'] }}%</span>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <div class="empty-state">{{ __('Nenhuma receita registrada.') }}</div>
                    @endif
                </div>
            </td>
            <td class="spacer-td"></td>
            <td class="half-td">
                <div class="section">
                    <div class="section-title"><span class="dot dot-red"></span> {{ __('Despesas por Categoria') }}</div>
                    @if(!empty($data['expenses_by_category']))
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>{{ __('Categoria') }}</th>
                                    <th class="text-right">{{ __('Total') }}</th>
                                    <th style="width: 80px;">%</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($data['expenses_by_category'] as $cat)
                                    <tr>
                                        <td>{{ $cat['category_name'] }}</td>
                                        <td class="text-right c-red font-bold font-mono">{{ money($cat['total']) }}</td>
                                        <td>
                                            <div style="display: flex; align-items: center; gap: 4px;">
                                                <div class="progress-bar-container">
                                                    <div class="progress-bar progress-red" style="width: {{ $cat['percentage'] }}%;"></div>
                                                </div>
                                                <span style="font-size: 8px; width: 28px; text-align: right;">{{ $cat['percentage'] }}%</span>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <div class="empty-state">{{ __('Nenhuma despesa registrada.') }}</div>
                    @endif
                </div>
            </td>
        </tr>
    </table>

    {{-- Receitas por Cliente + Top 10 Maiores Despesas --}}
    @php
        $expensesOnly = array_filter($data['transactions'] ?? [], fn($t) => $t['transaction_type'] === 'EXPENSE');
        usort($expensesOnly, fn($a, $b) => $b['amount'] <=> $a['amount']);
        $topExpenses = array_slice($expensesOnly, 0, 10);
    @endphp

    <table class="layout-table">
        <tr>
            <td class="half-td">
                <div class="section">
                    <div class="section-title"><span class="dot dot-blue"></span> {{ __('Receitas por Cliente') }}</div>
                    @if(!empty($data['income_by_client']))
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>{{ __('Cliente') }}</th>
                                    <th class="text-center">{{ __('Qtd') }}</th>
                                    <th class="text-right">{{ __('Total') }}</th>
                                    <th class="text-right">%</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($data['income_by_client'] as $client)
                                    <tr>
                                        <td class="font-bold">{{ $client['client_name'] }}</td>
                                        <td class="text-center">{{ $client['count'] }}</td>
                                        <td class="text-right c-green font-bold font-mono">{{ money($client['total']) }}</td>
                                        <td class="text-right c-muted">{{ $client['percentage'] }}%</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <div class="empty-state">{{ __('Nenhum cliente gerou receita.') }}</div>
                    @endif
                </div>
            </td>
            <td class="spacer-td"></td>
            <td class="half-td">
                <div class="section">
                    <div class="section-title"><span class="dot dot-red"></span> {{ __('Top 10 Maiores Despesas') }}</div>
                    @if(!empty($topExpenses))
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>{{ __('Descrição') }}</th>
                                    <th>{{ __('Data') }}</th>
                                    <th class="text-right">{{ __('Valor') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($topExpenses as $exp)
                                    <tr>
                                        <td>{{ \Illuminate\Support\Str::limit($exp['description'], 30) }}</td>
                                        <td>{{ \Carbon\Carbon::parse($exp['due_date'])->format('d/m/Y') }}</td>
                                        <td class="text-right c-red font-bold font-mono">{{ money($exp['amount']) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <div class="empty-state">{{ __('Nenhuma despesa registrada.') }}</div>
                    @endif
                </div>
            </td>
        </tr>
    </table>

    {{-- ════════════════════════════════════════════════ --}}
    {{-- PÁGINA 2 — EXTRATO ANALÍTICO COMPLETO          --}}
    {{-- ════════════════════════════════════════════════ --}}
    <div class="page-break"></div>

    <div class="header">
        <table class="header-top">
            <tr>
                <td style="width: 70%; vertical-align: middle;">
                    <h1>Extrato Analítico</h1>
                    <p class="subtitle">Todas as Movimentações — {{ $report->periodLabel() }}</p>
                </td>
                <td style="width: 30%; text-align: right; vertical-align: middle;">
                    <div class="header-badge">{{ count($data['transactions'] ?? []) }} registros</div>
                </td>
            </tr>
        </table>
    </div>

    <div class="section">
        @if(!empty($data['transactions']))
            @php
                $runningIncomeTotal = 0;
                $runningExpenseTotal = 0;
            @endphp
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width: 65px;">{{ __('Data') }}</th>
                        <th>{{ __('Descrição') }}</th>
                        <th>{{ __('Categoria') }}</th>
                        <th>{{ __('Conta') }}</th>
                        <th class="text-center" style="width: 55px;">{{ __('Tipo') }}</th>
                        <th class="text-center" style="width: 55px;">{{ __('Status') }}</th>
                        <th class="text-right" style="width: 85px;">{{ __('Valor') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($data['transactions'] as $tx)
                        @php
                            if ($tx['transaction_type'] === 'INCOME') {
                                $runningIncomeTotal += $tx['amount'];
                            } else {
                                $runningExpenseTotal += $tx['amount'];
                            }
                        @endphp
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($tx['due_date'])->format('d/m/Y') }}</td>
                            <td>{{ $tx['description'] }}</td>
                            <td>{{ $tx['category']['name'] ?? '—' }}</td>
                            <td>{{ $tx['bank_account']['name'] ?? '—' }}</td>
                            <td class="text-center">
                                @if($tx['transaction_type'] === 'INCOME')
                                    <span class="badge badge-income">Entrada</span>
                                @else
                                    <span class="badge badge-expense">Saída</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($tx['status'] === 'PAID')
                                    <span class="badge badge-paid">Pago</span>
                                @else
                                    <span class="badge badge-pending">Aberto</span>
                                @endif
                            </td>
                            <td class="text-right font-bold font-mono {{ $tx['transaction_type'] === 'INCOME' ? 'c-green' : 'c-red' }}">
                                {{ money($tx['amount']) }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="4" class="text-right font-bold">TOTAIS CONSOLIDADOS</td>
                        <td class="text-center"><span class="badge badge-income">Entradas</span></td>
                        <td></td>
                        <td class="text-right font-bold font-mono c-green">{{ money($runningIncomeTotal) }}</td>
                    </tr>
                    <tr>
                        <td colspan="4"></td>
                        <td class="text-center"><span class="badge badge-expense">Saídas</span></td>
                        <td></td>
                        <td class="text-right font-bold font-mono c-red">{{ money($runningExpenseTotal) }}</td>
                    </tr>
                    <tr>
                        <td colspan="4"></td>
                        <td class="text-center font-bold">Saldo</td>
                        <td></td>
                        <td class="text-right font-bold font-mono {{ ($runningIncomeTotal - $runningExpenseTotal) >= 0 ? 'c-blue' : 'c-red' }}">
                            {{ money($runningIncomeTotal - $runningExpenseTotal) }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        @else
            <div class="empty-state">{{ __('Nenhuma movimentação encontrada para este período.') }}</div>
        @endif
    </div>

    {{-- ════════════════════════════════════════════════ --}}
    {{-- PÁGINA 3 — CONTAS PENDENTES (se houver)        --}}
    {{-- ════════════════════════════════════════════════ --}}
    @if(!empty($data['pending_transactions']))
        <div class="page-break"></div>

        <div class="header" style="background: linear-gradient(135deg, #78350f 0%, #92400e 100%);">
            <table class="header-top">
                <tr>
                    <td style="width: 70%; vertical-align: middle;">
                        <h1>⚠ Contas Pendentes</h1>
                        <p class="subtitle">Lançamentos que ainda não foram pagos ou confirmados</p>
                    </td>
                    <td style="width: 30%; text-align: right; vertical-align: middle;">
                        <div class="header-badge" style="background: rgba(251,191,36,0.3); border-color: rgba(251,191,36,0.4); color: #fde68a;">{{ count($data['pending_transactions']) }} pendentes</div>
                    </td>
                </tr>
            </table>
        </div>

        <div class="alert-box">
            <strong>Atenção:</strong> Os lançamentos abaixo precisam de ação. Verifique se já foram pagos e atualize o status no sistema para manter a precisão dos seus relatórios.
        </div>

        <div class="section">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>{{ __('Vencimento') }}</th>
                        <th>{{ __('Descrição') }}</th>
                        <th>{{ __('Categoria') }}</th>
                        <th class="text-center">{{ __('Tipo') }}</th>
                        <th class="text-right">{{ __('Valor') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @php $totalPending = 0; @endphp
                    @foreach($data['pending_transactions'] as $ptx)
                        @php $totalPending += $ptx['amount']; @endphp
                        <tr>
                            <td class="font-bold">{{ \Carbon\Carbon::parse($ptx['due_date'])->format('d/m/Y') }}</td>
                            <td>{{ $ptx['description'] }}</td>
                            <td>{{ $ptx['category']['name'] ?? '—' }}</td>
                            <td class="text-center">
                                @if($ptx['transaction_type'] === 'INCOME')
                                    <span class="badge badge-income">A Receber</span>
                                @else
                                    <span class="badge badge-expense">A Pagar</span>
                                @endif
                            </td>
                            <td class="text-right font-bold font-mono c-amber">{{ money($ptx['amount']) }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="3" class="text-right font-bold">TOTAL PENDENTE</td>
                        <td></td>
                        <td class="text-right font-bold font-mono c-amber">{{ money($totalPending) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    @endif

    {{-- Rodapé fixo --}}
    <div class="footer">
        <table class="footer-table">
            <tr>
                <td style="width: 40%;">FinControl — Sistema de Gestão Financeira</td>
                <td style="width: 30%; text-align: center;">Documento gerado automaticamente</td>
                <td style="width: 30%; text-align: right;">{{ now()->format('d/m/Y H:i:s') }}</td>
            </tr>
        </table>
    </div>

</body>
</html>
