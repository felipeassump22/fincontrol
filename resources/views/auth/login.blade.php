<!DOCTYPE html>
<html data-theme="dark" lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — FinControl</title>
    <meta name="description" content="Acesse o sistema de gestão financeira FinControl">
    <link rel="icon" type="image/png" href="{{ asset('logo.png') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/dist/tabler-icons.min.css">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <style>
        /* Design Premium Exclusivo do Login */
        body {
            background: linear-gradient(-45deg, #0a0e17, #131b2f, #0d1222, #080b12);
            background-size: 400% 400%;
            animation: gradientBG 15s ease infinite;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            color: #ffffff;
        }

        @keyframes gradientBG {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        /* Efeito Glassmorphism no Container */
        .login-wrap {
            width: 100%;
            display: flex;
            justify-content: center;
            padding: 20px;
        }

        .login-box {
            width: 100%;
            max-width: 420px;
        }

        .card {
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.05);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            border-radius: 24px;
            padding: 40px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 35px 60px -15px rgba(0, 0, 0, 0.6);
        }

        /* Tipografia refinada */
        .login-logo p {
            color: #94a3b8;
            font-size: 14px;
            letter-spacing: 0.5px;
            margin-top: -8px;
            margin-bottom: 30px;
        }

        /* Inputs Premium */
        .form-label {
            color: #cbd5e1;
            font-weight: 500;
            font-size: 13px;
        }

        .form-group input {
            background: rgba(0, 0, 0, 0.2) !important;
            border: 1px solid rgba(255, 255, 255, 0.1) !important;
            color: #fff !important;
            border-radius: 12px !important;
            padding: 14px 16px !important;
            font-size: 14px !important;
            transition: all 0.3s ease !important;
        }

        .form-group input:focus {
            background: rgba(0, 0, 0, 0.4) !important;
            border-color: #3b82f6 !important;
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.15) !important;
            outline: none !important;
        }

        /* Botão Mágico */
        .btn-primary {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%) !important;
            border: none !important;
            border-radius: 12px !important;
            padding: 14px !important;
            font-weight: 600 !important;
            font-size: 15px !important;
            letter-spacing: 0.5px;
            box-shadow: 0 10px 20px -10px rgba(37, 99, 235, 0.5) !important;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
            color: white !important;
        }

        .btn-primary:hover {
            transform: translateY(-2px) scale(1.02);
            box-shadow: 0 15px 25px -10px rgba(37, 99, 235, 0.6) !important;
            background: linear-gradient(135deg, #60a5fa 0%, #3b82f6 100%) !important;
        }

        .btn-primary:active {
            transform: translateY(1px) scale(0.98);
        }

        /* Language Toggle Premium */
        .lang-toggle {
            position: absolute;
            top: 24px;
            right: 24px;
            z-index: 100;
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 4px;
            display: flex;
            gap: 4px;
            border: 1px solid rgba(255, 255, 255, 0.08);
        }
    </style>
</head>
<body>
    <div class="lang-toggle">
        <a href="{{ route('lang.switch', 'pt_BR') }}" {!! app()->getLocale() == 'pt_BR' ? 'style="padding:6px 14px;font-size:11px;font-weight:700;text-decoration:none;border-radius:16px;background:rgba(255,255,255,0.15);color:#fff;transition:0.3s;"' : 'style="padding:6px 14px;font-size:11px;font-weight:600;text-decoration:none;border-radius:16px;background:transparent;color:#94a3b8;transition:0.3s;"' !!}>PT</a>
        <a href="{{ route('lang.switch', 'en') }}" {!! app()->getLocale() == 'en' ? 'style="padding:6px 14px;font-size:11px;font-weight:700;text-decoration:none;border-radius:16px;background:rgba(255,255,255,0.15);color:#fff;transition:0.3s;"' : 'style="padding:6px 14px;font-size:11px;font-weight:600;text-decoration:none;border-radius:16px;background:transparent;color:#94a3b8;transition:0.3s;"' !!}>EN</a>
    </div>
    <div class="login-wrap">
        <div class="login-box">
            <div class="login-logo" style="text-align: center;">
                <img src="{{ asset('logo.png') }}?v={{ time() }}" alt="FinControl Logo" style="max-height: 110px; margin-bottom: 0px; object-fit: contain;">
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
    <script>
        document.cookie = "client_tz=" + Intl.DateTimeFormat().resolvedOptions().timeZone + "; path=/; max-age=31536000";
    </script>
</body>
</html>
