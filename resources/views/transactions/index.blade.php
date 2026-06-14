@extends('layouts.app')
@section('title', 'Lançamentos')

@section('content')
<div class="topbar">
    <span class="topbar-title">{{ __('Lançamentos') }}</span>
    <div class="topbar-actions">
        @if(auth()->user()->isAdmin())
            <button class="btn btn-primary" onclick="openModal('modal-novo')">
                <i class="ti ti-plus"></i>{{ __('Novo lançamento') }}
            </button>
        @endif
    </div>
</div>

<div class="content">
    {{-- Filtros --}}
    <form method="GET" action="{{ route('transactions.index') }}">
        <div class="filter-row">
            <a href="{{ route('transactions.index') }}" class="chip {{ empty($filters['status']) ? 'active-chip' : '' }}">{{ __('Todos') }}</a>
            <a href="{{ route('transactions.index', array_merge($filters, ['status' => 'PAID'])) }}" class="chip {{ ($filters['status'] ?? '') === 'PAID' ? 'active-chip' : '' }}">{{ __('Pagos') }}</a>
            <a href="{{ route('transactions.index', array_merge($filters, ['status' => 'PENDING'])) }}" class="chip {{ ($filters['status'] ?? '') === 'PENDING' ? 'active-chip' : '' }}">{{ __('Em aberto') }}</a>
            <div style="flex:1"></div>
            <select name="bank_account_id" style="width:auto;font-size:12px;padding:5px 8px" onchange="this.form.submit()">
                <option value="">{{ __('Todas as contas') }}</option>
                @foreach($bankAccounts as $account)
                    <option value="{{ $account->id }}" {{ ($filters['bank_account_id'] ?? '') == $account->id ? 'selected' : '' }}>
                        {{ $account->name }}
                    </option>
                @endforeach
            </select>
            <input type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}" style="width:auto;font-size:12px;padding:5px 8px" onchange="this.form.submit()">
            <input type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}" style="width:auto;font-size:12px;padding:5px 8px" onchange="this.form.submit()">
        </div>
    </form>

    {{-- Tabela de lançamentos --}}
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>{{ __('Data') }}</th>
                    <th>{{ __('Descrição') }}</th>
                    <th class="hide-on-mobile">{{ __('Categoria') }}</th>
                    <th class="hide-on-mobile">{{ __('Conta') }}</th>
                    <th class="hide-on-mobile">{{ __('Tipo') }}</th>
                    <th>{{ __('Valor') }}</th>
                    <th class="hide-on-mobile">{{ __('NF') }}</th>
                    <th>{{ __('Status') }}</th>
                    <th>{{ __('Ações') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($transactions as $tx)
                    <tr>
                        <td>{{ $tx->due_date->format('d/m/Y') }}</td>
                        <td>{{ $tx->description }}</td>
                        <td class="hide-on-mobile">{{ $tx->category->name ?? '—' }}</td>
                        <td class="hide-on-mobile">{{ $tx->bankAccount->name }}</td>
                        <td class="hide-on-mobile"><span class="tag {{ $tx->transaction_type->cssClass() }}">{{ $tx->transaction_type->label() }}</span></td>
                        <td style="color:{{ $tx->isIncome() ? 'var(--color-text-success)' : 'var(--color-text-danger)' }};font-weight:500">
                            {{ money($tx->amount) }}
                        </td>
                        <td class="hide-on-mobile">
                            @if($tx->invoice_document_url)
                                <a href="{{ asset('storage/' . $tx->invoice_document_url) }}" target="_blank" title="{{ __('Ver nota fiscal') }}">
                                    <i class="ti ti-file-invoice" style="color:var(--color-text-info);font-size:15px"></i>
                                </a>
                            @else
                                —
                            @endif
                        </td>
                        <td><span class="badge {{ $tx->status->badgeClass() }}">{{ $tx->status->label() }}</span></td>
                        <td>
                            <div class="action-cell">
                                @if($tx->isPending() && auth()->user()->isAdmin())
                                    <form method="POST" action="{{ route('transactions.pay', $tx) }}" style="display:inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm"><i class="ti ti-check"></i>{{ __('Pagar') }}</button>
                                    </form>
                                    <i class="ti ti-edit action-icon" onclick="openModal('modal-edit-{{ $tx->id }}')" title="{{ __('Editar') }}"></i>
                                @endif
                                @if($tx->isPaid())
                                    <i class="ti ti-eye action-icon" onclick="openModal('modal-detail-{{ $tx->id }}')" title="{{ __('Ver detalhes') }}"></i>
                                @endif
                                @if(auth()->user()->canDeleteTransactions())
                                    <form method="POST" action="{{ route('transactions.destroy', $tx) }}" style="display:inline"
                                          onsubmit="return confirm('{{ __('Excluir este lançamento?') }}')">
                                        @csrf @method('DELETE')
                                        <button type="submit" style="background:none;border:none;cursor:pointer;padding:0">
                                            <i class="ti ti-trash action-icon" style="color:var(--color-text-danger)" title="{{ __('Excluir') }}"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="9" style="color:var(--color-text-tertiary);text-align:center">{{ __('Nenhum lançamento encontrado.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Paginação --}}
    @if($transactions->hasPages())
        {{ $transactions->appends($filters)->links() }}
    @endif
</div>

{{-- Modal: Novo lançamento --}}
<div class="modal-overlay" id="modal-novo">
    <div class="modal">
        <div class="modal-header">
            <h3>{{ __('Novo lançamento') }}</h3>
            <i class="ti ti-x" style="cursor:pointer;font-size:18px;color:var(--color-text-secondary)" onclick="closeModal('modal-novo')"></i>
        </div>
        <form method="POST" action="{{ route('transactions.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">{{ __('Tipo') }}</label>
                    <select name="transaction_type" id="new-type" required>
                        <option value="INCOME">{{ __('Entrada') }}</option>
                        <option value="EXPENSE">{{ __('Saída') }}</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">{{ __('Data de vencimento') }}</label>
                    <input type="date" name="due_date" required>
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">{{ __('Descrição') }}</label>
                <input type="text" name="description" placeholder="{{ __('Ex: Serviço de consultoria') }}" required>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">{{ __('Valor') }}</label>
                    <input type="number" name="amount" step="0.01" min="0.01" placeholder="0,00" required
                           id="new-amount" onchange="checkImpact()">
                </div>
                <div class="form-group">
                    <label class="form-label">{{ __('Conta bancária') }}</label>
                    <select name="bank_account_id" id="new-account" required onchange="checkImpact()">
                        @foreach($bankAccounts as $account)
                            <option value="{{ $account->id }}">{{ $account->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            {{-- Alerta de saldo negativo --}}
            <div class="alert alert-danger" id="balance-alert" style="display:none">
                <i class="ti ti-alert-triangle"></i>
                <span id="balance-alert-msg"></span>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">{{ __('Categoria') }}</label>
                    <select name="category_id">
                        <option value="">{{ __('— Selecione —') }}</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">{{ __('Cliente (opcional)') }}</label>
                    <select name="client_id">
                        <option value="">{{ __('— Nenhum —') }}</option>
                        @foreach($clients as $client)
                            <option value="{{ $client->id }}">{{ $client->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">{{ __('Nota fiscal') }} <span style="color:var(--color-text-tertiary);font-size:11px">{{ __('(opcional — PDF, JPG, PNG até 5MB)') }}</span></label>
                <input type="file" name="invoice_document" style="padding:5px" accept=".pdf,.jpg,.jpeg,.png">
            </div>
            <div style="display:flex;justify-content:flex-end;gap:8px;margin-top:16px">
                <button type="button" class="btn" onclick="closeModal('modal-novo')">{{ __('Cancelar') }}</button>
                <button type="submit" class="btn btn-primary">{{ __('Salvar') }}</button>
            </div>
        </form>
    </div>
</div>

{{-- Modais de detalhe para lançamentos pagos --}}
@foreach($transactions->where('status.value', 'PAID') as $tx)
<div class="modal-overlay" id="modal-detail-{{ $tx->id }}">
    <div class="modal">
        <div class="modal-header">
            <h3>{{ __('Detalhes do lançamento') }}</h3>
            <i class="ti ti-x" style="cursor:pointer;font-size:18px;color:var(--color-text-secondary)" onclick="closeModal('modal-detail-{{ $tx->id }}')"></i>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;font-size:13px">
            <div><span style="color:var(--color-text-secondary);font-size:12px">{{ __('Descrição') }}</span><p style="font-weight:500;margin-top:2px">{{ $tx->description }}</p></div>
            <div><span style="color:var(--color-text-secondary);font-size:12px">{{ __('Data vencimento') }}</span><p style="font-weight:500;margin-top:2px">{{ $tx->due_date->format('d/m/Y') }}</p></div>
            <div><span style="color:var(--color-text-secondary);font-size:12px">{{ __('Valor') }}</span><p style="font-weight:500;margin-top:2px;color:{{ $tx->isIncome() ? 'var(--color-text-success)' : 'var(--color-text-danger)' }}">{{ money($tx->amount) }}</p></div>
            <div><span style="color:var(--color-text-secondary);font-size:12px">{{ __('Conta') }}</span><p style="font-weight:500;margin-top:2px">{{ $tx->bankAccount->name }}</p></div>
            <div><span style="color:var(--color-text-secondary);font-size:12px">{{ __('Categoria') }}</span><p style="font-weight:500;margin-top:2px">{{ $tx->category->name ?? '—' }}</p></div>
            <div><span style="color:var(--color-text-secondary);font-size:12px">{{ __('Status') }}</span><p style="margin-top:4px"><span class="badge badge-success">{{ __('Pago') }}</span></p></div>
            @if($tx->payment_date)
            <div><span style="color:var(--color-text-secondary);font-size:12px">{{ __('Data pagamento') }}</span><p style="font-weight:500;margin-top:2px">{{ $tx->payment_date->format('d/m/Y') }}</p></div>
            @endif
        </div>
        <div style="margin-top:14px;padding:10px 12px;background:var(--color-background-secondary);border-radius:var(--border-radius-md);font-size:12px;color:var(--color-text-secondary);display:flex;align-items:center;gap:6px">
            <i class="ti ti-lock" style="font-size:14px"></i>
            {{ __('Lançamento pago — edição bloqueada') }}
        </div>
        <div style="display:flex;justify-content:flex-end;margin-top:16px">
            <button class="btn" onclick="closeModal('modal-detail-{{ $tx->id }}')">{{ __('Fechar') }}</button>
        </div>
    </div>
</div>
@endforeach

{{-- Modais de edição para lançamentos pendentes --}}
@foreach($transactions->where('status.value', 'PENDING') as $tx)
<div class="modal-overlay" id="modal-edit-{{ $tx->id }}">
    <div class="modal">
        <div class="modal-header">
            <h3>{{ __('Editar lançamento') }}</h3>
            <i class="ti ti-x" style="cursor:pointer;font-size:18px;color:var(--color-text-secondary)" onclick="closeModal('modal-edit-{{ $tx->id }}')"></i>
        </div>
        <form method="POST" action="{{ route('transactions.update', $tx) }}" enctype="multipart/form-data">
            @csrf @method('PUT')
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">{{ __('Tipo') }}</label>
                    <select name="transaction_type" required>
                        <option value="INCOME" {{ $tx->transaction_type->value === 'INCOME' ? 'selected' : '' }}>{{ __('Entrada') }}</option>
                        <option value="EXPENSE" {{ $tx->transaction_type->value === 'EXPENSE' ? 'selected' : '' }}>{{ __('Saída') }}</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">{{ __('Data de vencimento') }}</label>
                    <input type="date" name="due_date" value="{{ $tx->due_date->format('Y-m-d') }}" required>
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">{{ __('Descrição') }}</label>
                <input type="text" name="description" value="{{ $tx->description }}" required>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">{{ __('Valor') }}</label>
                    <input type="number" name="amount" step="0.01" min="0.01" value="{{ $tx->amount }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label">{{ __('Conta bancária') }}</label>
                    <select name="bank_account_id" required>
                        @foreach($bankAccounts as $account)
                            <option value="{{ $account->id }}" {{ $tx->bank_account_id == $account->id ? 'selected' : '' }}>{{ $account->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">{{ __('Categoria') }}</label>
                    <select name="category_id">
                        <option value="">{{ __('— Selecione —') }}</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ $tx->category_id == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">{{ __('Cliente (opcional)') }}</label>
                    <select name="client_id">
                        <option value="">{{ __('— Nenhum —') }}</option>
                        @foreach($clients as $client)
                            <option value="{{ $client->id }}" {{ $tx->client_id == $client->id ? 'selected' : '' }}>{{ $client->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">{{ __('Nota fiscal') }} <span style="color:var(--color-text-tertiary);font-size:11px">{{ __('(opcional — PDF, JPG, PNG até 5MB)') }}</span></label>
                <input type="file" name="invoice_document" style="padding:5px" accept=".pdf,.jpg,.jpeg,.png">
                @if($tx->invoice_document_url)
                    <span style="font-size:11px;color:var(--color-text-tertiary);margin-top:4px;display:block">{{ __('Já possui arquivo anexado.') }}</span>
                @endif
            </div>
            <div style="display:flex;justify-content:flex-end;gap:8px;margin-top:16px">
                <button type="button" class="btn" onclick="closeModal('modal-edit-{{ $tx->id }}')">{{ __('Cancelar') }}</button>
                <button type="submit" class="btn btn-primary">{{ __('Salvar alterações') }}</button>
            </div>
        </form>
    </div>
</div>
@endforeach

@push('scripts')
<script>
    /**
     * RF04 — Verificação de impacto no saldo via AJAX.
     */
    function checkImpact() {
        const amount = parseFloat(document.getElementById('new-amount').value);
        const type = document.getElementById('new-type').value;
        const accountId = document.getElementById('new-account').value;

        if (!amount || amount <= 0 || type !== 'EXPENSE') {
            document.getElementById('balance-alert').style.display = 'none';
            return;
        }

        fetch("{{ route('transactions.check-impact') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
            },
            body: JSON.stringify({ bank_account_id: accountId, amount, transaction_type: type }),
        })
        .then(r => r.json())
        .then(data => {
            const alert = document.getElementById('balance-alert');
            if (data.will_be_negative) {
                document.getElementById('balance-alert-msg').textContent =
                    `{{ __('Atenção: A conta') }} "${data.account_name}" {{ __('ficará negativa! Saldo projetado:') }} {{ currency_symbol() }} ${data.projected_balance.toFixed(2).replace('.', ',')}`;
                alert.style.display = 'flex';
            } else {
                alert.style.display = 'none';
            }
        });
    }
</script>
@endpush
@endsection
