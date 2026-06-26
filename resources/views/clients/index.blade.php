@extends('layouts.app')
@section('title', 'Clientes')

@section('content')
<div class="topbar">
    <span class="topbar-title">{{ __('Clientes') }}</span>
    @if(auth()->user()->canManageFinances())
        <button class="btn btn-primary" onclick="openModal('modal-client')"><i class="ti ti-plus"></i>{{ __('Novo cliente') }}</button>
    @endif
</div>

<div class="content">
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>{{ __('Nome') }}</th>
                    <th>{{ __('CPF/CNPJ') }}</th>
                    <th>{{ __('Cidade') }}</th>
                    <th>{{ __('Lançamentos') }}</th>
                    <th>{{ __('Ações') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($clients as $client)
                    <tr>
                        <td style="font-weight:500">{{ $client->name }}</td>
                        <td style="font-size:12px">
                            @if($client->document)
                                {{ $client->document_type }}: {{ $client->document }}
                            @else
                                —
                            @endif
                        </td>
                        <td style="font-size:12px">{{ $client->city ? $client->city.'/'.$client->state : '—' }}</td>
                        <td>{{ $client->transactions_count }}</td>
                        <td>
                            <div class="action-cell">
                                @if(auth()->user()->canManageFinances())
                                    <i class="ti ti-edit action-icon" onclick="openModal('modal-edit-{{ $client->id }}')" title="{{ __('Editar') }}"></i>
                                    @if($client->transactions_count === 0)
                                        <form method="POST" action="{{ route('clients.destroy', $client) }}" style="display:inline"
                                              onsubmit="return confirm('{{ __('Excluir este cliente?') }}')">
                                            @csrf @method('DELETE')
                                            <button type="submit" style="background:none;border:none;cursor:pointer;padding:0">
                                                <i class="ti ti-trash action-icon" style="color:var(--color-text-danger)" title="{{ __('Excluir') }}"></i>
                                            </button>
                                        </form>
                                    @endif
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" style="color:var(--color-text-tertiary);text-align:center">{{ __('Nenhum cliente cadastrado.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Modal: Novo cliente --}}
<div class="modal-overlay" id="modal-client">
    <div class="modal" style="max-width:640px">
        <div class="modal-header">
            <h3>{{ __('Novo cliente') }}</h3>
            <i class="ti ti-x" style="cursor:pointer;font-size:18px;color:var(--color-text-secondary)" onclick="closeModal('modal-client')"></i>
        </div>
        <form method="POST" action="{{ route('clients.store') }}">
            @csrf
            @include('clients._form', ['prefix' => 'new', 'client' => null])
            <div style="display:flex;justify-content:flex-end;gap:8px;margin-top:16px">
                <button type="button" class="btn" onclick="closeModal('modal-client')">{{ __('Cancelar') }}</button>
                <button type="submit" class="btn btn-primary">{{ __('Salvar') }}</button>
            </div>
        </form>
    </div>
</div>

@foreach($clients as $client)
<div class="modal-overlay" id="modal-edit-{{ $client->id }}">
    <div class="modal" style="max-width:640px">
        <div class="modal-header">
            <h3>{{ __('Editar cliente') }}</h3>
            <i class="ti ti-x" style="cursor:pointer;font-size:18px;color:var(--color-text-secondary)" onclick="closeModal('modal-edit-{{ $client->id }}')"></i>
        </div>
        <form method="POST" action="{{ route('clients.update', $client) }}">
            @csrf @method('PUT')
            @include('clients._form', ['prefix' => 'edit-'.$client->id, 'client' => $client])
            <div style="display:flex;justify-content:flex-end;gap:8px;margin-top:16px">
                <button type="button" class="btn" onclick="closeModal('modal-edit-{{ $client->id }}')">{{ __('Cancelar') }}</button>
                <button type="submit" class="btn btn-primary">{{ __('Salvar alterações') }}</button>
            </div>
        </form>
    </div>
</div>
@endforeach

@push('scripts')
<script>
    if (!window.__clientsCepBound) {
        window.__clientsCepBound = true;
        window.fetchAddressByCep = window.fetchAddressByCep || function (cepInputId, prefix) {
            var cepEl = document.getElementById(cepInputId);
            if (!cepEl) return;
            var cep = cepEl.value.replace(/\D/g, '');
            if (cep.length !== 8) return;
            fetch('https://viacep.com.br/ws/' + cep + '/json/')
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    if (data.erro) return;
                    var street = document.getElementById(prefix + '-street');
                    var neighborhood = document.getElementById(prefix + '-neighborhood');
                    var city = document.getElementById(prefix + '-city');
                    var state = document.getElementById(prefix + '-state');
                    if (street) street.value = data.logradouro || '';
                    if (neighborhood) neighborhood.value = data.bairro || '';
                    if (city) city.value = data.localidade || '';
                    if (state) state.value = data.uf || '';
                });
        };
    }
</script>
@endpush
@endsection
