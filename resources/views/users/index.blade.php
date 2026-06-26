@extends('layouts.app')
@section('title', 'Usuários')

@section('content')
<div class="topbar">
    <span class="topbar-title">{{ __('Usuários') }}</span>
    <button class="btn btn-primary" onclick="openModal('modal-user')"><i class="ti ti-plus"></i>{{ __('Novo usuário') }}</button>
</div>

<div class="content">
    <div class="table-wrap">
        <table>
            <thead>
                <tr><th>{{ __('Usuário') }}</th><th>{{ __('E-mail') }}</th><th>{{ __('Perfil') }}</th><th>{{ __('Último acesso') }}</th><th>{{ __('Status') }}</th><th>{{ __('Ações') }}</th></tr>
            </thead>
            <tbody>
                @foreach($users as $user)
                    <tr>
                        <td>
                            <div style="display:flex;align-items:center;gap:8px">
                                <div class="avatar" style="{{ $user->isAdmin() ? '' : 'background:var(--color-background-success);color:var(--color-text-success)' }}">
                                    {{ $user->initials() }}
                                </div>
                                {{ $user->username }}
                            </div>
                        </td>
                        <td style="font-size:12px">{{ $user->email }}</td>
                        <td>
                            <span class="badge {{ $user->isAdmin() ? 'badge-danger' : ($user->isFinancial() ? 'badge-warning' : 'badge-info') }}">
                                {{ $user->role->name }}
                            </span>
                        </td>
                        <td style="font-size:12px">{{ $user->last_login_at ? $user->last_login_at->format('d/m/Y H:i') : '—' }}</td>
                        <td>
                            <span class="badge {{ $user->is_active ? 'badge-success' : 'badge-warning' }}">
                                {{ $user->is_active ? __('Ativo') : __('Inativo') }}
                            </span>
                        </td>
                        <td><i class="ti ti-edit action-icon" title="Editar"></i></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

{{-- Modal: Novo usuário --}}
<div class="modal-overlay" id="modal-user">
    <div class="modal">
        <div class="modal-header">
            <h3>{{ __('Novo usuário') }}</h3>
            <i class="ti ti-x" style="cursor:pointer;font-size:18px;color:var(--color-text-secondary)" onclick="closeModal('modal-user')"></i>
        </div>
        <form method="POST" action="{{ route('users.store') }}">
            @csrf
            <div class="form-group">
                <label class="form-label">{{ __('Nome completo') }}</label>
                <input type="text" name="username" placeholder="{{ __('Ex: João Silva') }}" autocomplete="name" required>
            </div>
            <div class="form-group">
                <label class="form-label">{{ __('E-mail') }}</label>
                <input type="email" name="email" placeholder="joao@empresa.com.br" autocomplete="email" required>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">{{ __('Senha') }}</label>
                    <input type="password" name="password" placeholder="••••••" autocomplete="new-password" required>
                </div>
                <div class="form-group">
                    <label class="form-label">{{ __('Confirmar senha') }}</label>
                    <input type="password" name="password_confirmation" placeholder="••••••" autocomplete="new-password" required>
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">{{ __('Perfil') }}</label>
                <select name="role_id" required>
                    @foreach($roles as $role)
                        <option value="{{ $role->id }}">{{ $role->name }} — {{ $role->description }}</option>
                    @endforeach
                </select>
            </div>
            <div style="display:flex;justify-content:flex-end;gap:8px;margin-top:16px">
                <button type="button" class="btn" onclick="closeModal('modal-user')">{{ __('Cancelar') }}</button>
                <button type="submit" class="btn btn-primary">{{ __('Salvar') }}</button>
            </div>
        </form>
    </div>
</div>
@endsection
