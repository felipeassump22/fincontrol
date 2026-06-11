<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script>
        const savedTheme = localStorage.getItem('theme') || 'light';
        document.documentElement.setAttribute('data-theme', savedTheme);
    </script>
    <title>@yield('title', 'FinControl') — Gestão Financeira</title>
    <meta name="description" content="Sistema de gestão financeira empresarial">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/dist/tabler-icons.min.css">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}?v=1.1.1">
    <meta name="view-transition" content="same-origin">
    <script type="module">
        import * as Turbo from 'https://cdn.jsdelivr.net/npm/@hotwired/turbo@8.0.4/dist/turbo.es2017-esm.js';
        window.Turbo = Turbo;
    </script>
    <style>
        .turbo-progress-bar {
            height: 3px;
            background-color: var(--color-background-info);
        }

        /* Fallback nativo do Chrome para caso o Turbo falhe */
        @view-transition {
            navigation: auto;
        }
    </style>
    <!-- Script Anti-Flash Branco para Temas -->
    <script>
        (function() {
            try {
                var theme = localStorage.getItem('theme') || 'system';
                if (theme === 'system') {
                    theme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
                }
                document.documentElement.setAttribute('data-theme', theme);
            } catch (e) {}
        })();
    </script>
    @stack('styles')
</head>

<body>
    <div class="app" id="app">

        {{-- SIDEBAR --}}
        <div class="sidebar" id="sidebar">
            <div class="sidebar-logo" style="padding: 10px 0;">
                <img src="{{ asset('logo.jpg') }}" alt="FinControl Logo" style="max-height: 40px; object-fit: contain;">
            </div>
            <div class="sidebar-inner">
                <div class="nav-section">
                    <div class="nav-label">{{ __('Principal') }}</div>
                    <a href="{{ route('dashboard') }}" class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                        <i class="ti ti-layout-dashboard"></i>{{ __('Dashboard') }}
                    </a>
                    <a href="{{ route('transactions.index') }}" class="nav-item {{ request()->routeIs('transactions.*') ? 'active' : '' }}">
                        <i class="ti ti-list"></i>{{ __('Lançamentos') }}
                    </a>
                    <a href="{{ route('bank-accounts.index') }}" class="nav-item {{ request()->routeIs('bank-accounts.*') ? 'active' : '' }}">
                        <i class="ti ti-wallet"></i>{{ __('Contas bancárias') }}
                    </a>
                    <a href="{{ route('categories.index') }}" class="nav-item {{ request()->routeIs('categories.*') ? 'active' : '' }}">
                        <i class="ti ti-tag"></i>{{ __('Categorias') }}
                    </a>
                    <a href="{{ route('credit-cards.index') }}" class="nav-item {{ request()->routeIs('credit-cards.*') ? 'active' : '' }}">
                        <i class="ti ti-credit-card"></i>{{ __('Cartões de crédito') }}
                    </a>
                    <a href="{{ route('investments.index') }}" class="nav-item {{ request()->routeIs('investments.*') ? 'active' : '' }}">
                        <i class="ti ti-chart-line"></i>{{ __('Investimentos') }}
                    </a>
                </div>
                <div class="nav-section">
                    <div class="nav-label">{{ __('Cadastros') }}</div>
                    <a href="{{ route('clients.index') }}" class="nav-item {{ request()->routeIs('clients.*') ? 'active' : '' }}">
                        <i class="ti ti-briefcase"></i>{{ __('Clientes') }}
                    </a>
                    <a href="{{ route('invoices.index') }}" class="nav-item {{ request()->routeIs('invoices.*') ? 'active' : '' }}">
                        <i class="ti ti-receipt-2"></i>{{ __('Parcelamentos') }}
                    </a>
                    <a href="{{ route('recurring-expenses.index') }}" class="nav-item {{ request()->routeIs('recurring-expenses.*') ? 'active' : '' }}">
                        <i class="ti ti-repeat"></i>{{ __('Despesas fixas') }}
                    </a>
                </div>
                <div class="nav-section">
                    <div class="nav-label">{{ __('Relatórios') }}</div>
                    <a href="{{ route('reports.index') }}" class="nav-item {{ request()->routeIs('reports.index') ? 'active' : '' }}">
                        <i class="ti ti-chart-bar"></i>{{ __('Relatório mensal') }}
                    </a>
                    <a href="{{ route('reports.cash-flow') }}" class="nav-item {{ request()->routeIs('reports.cash-flow') ? 'active' : '' }}">
                        <i class="ti ti-trending-up"></i>{{ __('Fluxo de caixa') }}
                    </a>
                    <a href="{{ route('audit.index') }}" class="nav-item {{ request()->routeIs('audit.*') ? 'active' : '' }}">
                        <i class="ti ti-history"></i>{{ __('Auditoria') }}
                    </a>
                </div>
                <div class="nav-section">
                    <div class="nav-label">{{ __('Configurações') }}</div>
                    <a href="{{ route('settings.index') }}" class="nav-item {{ request()->routeIs('settings.*') ? 'active' : '' }}">
                        <i class="ti ti-settings"></i>{{ __('Configurações') }}
                    </a>
                    @if(auth()->user()->isAdmin())
                    <a href="{{ route('users.index') }}" class="nav-item {{ request()->routeIs('users.*') ? 'active' : '' }}">
                        <i class="ti ti-users"></i>{{ __('Usuários') }}
                    </a>
                    @endif
                </div>
            </div>

            <div class="user-pill">
                <div class="avatar">{{ auth()->user()->initials() }}</div>
                <div style="flex:1">
                    <div style="font-size:12px;font-weight:500">{{ auth()->user()->username }}</div>
                    <div style="font-size:11px;color:var(--color-text-tertiary)">{{ auth()->user()->role->name }}</div>
                </div>
                <button id="theme-toggle" style="background:none;border:none;cursor:pointer;padding:0;margin-right:8px" title="Alternar tema">
                    <i class="ti ti-moon" id="theme-icon" style="font-size:16px;color:var(--color-text-tertiary)"></i>
                </button>
                <form action="{{ route('logout') }}" method="POST" style="display:inline">
                    @csrf
                    <button type="submit" style="background:none;border:none;cursor:pointer;padding:0">
                        <i class="ti ti-logout" style="font-size:16px;color:var(--color-text-tertiary)" title="{{ __('Sair') }}"></i>
                    </button>
                </form>
            </div>
        </div>

        {{-- MAIN CONTENT --}}
        <div class="main">
            @yield('content')
        </div>

    </div>

    {{-- Toast notifications --}}
    <div class="toast" id="toast">
        <i class="ti ti-circle-check"></i>
        <span id="toast-msg"></span>
    </div>

    <script>
        document.addEventListener('turbo:load', () => {
            // Theme Toggle Logic
            const toggleBtn = document.getElementById('theme-toggle');
            const themeIcon = document.getElementById('theme-icon');
            const html = document.documentElement;

            const updateIcon = (theme) => {
                if (theme === 'amoled') {
                    themeIcon.classList.remove('ti-moon');
                    themeIcon.classList.add('ti-sun');
                } else {
                    themeIcon.classList.remove('ti-sun');
                    themeIcon.classList.add('ti-moon');
                }
            };

            if (toggleBtn && themeIcon) {
                updateIcon(html.getAttribute('data-theme'));
                toggleBtn.addEventListener('click', () => {
                    const currentTheme = html.getAttribute('data-theme');
                    const newTheme = currentTheme === 'amoled' ? 'light' : 'amoled';

                    // Ativar transição suave
                    html.setAttribute('data-theme-transitioning', '');
                    html.setAttribute('data-theme', newTheme);
                    localStorage.setItem('theme', newTheme);
                    updateIcon(newTheme);

                    // Remover a flag de transição depois que terminar
                    setTimeout(() => html.removeAttribute('data-theme-transitioning'), 600);
                });
            }
        });

        const toast = document.getElementById('toast');
        if (toast) {
            setTimeout(() => toast.classList.add('show'), 100);
            setTimeout(() => {
                toast.classList.remove('show');
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        }
    </script>

    <script>
        // ─── Toast ────────────────────────────────────
        let toastTimer;

        function showToast(msg, type = 'success') {
            const toast = document.getElementById('toast');
            document.getElementById('toast-msg').textContent = msg;
            toast.className = 'toast' + (type === 'error' ? ' toast-error' : '');
            toast.classList.add('show');
            clearTimeout(toastTimer);
            toastTimer = setTimeout(() => toast.classList.remove('show'), 3000);
        }

        // ─── Modal ────────────────────────────────────
        function openModal(id) {
            document.getElementById(id).classList.add('open');
        }

        function closeModal(id) {
            document.getElementById(id).classList.remove('open');
        }

        // Fechar modal com ESC
        document.addEventListener('keydown', e => {
            if (e.key === 'Escape') {
                document.querySelectorAll('.modal-overlay.open').forEach(m => m.classList.remove('open'));
            }
        });

        // Fechar modal clicando fora
        document.querySelectorAll('.modal-overlay').forEach(overlay => {
            overlay.addEventListener('click', e => {
                if (e.target === overlay) overlay.classList.remove('open');
            });
        });
    </script>

    {{-- Flash Messages Invisíveis (Para o JS ler sem gerar erro no VS Code) --}}
    @if(session('success'))
    <div id="flash-success" data-message="{{ session('success') }}" style="display:none;"></div>
    @endif
    @if(session('error'))
    <div id="flash-error" data-message="{{ session('error') }}" style="display:none;"></div>
    @endif

    <script>
        // ─── Flash Messages ────────────────────────────
        const flashSuccess = document.getElementById('flash-success');
        if (flashSuccess) {
            showToast(flashSuccess.getAttribute('data-message'));
        }

        const flashError = document.getElementById('flash-error');
        if (flashError) {
            showToast(flashError.getAttribute('data-message'), 'error');
        }
    </script>

    {{-- Botão Menu Mobile --}}
    <button class="mobile-menu-btn" id="mobile-menu-btn" onclick="toggleMobileMenu()">
        <i class="ti ti-menu-2" style="font-size:24px;"></i>
    </button>

    <script>
        function toggleMobileMenu() {
            document.querySelector('.sidebar').classList.toggle('open');
        }

        // Fechar menu ao clicar fora dele na versão mobile
        document.addEventListener('click', function(e) {
            const sidebar = document.querySelector('.sidebar');
            const btn = document.getElementById('mobile-menu-btn');
            if (sidebar && sidebar.classList.contains('open') && !sidebar.contains(e.target) && !btn.contains(e.target)) {
                sidebar.classList.remove('open');
            }
        });
    </script>

    @stack('scripts')

    <script>
        // ─── Engine de Temas ───────────────────────────
        function setTheme(mode) {
            localStorage.setItem('theme', mode);
            let activeTheme = mode;
            if (mode === 'system') {
                activeTheme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
            }
            document.documentElement.setAttribute('data-theme', activeTheme);
            updateThemeUI(mode);
        }

        function updateThemeUI(mode) {
            document.querySelectorAll('.theme-btn').forEach(btn => {
                btn.style.background = 'transparent';
                btn.style.color = 'var(--color-text-tertiary)';
                btn.style.boxShadow = 'none';
            });
            const activeBtn = document.querySelector(`.theme-btn[data-mode="${mode}"]`);
            if (activeBtn) {
                activeBtn.style.background = 'var(--color-background-primary)';
                activeBtn.style.color = 'var(--color-text-primary)';
                activeBtn.style.boxShadow = '0 1px 3px rgba(0,0,0,0.1)';
            }
        }

        // Inicializa UI
        document.addEventListener("turbo:load", function() {
            const saved = localStorage.getItem('theme') || 'system';
            updateThemeUI(saved);
        });

        // Escutar mudanças no SO se estiver no modo Sistema
        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', e => {
            if (localStorage.getItem('theme') === 'system' || !localStorage.getItem('theme')) {
                document.documentElement.setAttribute('data-theme', e.matches ? 'dark' : 'light');
            }
        });

        // UI na primeira carga (sem turbo ativado)
        updateThemeUI(localStorage.getItem('theme') || 'system');
    </script>
</body>

</html>