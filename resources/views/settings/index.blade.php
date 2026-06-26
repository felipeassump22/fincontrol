@extends('layouts.app')
@section('title', __('Configurações'))

@section('content')
<div class="topbar">
    <span class="topbar-title">{{ __('Central de Configurações') }}</span>
</div>

<div class="content">
    @if(session('success'))
        <div class="alert alert-success" style="background:var(--color-background-success);color:var(--color-text-success);padding:12px;border-radius:var(--border-radius-md);margin-bottom:20px;display:flex;align-items:center;gap:8px">
            <i class="ti ti-check"></i> {{ session('success') }}
        </div>
    @endif

    <div class="grid-2">
        {{-- Card: Aparência (Tema) --}}
        <div class="card">
            <h3 style="display:flex;align-items:center;gap:8px;margin-bottom:16px;color:var(--color-text-primary)">
                <i class="ti ti-palette" style="color:var(--color-text-info)"></i> {{ __('Aparência do Sistema') }}
            </h3>
            <p style="color:var(--color-text-secondary);font-size:13px;margin-bottom:16px;">
                {{ __('Escolha o tema que mais te agrada. O modo Amoled ajuda a economizar bateria em telas OLED.') }}
            </p>
            
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                <button type="button" onclick="setTheme('light')" class="btn" style="justify-content:center"><i class="ti ti-sun"></i> {{ __('Claro') }}</button>
                <button type="button" onclick="setTheme('dark')" class="btn" style="justify-content:center"><i class="ti ti-moon"></i> {{ __('Escuro') }}</button>
                <button type="button" onclick="setTheme('amoled')" class="btn" style="justify-content:center"><i class="ti ti-device-mobile"></i> {{ __('Amoled') }}</button>
                <button type="button" onclick="setTheme('system')" class="btn" style="justify-content:center"><i class="ti ti-device-desktop"></i> {{ __('Sistema') }}</button>
            </div>
        </div>

        {{-- Card: Moeda --}}
        <div class="card">
            <h3 style="display:flex;align-items:center;gap:8px;margin-bottom:16px;color:var(--color-text-primary)">
                <i class="ti ti-cash" style="color:var(--color-text-success)"></i> {{ __('Moeda Padrão') }}
            </h3>
            <p style="color:var(--color-text-secondary);font-size:13px;margin-bottom:16px;">
                {{ __('Isso alterará os símbolos de moeda em todos os relatórios, cartões e faturas do sistema.') }}
            </p>
            
            <form method="POST" action="{{ route('settings.currency') }}">
                @csrf
                <div class="form-group">
                    <select name="currency" class="form-select" style="width:100%;padding:10px;border-radius:var(--border-radius-md);border:1px solid var(--color-border-primary);background:var(--color-background-primary);color:var(--color-text-primary);margin-bottom:12px;">
                        <option value="BRL" {{ auth()->user()->currency == 'BRL' ? 'selected' : '' }}>Real Brasileiro (R$)</option>
                        <option value="USD" {{ auth()->user()->currency == 'USD' ? 'selected' : '' }}>Dólar Americano ($)</option>
                        <option value="EUR" {{ auth()->user()->currency == 'EUR' ? 'selected' : '' }}>Euro (€)</option>
                        <option value="GBP" {{ auth()->user()->currency == 'GBP' ? 'selected' : '' }}>Libra Esterlina (£)</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center">{{ __('Atualizar Moeda') }}</button>
            </form>
        </div>

        {{-- Card: Idioma --}}
        <div class="card">
            <h3 style="display:flex;align-items:center;gap:8px;margin-bottom:16px;color:var(--color-text-primary)">
                <i class="ti ti-language" style="color:var(--color-text-warning)"></i> {{ __('Idioma') }}
            </h3>
            <p style="color:var(--color-text-secondary);font-size:13px;margin-bottom:16px;">
                {{ __('Selecione o idioma de interface do sistema.') }}
            </p>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                <a href="{{ route('lang.switch', 'pt_BR') }}" class="btn {{ app()->getLocale() == 'pt_BR' ? 'btn-primary' : '' }}" style="justify-content:center;text-decoration:none;">Português (BR)</a>
                <a href="{{ route('lang.switch', 'en') }}" class="btn {{ app()->getLocale() == 'en' ? 'btn-primary' : '' }}" style="justify-content:center;text-decoration:none;">English (US)</a>
            </div>
        </div>

        {{-- Card: Preferências de Notificação (Placeholder) --}}
        <div class="card" style="opacity: 0.7;">
            <h3 style="display:flex;align-items:center;gap:8px;margin-bottom:16px;color:var(--color-text-primary)">
                <i class="ti ti-bell" style="color:var(--color-text-info)"></i> {{ __('Notificações (Em breve)') }}
            </h3>
            <p style="color:var(--color-text-secondary);font-size:13px;margin-bottom:16px;">
                {{ __('Alertas de vencimento de faturas, fechamento de cartão e limites atingidos diretamente no seu e-mail ou Telegram.') }}
            </p>
            <div style="display:flex;align-items:center;gap:8px;margin-bottom:12px;">
                <input type="checkbox" disabled checked> <span style="color:var(--color-text-secondary);font-size:14px;">{{ __('Alertas na plataforma') }}</span>
            </div>
            <div style="display:flex;align-items:center;gap:8px;">
                <input type="checkbox" disabled> <span style="color:var(--color-text-secondary);font-size:14px;">{{ __('Alertas por E-mail') }}</span>
            </div>
        </div>

        {{-- Card: Dados da empresa --}}
        @if(auth()->user()->canManageFinances())
        <div class="card" style="grid-column: 1 / -1;">
            <h3 style="display:flex;align-items:center;gap:8px;margin-bottom:16px;color:var(--color-text-primary)">
                <i class="ti ti-building" style="color:var(--color-text-info)"></i> {{ __('Dados da empresa (PDF)') }}
            </h3>
            <p style="color:var(--color-text-secondary);font-size:13px;margin-bottom:16px;">
                {{ __('Essas informações aparecem no cabeçalho dos relatórios exportados em PDF.') }}
            </p>
            <form method="POST" action="{{ route('settings.company') }}">
                @csrf
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">{{ __('Razão social') }}</label>
                        <input type="text" name="company_name" value="{{ old('company_name', $company->company_name) }}">
                    </div>
                    <div class="form-group">
                        <label class="form-label">{{ __('Nome fantasia') }}</label>
                        <input type="text" name="trade_name" value="{{ old('trade_name', $company->trade_name) }}">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">{{ __('CNPJ/CPF') }}</label>
                        <input type="text" name="document" value="{{ old('document', $company->document) }}">
                    </div>
                    <div class="form-group">
                        <label class="form-label">{{ __('E-mail') }}</label>
                        <input type="email" name="email" value="{{ old('email', $company->email) }}">
                    </div>
                    <div class="form-group">
                        <label class="form-label">{{ __('Telefone') }}</label>
                        <input type="text" name="phone" value="{{ old('phone', $company->phone) }}">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">{{ __('CEP') }}</label>
                        <input type="text" name="zip_code" id="company-zip" value="{{ old('zip_code', $company->zip_code) }}" onblur="fetchAddressByCep('company-zip', 'company')">
                    </div>
                    <div class="form-group">
                        <label class="form-label">{{ __('Rua') }}</label>
                        <input type="text" name="street" id="company-street" value="{{ old('street', $company->street) }}">
                    </div>
                    <div class="form-group">
                        <label class="form-label">{{ __('Número') }}</label>
                        <input type="text" name="number" value="{{ old('number', $company->number) }}">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">{{ __('Complemento') }}</label>
                        <input type="text" name="complement" value="{{ old('complement', $company->complement) }}">
                    </div>
                    <div class="form-group">
                        <label class="form-label">{{ __('Bairro') }}</label>
                        <input type="text" name="neighborhood" id="company-neighborhood" value="{{ old('neighborhood', $company->neighborhood) }}">
                    </div>
                    <div class="form-group">
                        <label class="form-label">{{ __('Cidade') }}</label>
                        <input type="text" name="city" id="company-city" value="{{ old('city', $company->city) }}">
                    </div>
                    <div class="form-group">
                        <label class="form-label">{{ __('UF') }}</label>
                        <input type="text" name="state" id="company-state" maxlength="2" value="{{ old('state', $company->state) }}">
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">{{ __('Salvar dados da empresa') }}</button>
            </form>
        </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
    window.fetchAddressByCep = function (cepInputId, prefix) {
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
</script>
@endpush
@endsection
