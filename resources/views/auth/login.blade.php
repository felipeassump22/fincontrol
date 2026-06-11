<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — FinControl</title>
    <meta name="description" content="Acesse o sistema de gestão financeira FinControl">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/dist/tabler-icons.min.css">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
    <div style="position:absolute;top:20px;right:20px;z-index:100;background:var(--color-background-secondary);border-radius:20px;padding:4px;display:flex;gap:4px;box-shadow:0 2px 10px rgba(0,0,0,0.05);border:0.5px solid var(--color-border-tertiary);">
        <a href="{{ route('lang.switch', 'pt_BR') }}" {!! app()->getLocale() == 'pt_BR' ? 'style="padding:4px 12px;font-size:11px;font-weight:600;text-decoration:none;border-radius:16px;background:var(--color-background-primary);color:var(--color-text-primary);box-shadow:0 1px 3px rgba(0,0,0,0.1);transition:0.2s;"' : 'style="padding:4px 12px;font-size:11px;font-weight:600;text-decoration:none;border-radius:16px;background:transparent;color:var(--color-text-tertiary);transition:0.2s;"' !!}>PT</a>
        <a href="{{ route('lang.switch', 'en') }}" {!! app()->getLocale() == 'en' ? 'style="padding:4px 12px;font-size:11px;font-weight:600;text-decoration:none;border-radius:16px;background:var(--color-background-primary);color:var(--color-text-primary);box-shadow:0 1px 3px rgba(0,0,0,0.1);transition:0.2s;"' : 'style="padding:4px 12px;font-size:11px;font-weight:600;text-decoration:none;border-radius:16px;background:transparent;color:var(--color-text-tertiary);transition:0.2s;"' !!}>EN</a>
    </div>
    <div class="login-wrap">
        <div class="login-box">
            <div class="login-logo">
                <img src="{{ asset('logo.svg') }}" alt="FinControl Logo" style="max-height: 80px; margin-bottom: 16px; object-fit: contain;">
                <p>{{ __('Gestão financeira empresarial') }}</p>
            </div>
            <div class="card">
                <form method="POST" action="{{ route('login') }}">
                    @csrf
                    <div class="form-group">
                        <label class="form-label" for="email">{{ __('E-mail') }}</label>
                        <input type="email" id="email" name="email" placeholder="seu@email.com.br"
                               value="{{ old('email') }}" required autofocus>
                        @error('email')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="password">{{ __('Senha') }}</label>
                        <input type="password" id="password" name="password" placeholder="••••••••" required>
                        @error('password')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>
                    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px">
                        <label style="font-size:12px;display:flex;align-items:center;gap:6px;cursor:pointer">
                            <input type="checkbox" name="remember" style="width:auto" {{ old('remember') ? 'checked' : '' }}>
                            {{ __('Lembrar-me') }}
                        </label>
                    </div>
                    <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center">
                        <i class="ti ti-login"></i>{{ __('Entrar') }}
                    </button>
                </form>
            </div>

        </div>
    </div>
</body>
</html>
