@extends('layouts.app')
@section('title', 'Categorias')

@section('content')
<div class="topbar">
    <span class="topbar-title">{{ __('Categorias financeiras') }}</span>
    @if(auth()->user()->isAdmin())
        <button class="btn btn-primary" onclick="openModal('modal-cat')"><i class="ti ti-plus"></i>{{ __('Nova categoria') }}</button>
    @endif
</div>

<div class="content">
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>{{ __('Nome') }}</th>
                    <th>{{ __('Tipo') }}</th>
                    <th>{{ __('Requer cliente') }}</th>
                    <th>{{ __('Lançamentos') }}</th>
                    <th>{{ __('Total no mês') }}</th>
                    <th>{{ __('Ações') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($categories as $cat)
                    <tr>
                        <td>{{ $cat->name }}</td>
                        <td>
                            <span class="tag {{ $cat->type === 'INCOME' ? 'tag-in' : ($cat->type === 'EXPENSE' ? 'tag-out' : '') }}">
                                @if($cat->type === 'INCOME') {{ __('Entrada') }}
                                @elseif($cat->type === 'EXPENSE') {{ __('Saída') }}
                                @else {{ __('Ambos') }}
                                @endif
                            </span>
                        </td>
                        <td>{{ $cat->requires_client ? __('Sim') : __('Não') }}</td>
                        <td>{{ $cat->transactions_count }}</td>
                        <td style="color:{{ $cat->type === 'INCOME' ? 'var(--color-text-success)' : 'var(--color-text-danger)' }}">
                            {{ money($cat->monthly_total) }}
                        </td>
                        <td>
                            @if(auth()->user()->isAdmin())
                                <i class="ti ti-edit action-icon" onclick="openModal('modal-edit-{{ $cat->id }}')" title="{{ __('Editar') }}"></i>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<div class="modal-overlay" id="modal-cat">
    <div class="modal">
        <div class="modal-header">
            <h3>{{ __('Nova categoria') }}</h3>
            <i class="ti ti-x" style="cursor:pointer;font-size:18px;color:var(--color-text-secondary)" onclick="closeModal('modal-cat')"></i>
        </div>
        <form method="POST" action="{{ route('categories.store') }}">
            @csrf
            <div class="form-group">
                <label class="form-label">{{ __('Nome') }}</label>
                <input type="text" name="name" placeholder="{{ __('Ex: Marketing') }}" required>
            </div>
            <div class="form-group">
                <label class="form-label">{{ __('Tipo') }}</label>
                <select name="type" required>
                    <option value="INCOME">{{ __('Entrada') }}</option>
                    <option value="EXPENSE">{{ __('Saída') }}</option>
                    <option value="BOTH">{{ __('Ambos') }}</option>
                </select>
            </div>
            <div class="form-group">
                <label style="display:flex;align-items:center;gap:8px;font-size:14px">
                    <input type="checkbox" name="requires_client" value="1">
                    {{ __('Requer cliente (obrigatório em receitas)') }}
                </label>
            </div>
            <div style="display:flex;justify-content:flex-end;gap:8px;margin-top:16px">
                <button type="button" class="btn" onclick="closeModal('modal-cat')">{{ __('Cancelar') }}</button>
                <button type="submit" class="btn btn-primary">{{ __('Salvar') }}</button>
            </div>
        </form>
    </div>
</div>

@foreach($categories as $cat)
<div class="modal-overlay" id="modal-edit-{{ $cat->id }}">
    <div class="modal">
        <div class="modal-header">
            <h3>{{ __('Editar categoria') }}</h3>
            <i class="ti ti-x" style="cursor:pointer;font-size:18px;color:var(--color-text-secondary)" onclick="closeModal('modal-edit-{{ $cat->id }}')"></i>
        </div>
        <form method="POST" action="{{ route('categories.update', $cat) }}">
            @csrf @method('PUT')
            <div class="form-group">
                <label class="form-label">{{ __('Nome') }}</label>
                <input type="text" name="name" value="{{ $cat->name }}" required>
            </div>
            <div class="form-group">
                <label class="form-label">{{ __('Tipo') }}</label>
                <select name="type" required>
                    <option value="INCOME" {{ $cat->type === 'INCOME' ? 'selected' : '' }}>{{ __('Entrada') }}</option>
                    <option value="EXPENSE" {{ $cat->type === 'EXPENSE' ? 'selected' : '' }}>{{ __('Saída') }}</option>
                    <option value="BOTH" {{ $cat->type === 'BOTH' ? 'selected' : '' }}>{{ __('Ambos') }}</option>
                </select>
            </div>
            <div class="form-group">
                <label style="display:flex;align-items:center;gap:8px;font-size:14px">
                    <input type="checkbox" name="requires_client" value="1" {{ $cat->requires_client ? 'checked' : '' }}>
                    {{ __('Requer cliente (obrigatório em receitas)') }}
                </label>
            </div>
            <div style="display:flex;justify-content:flex-end;gap:8px;margin-top:16px">
                <button type="button" class="btn" onclick="closeModal('modal-edit-{{ $cat->id }}')">{{ __('Cancelar') }}</button>
                <button type="submit" class="btn btn-primary">{{ __('Salvar') }}</button>
            </div>
        </form>
    </div>
</div>
@endforeach
@endsection
