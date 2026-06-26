@extends('layouts.app')
@section('title', 'Lançamentos')

@section('content')
<div class="topbar">
    <span class="topbar-title">{{ __('Lançamentos') }}</span>
    <div class="topbar-actions">
        @if(auth()->user()->canManageFinances())
            <button class="btn btn-primary" onclick="openModal('modal-novo'); toggleCreditCardFields();">
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
            <a href="{{ route('transactions.index', array_merge($filters, ['status' => 'RECONCILED'])) }}" class="chip {{ ($filters['status'] ?? '') === 'RECONCILED' ? 'active-chip' : '' }}">{{ __('Conciliados') }}</a>
            <a href="{{ route('transactions.index', array_merge($filters, ['status' => 'CANCELED'])) }}" class="chip {{ ($filters['status'] ?? '') === 'CANCELED' ? 'active-chip' : '' }}">{{ __('Cancelados') }}</a>
            <a href="{{ route('transactions.index', array_merge($filters, ['quick_period' => 'today'])) }}" class="chip {{ ($filters['quick_period'] ?? '') === 'today' ? 'active-chip' : '' }}">{{ __('Hoje') }}</a>
            <a href="{{ route('transactions.index', array_merge($filters, ['quick_period' => 'this_week'])) }}" class="chip {{ ($filters['quick_period'] ?? '') === 'this_week' ? 'active-chip' : '' }}">{{ __('Esta semana') }}</a>
            <a href="{{ route('transactions.index', array_merge($filters, ['quick_period' => 'this_month'])) }}" class="chip {{ ($filters['quick_period'] ?? '') === 'this_month' ? 'active-chip' : '' }}">{{ __('Este mês') }}</a>
            <a href="{{ route('transactions.index', array_merge($filters, ['quick_period' => 'last_month'])) }}" class="chip {{ ($filters['quick_period'] ?? '') === 'last_month' ? 'active-chip' : '' }}">{{ __('Mês anterior') }}</a>
            <div style="flex:1"></div>
            <select name="bank_account_id" style="width:auto;font-size:12px;padding:5px 8px" onchange="this.form.submit()">
                <option value="">{{ __('Todas as contas') }}</option>
                @foreach($bankAccounts as $account)
                    <option value="{{ $account->id }}" {{ ($filters['bank_account_id'] ?? '') == $account->id ? 'selected' : '' }}>
                        {{ $account->name }}
                    </option>
                @endforeach
            </select>
            <select name="transaction_type" style="width:auto;font-size:12px;padding:5px 8px" onchange="this.form.submit()">
                <option value="">{{ __('Tipo') }}</option>
                <option value="INCOME" {{ ($filters['transaction_type'] ?? '') == 'INCOME' ? 'selected' : '' }}>{{ __('Entrada') }}</option>
                <option value="EXPENSE" {{ ($filters['transaction_type'] ?? '') == 'EXPENSE' ? 'selected' : '' }}>{{ __('Saída') }}</option>
            </select>
            <select name="category_id" style="width:auto;font-size:12px;padding:5px 8px" onchange="this.form.submit()">
                <option value="">{{ __('Categoria') }}</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ ($filters['category_id'] ?? '') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                @endforeach
            </select>
            <select name="period" style="width:auto;font-size:12px;padding:5px 8px" onchange="this.form.submit()">
                <option value="">{{ __('Período (Personalizado)') }}</option>
                <option value="7" {{ ($filters['period'] ?? '') == '7' ? 'selected' : '' }}>{{ __('Próximos 7 dias') }}</option>
                <option value="15" {{ ($filters['period'] ?? '') == '15' ? 'selected' : '' }}>{{ __('Próximos 15 dias') }}</option>
                <option value="30" {{ ($filters['period'] ?? '') == '30' ? 'selected' : '' }}>{{ __('Próximos 30 dias') }}</option>
            </select>
            <input type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}" style="width:auto;font-size:12px;padding:5px 8px" onchange="document.querySelector('[name=period]').value=''; this.form.submit()">
            <input type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}" style="width:auto;font-size:12px;padding:5px 8px" onchange="document.querySelector('[name=period]').value=''; this.form.submit()">
        </div>
    </form>

    {{-- Gráfico do topo --}}
    <div class="card" style="margin-bottom: 24px; padding: 16px; border-radius: 16px;">
        <div style="height: 200px; width: 100%;">
            <canvas id="topChart" data-chart='@json($chartData)'></canvas>
        </div>
    </div>

    {{-- Tabela de lançamentos --}}
    <div class="table-wrap">
        <table class="android-list-table">
            <thead>
                <tr>
                    <th>{{ __('Data') }}</th>
                    <th>{{ __('Descrição') }}</th>
                    <th>{{ __('Categoria') }}</th>
                    <th>{{ __('Conta') }}</th>
                    <th>{{ __('Tipo') }}</th>
                    <th>{{ __('Valor') }}</th>
                    <th>{{ __('NF') }}</th>
                    <th>{{ __('Status') }}</th>
                    <th>{{ __('Ações') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($transactions as $tx)
                    <tr>
                        <td data-label="{{ __('Data') }}">{{ $tx->due_date->format('d/m/Y') }}</td>
                        <td data-label="{{ __('Descrição') }}">{{ $tx->description }}</td>
                        <td data-label="{{ __('Categoria') }}">{{ $tx->category->name ?? '—' }}</td>
                        <td data-label="{{ __('Conta') }}">{{ $tx->bankAccount->name }}</td>
                        <td data-label="{{ __('Tipo') }}"><span class="tag {{ $tx->transaction_type->cssClass() }}">{{ $tx->transaction_type->label() }}</span></td>
                        <td data-label="{{ __('Valor') }}" style="color:{{ $tx->isIncome() ? 'var(--color-text-success)' : 'var(--color-text-danger)' }};font-weight:500">
                            {{ money($tx->amount) }}
                        </td>
                        <td data-label="{{ __('NF') }}">
                            @if($tx->invoice_document_url)
                                <a href="{{ asset('storage/' . $tx->invoice_document_url) }}" target="_blank" title="{{ __('Ver nota fiscal') }}">
                                    <i class="ti ti-file-invoice" style="color:var(--color-text-info);font-size:15px"></i>
                                </a>
                            @else
                                —
                            @endif
                        </td>
                        <td data-label="{{ __('Status') }}"><span class="badge {{ $tx->status->badgeClass() }}">{{ $tx->status->label() }}</span></td>
                        <td data-label="{{ __('Ações') }}" class="action-cell-td">
                            <div class="action-cell">
                                @if($tx->isPending() && auth()->user()->canManageFinances())
                                    <form method="POST" action="{{ route('transactions.pay', $tx) }}" style="display:inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm"><i class="ti ti-check"></i>{{ __('Pagar') }}</button>
                                    </form>
                                    <form method="POST" action="{{ route('transactions.cancel', $tx) }}" style="display:inline" onsubmit="return confirm('{{ __('Cancelar este lançamento?') }}')">
                                        @csrf
                                        <button type="submit" class="btn btn-sm" style="color:var(--color-text-danger)"><i class="ti ti-x"></i></button>
                                    </form>
                                    <i class="ti ti-edit action-icon" onclick="openModal('modal-edit-{{ $tx->id }}')" title="{{ __('Editar') }}"></i>
                                @endif
                                @if(($tx->isPaid() || $tx->isReconciled()) && auth()->user()->can('update', $tx))
                                    <i class="ti ti-edit action-icon" onclick="openModal('modal-edit-{{ $tx->id }}')" title="{{ __('Editar') }}"></i>
                                @endif
                                @if(auth()->user()->can('reverse', $tx))
                                    <form method="POST" action="{{ route('transactions.reverse', $tx) }}" style="display:inline" onsubmit="return confirm('{{ __('Estornar este lançamento? Será criada uma transação inversa.') }}')">
                                        @csrf
                                        <button type="submit" style="background:none;border:none;cursor:pointer;padding:0">
                                            <i class="ti ti-arrow-back-up action-icon" style="color:var(--color-text-warning)" title="{{ __('Estornar') }}"></i>
                                        </button>
                                    </form>
                                @endif
                                @if($tx->wasReversed())
                                    <span class="badge badge-info" style="font-size:10px">{{ __('Estornado') }}</span>
                                @endif
                                @if($tx->isPaid() && auth()->user()->canManageFinances())
                                    <form method="POST" action="{{ route('transactions.reconcile', $tx) }}" style="display:inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm"><i class="ti ti-link"></i>{{ __('Conciliar') }}</button>
                                    </form>
                                    <i class="ti ti-eye action-icon" onclick="openModal('modal-detail-{{ $tx->id }}')" title="{{ __('Ver detalhes') }}"></i>
                                @endif
                                @if(($tx->isReconciled() || $tx->isCanceled()) && auth()->user()->canManageFinances())
                                    <i class="ti ti-eye action-icon" onclick="openModal('modal-detail-{{ $tx->id }}')" title="{{ __('Ver detalhes') }}"></i>
                                @endif
                                @if(auth()->user()->can('delete', $tx))
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
                    <select name="transaction_type" id="new-type" required onchange="toggleCreditCardFields(); filterCategoriesByType('new-type', 'new-category');">
                        <option value="INCOME">{{ __('Entrada') }}</option>
                        <option value="EXPENSE">{{ __('Saída') }}</option>
                    </select>
                </div>
                <div class="form-group" id="field-due-date">
                    <label class="form-label">{{ __('Data de vencimento') }}</label>
                    <input type="date" name="due_date" id="new-due-date">
                </div>
                <div class="form-group" id="field-purchase-date" style="display:none">
                    <label class="form-label">{{ __('Data da compra') }}</label>
                    <input type="date" name="purchase_date" id="new-purchase-date" value="{{ now()->format('Y-m-d') }}">
                </div>
                <div class="form-group" id="field-competence-date">
                    <label class="form-label">{{ __('Data de competência') }}</label>
                    <input type="date" name="competence_date" id="new-competence-date" value="{{ now()->format('Y-m-d') }}">
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
            <div class="form-row" id="field-payment-method">
                <div class="form-group">
                    <label class="form-label">{{ __('Método de pagamento') }}</label>
                    <select name="payment_method" id="new-payment-method">
                        <option value="PIX">PIX</option>
                        <option value="BOLETO">{{ __('Boleto') }}</option>
                        <option value="CARTAO">{{ __('Cartão') }}</option>
                        <option value="TRANSFERENCIA">{{ __('Transferência') }}</option>
                        <option value="DINHEIRO">{{ __('Dinheiro') }}</option>
                        <option value="OUTRO">{{ __('Outro') }}</option>
                    </select>
                </div>
            </div>
            <div class="form-row" id="credit-card-fields" style="display:none">
                <div class="form-group">
                    <label class="form-label">{{ __('Cartão de crédito') }}</label>
                    <select name="credit_card_id" id="new-credit-card" onchange="toggleCreditCardFields()">
                        <option value="">{{ __('— Nenhum (débito em conta) —') }}</option>
                        @foreach($creditCards as $card)
                            <option value="{{ $card->id }}">{{ $card->displayName() }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group" id="field-installments" style="display:none">
                    <label class="form-label">{{ __('Número de Parcelas') }}</label>
                    <input type="number" name="installments" id="new-installments" min="1" max="48" value="1" placeholder="1">
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
                    <select name="category_id" id="new-category">
                        <option value="">{{ __('— Selecione —') }}</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" data-type="{{ $cat->type }}" data-requires-client="{{ $cat->requires_client ? '1' : '0' }}">{{ $cat->name }}</option>
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

{{-- Modais de detalhe para lançamentos bloqueados --}}
@foreach($transactions->filter(fn ($tx) => ! $tx->isPending()) as $tx)
<div class="modal-overlay" id="modal-detail-{{ $tx->id }}">
    <div class="modal">
        <div class="modal-header">
            <h3>{{ __('Detalhes do lançamento') }}</h3>
            <i class="ti ti-x" style="cursor:pointer;font-size:18px;color:var(--color-text-secondary)" onclick="closeModal('modal-detail-{{ $tx->id }}')"></i>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;font-size:13px">
            <div><span style="color:var(--color-text-secondary);font-size:12px">{{ __('Descrição') }}</span><p style="font-weight:500;margin-top:2px">{{ $tx->description }}</p></div>
            <div><span style="color:var(--color-text-secondary);font-size:12px">{{ __('Data vencimento') }}</span><p style="font-weight:500;margin-top:2px">{{ $tx->due_date->format('d/m/Y') }}</p></div>
            @if($tx->competence_date)
            <div><span style="color:var(--color-text-secondary);font-size:12px">{{ __('Competência') }}</span><p style="font-weight:500;margin-top:2px">{{ $tx->competence_date->format('d/m/Y') }}</p></div>
            @endif
            <div><span style="color:var(--color-text-secondary);font-size:12px">{{ __('Valor') }}</span><p style="font-weight:500;margin-top:2px;color:{{ $tx->isIncome() ? 'var(--color-text-success)' : 'var(--color-text-danger)' }}">{{ money($tx->amount) }}</p></div>
            <div><span style="color:var(--color-text-secondary);font-size:12px">{{ __('Conta') }}</span><p style="font-weight:500;margin-top:2px">{{ $tx->bankAccount->name }}</p></div>
            <div><span style="color:var(--color-text-secondary);font-size:12px">{{ __('Categoria') }}</span><p style="font-weight:500;margin-top:2px">{{ $tx->category->name ?? '—' }}</p></div>
            <div><span style="color:var(--color-text-secondary);font-size:12px">{{ __('Status') }}</span><p style="margin-top:4px"><span class="badge {{ $tx->status->badgeClass() }}">{{ $tx->status->label() }}</span></p></div>
            @if($tx->payment_method)
            <div><span style="color:var(--color-text-secondary);font-size:12px">{{ __('Método') }}</span><p style="font-weight:500;margin-top:2px">{{ $tx->payment_method->label() }}</p></div>
            @endif
            @if($tx->payment_date)
            <div><span style="color:var(--color-text-secondary);font-size:12px">{{ __('Data pagamento') }}</span><p style="font-weight:500;margin-top:2px">{{ $tx->payment_date->format('d/m/Y') }}</p></div>
            @endif
        </div>
        <div style="margin-top:14px;padding:10px 12px;background:var(--color-background-secondary);border-radius:var(--border-radius-md);font-size:12px;color:var(--color-text-secondary);display:flex;align-items:center;gap:6px">
            <i class="ti ti-lock" style="font-size:14px"></i>
            {{ __('Lançamento bloqueado para edição') }}
        </div>
        <div style="display:flex;justify-content:flex-end;margin-top:16px">
            <button class="btn" onclick="closeModal('modal-detail-{{ $tx->id }}')">{{ __('Fechar') }}</button>
        </div>
    </div>
</div>
@endforeach

{{-- Modais de edição para lançamentos pendentes --}}
@foreach($transactions->filter(fn ($tx) => auth()->user()->can('update', $tx)) as $tx)
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
                    <select name="transaction_type" id="edit-type-{{ $tx->id }}" required onchange="filterCategoriesByType('edit-type-{{ $tx->id }}', 'edit-category-{{ $tx->id }}')">
                        <option value="INCOME" {{ $tx->transaction_type->value === 'INCOME' ? 'selected' : '' }}>{{ __('Entrada') }}</option>
                        <option value="EXPENSE" {{ $tx->transaction_type->value === 'EXPENSE' ? 'selected' : '' }}>{{ __('Saída') }}</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">{{ __('Data de vencimento') }}</label>
                    <input type="date" name="due_date" value="{{ $tx->due_date->format('Y-m-d') }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label">{{ __('Data de competência') }}</label>
                    <input type="date" name="competence_date" value="{{ $tx->competence_date?->format('Y-m-d') ?? $tx->due_date->format('Y-m-d') }}">
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
                        @if($tx->bankAccount && ! $bankAccounts->contains('id', $tx->bank_account_id))
                            <option value="{{ $tx->bank_account_id }}" selected>{{ $tx->bankAccount->name }} ({{ __('Inativa') }})</option>
                        @endif
                    </select>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">{{ __('Método de pagamento') }}</label>
                    <select name="payment_method">
                        @foreach(\App\Enums\PaymentMethod::cases() as $method)
                            <option value="{{ $method->value }}" {{ $tx->payment_method?->value === $method->value ? 'selected' : '' }}>{{ $method->label() }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">{{ __('Categoria') }}</label>
                    <select name="category_id" id="edit-category-{{ $tx->id }}">
                        <option value="">{{ __('— Selecione —') }}</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" data-type="{{ $cat->type }}" {{ $tx->category_id == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
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
    window.toggleCreditCardFields = function () {
        var typeEl = document.getElementById('new-type');
        var cardEl = document.getElementById('new-credit-card');
        if (!typeEl || !cardEl) return;

        var type = typeEl.value;
        var cardId = cardEl.value;
        var isExpense = type === 'EXPENSE';
        var hasCard = isExpense && cardId !== '';

        var cardFields = document.getElementById('credit-card-fields');
        var installmentsField = document.getElementById('field-installments');
        var dueDateField = document.getElementById('field-due-date');
        var purchaseDateField = document.getElementById('field-purchase-date');
        var dueDateInput = document.getElementById('new-due-date');
        var purchaseDateInput = document.getElementById('new-purchase-date');
        var installmentsInput = document.getElementById('new-installments');

        if (cardFields) cardFields.style.display = isExpense ? 'grid' : 'none';
        if (installmentsField) installmentsField.style.display = hasCard ? 'block' : 'none';
        if (dueDateField) dueDateField.style.display = hasCard ? 'none' : 'block';
        if (purchaseDateField) purchaseDateField.style.display = hasCard ? 'block' : 'none';

        var paymentMethodField = document.getElementById('field-payment-method');
        var paymentMethodInput = document.getElementById('new-payment-method');
        if (paymentMethodField && paymentMethodInput) {
            paymentMethodField.style.display = isExpense && !hasCard ? 'grid' : 'none';
            paymentMethodInput.required = isExpense && !hasCard;
        }

        if (dueDateInput) dueDateInput.required = isExpense && !hasCard;
        if (purchaseDateInput) purchaseDateInput.required = hasCard;
        if (installmentsInput) installmentsInput.required = hasCard;

        var balanceAlert = document.getElementById('balance-alert');
        if (!hasCard) {
            if (balanceAlert) balanceAlert.style.display = 'none';
        } else {
            window.checkImpact();
        }
    };

    window.filterCategoriesByType = function (typeSelectId, categorySelectId) {
        var typeEl = document.getElementById(typeSelectId);
        var categoryEl = document.getElementById(categorySelectId);
        if (!typeEl || !categoryEl) return;

        var type = typeEl.value;
        Array.from(categoryEl.options).forEach(function (option) {
            if (!option.value) {
                option.hidden = false;
                return;
            }
            option.hidden = !(option.dataset.type === type || option.dataset.type === 'BOTH');
        });

        var selected = categoryEl.options[categoryEl.selectedIndex];
        if (selected && selected.hidden) {
            categoryEl.value = '';
        }
    };

    window.checkImpact = function () {
        var amount = parseFloat(document.getElementById('new-amount').value);
        var type = document.getElementById('new-type').value;
        var accountId = document.getElementById('new-account').value;
        var cardId = document.getElementById('new-credit-card').value;

        if (!amount || amount <= 0 || type !== 'EXPENSE' || cardId) {
            var alertEl = document.getElementById('balance-alert');
            if (alertEl) alertEl.style.display = 'none';
            return;
        }

        fetch("{{ route('transactions.check-impact') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
            },
            body: JSON.stringify({ bank_account_id: accountId, amount: amount, transaction_type: type }),
        })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            var alertBox = document.getElementById('balance-alert');
            if (!alertBox) return;
            if (data.will_be_negative) {
                document.getElementById('balance-alert-msg').textContent =
                    '{{ __('Atenção: A conta') }} "' + data.account_name + '" {{ __('ficará negativa! Saldo projetado:') }} {{ currency_symbol() }} ' + data.projected_balance.toFixed(2).replace('.', ',');
                alertBox.style.display = 'flex';
            } else {
                alertBox.style.display = 'none';
            }
        });
    };

    window.initTransactionsPage = function () {
        if (document.getElementById('new-type')) {
            window.toggleCreditCardFields();
            window.filterCategoriesByType('new-type', 'new-category');
        }

        document.querySelectorAll('[id^="edit-type-"]').forEach(function (typeEl) {
            var txId = typeEl.id.replace('edit-type-', '');
            window.filterCategoriesByType(typeEl.id, 'edit-category-' + txId);
        });

        var ctx = document.getElementById('topChart');
        if (!ctx) return;

        var chartData = [];
        try {
            chartData = JSON.parse(ctx.dataset.chart || '[]');
        } catch (e) {
            chartData = [];
        }
        if (!chartData.length) return;

        var dates = [...new Set(chartData.map(function (d) { return d.date; }))].sort();
        var incomeData = dates.map(function (date) {
            var item = chartData.find(function (d) { return d.date === date && d.transaction_type === 'INCOME'; });
            return item ? parseFloat(item.total) : 0;
        });
        var expenseData = dates.map(function (date) {
            var item = chartData.find(function (d) { return d.date === date && d.transaction_type === 'EXPENSE'; });
            return item ? parseFloat(item.total) : 0;
        });
        var labels = dates.map(function (date) {
            var parts = date.split('-');
            return parts[2] + '/' + parts[1];
        });

        var ctxCanvas = ctx.getContext('2d');
        var incomeGradient = ctxCanvas.createLinearGradient(0, 0, 0, 300);
        incomeGradient.addColorStop(0, 'rgba(34, 197, 94, 0.15)');
        incomeGradient.addColorStop(1, 'rgba(34, 197, 94, 0.0)');
        var expenseGradient = ctxCanvas.createLinearGradient(0, 0, 0, 300);
        expenseGradient.addColorStop(0, 'rgba(239, 68, 68, 0.15)');
        expenseGradient.addColorStop(1, 'rgba(239, 68, 68, 0.0)');

        Chart.defaults.font.family = "'Inter', system-ui, -apple-system, sans-serif";
        Chart.defaults.color = '#9ca3af';

        if (window.transactionsTopChart instanceof Chart) {
            window.transactionsTopChart.destroy();
        }

        window.transactionsTopChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: '{{ __("Entradas") }}',
                        data: incomeData,
                        borderColor: '#4ade80',
                        backgroundColor: incomeGradient,
                        borderWidth: 3,
                        tension: 0.4,
                        fill: true
                    },
                    {
                        label: '{{ __("Saídas") }}',
                        data: expenseData,
                        borderColor: '#f87171',
                        backgroundColor: expenseGradient,
                        borderWidth: 3,
                        tension: 0.4,
                        fill: true
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: { legend: { position: 'top' } },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function (value) {
                                return window.formatChartCurrency(value, true);
                            }
                        }
                    },
                    x: { grid: { display: false } }
                }
            }
        });
    };

    window.initTransactionsPage();

    if (!window.__transactionsTurboBound) {
        window.__transactionsTurboBound = true;
        document.addEventListener('turbo:load', window.initTransactionsPage);
    }
</script>
@endpush
@endsection
