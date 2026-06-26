<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @auth
    <meta name="fincontrol-currency" content="{{ user_currency() }}" data-turbo-track="reload">
    <meta name="fincontrol-chart-locale" content="{{ chart_locale() }}" data-turbo-track="reload">
    @endauth
    <script data-turbo-eval="false">
        window.getFincontrolCurrency = function () {
            var meta = document.querySelector('meta[name="fincontrol-currency"]');
            return meta && meta.content ? meta.content : 'BRL';
        };
        window.getFincontrolChartLocale = function () {
            var meta = document.querySelector('meta[name="fincontrol-chart-locale"]');
            return meta && meta.content ? meta.content : 'pt-BR';
        };
        window.formatChartCurrency = function (value, compact) {
            var options = {
                style: 'currency',
                currency: window.getFincontrolCurrency(),
            };
            if (compact) {
                options.notation = 'compact';
            }
            return new Intl.NumberFormat(window.getFincontrolChartLocale(), options).format(value);
        };
    </script>
    <script>
        (function () {
            var savedTheme = localStorage.getItem('theme') || 'light';
            document.documentElement.setAttribute('data-theme', savedTheme);
        })();
    </script>
    <title>@yield('title', 'FinControl') — Gestão Financeira</title>
    <meta name="description" content="Sistema de gestão financeira empresarial">
    <link rel="icon" type="image/png" href="{{ asset('logo.png') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/dist/tabler-icons.min.css">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}?v=1.1.1">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
        
        /* Transição premium e fluída para mudança de tema */
        * {
            transition: background-color 0.35s ease, color 0.35s ease, border-color 0.35s ease, box-shadow 0.35s ease;
        }

        .icon-btn {
            background: none;
            border: none;
            cursor: pointer;
            padding: 6px;
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s ease;
            color: var(--color-text-tertiary);
            outline: none;
        }
        .icon-btn i {
            font-size: 18px;
            transition: transform 0.2s cubic-bezier(0.34, 1.56, 0.64, 1);
        }
        .icon-btn:hover {
            background-color: var(--color-surface-hover);
            color: var(--color-text-primary);
        }
        .icon-btn:hover i {
            transform: scale(1.15) rotate(8deg);
        }
        .icon-btn:active i {
            transform: scale(0.9);
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
            <div class="sidebar-logo" style="padding: 10px 0; justify-content: center;">
                <img src="{{ asset('logo.png') }}?v={{ time() }}" alt="FinControl Logo" style="max-height: 40px; object-fit: contain;">
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
                <div class="divider" style="margin: 4px 16px; opacity: 0.5;"></div>
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
                <div class="divider" style="margin: 4px 16px; opacity: 0.5;"></div>
                <div class="nav-section">
                    <div class="nav-label">{{ __('Relatórios') }}</div>
                    <a href="{{ route('reports.index') }}" class="nav-item {{ request()->routeIs('reports.index') ? 'active' : '' }}">
                        <i class="ti ti-chart-bar"></i>{{ __('Relatório mensal') }}
                    </a>
                    <a href="{{ route('reports.cash-flow') }}" class="nav-item {{ request()->routeIs('reports.cash-flow') ? 'active' : '' }}">
                        <i class="ti ti-trending-up"></i>{{ __('Fluxo de caixa') }}
                    </a>
                    <a href="{{ route('reports.accounting') }}" class="nav-item {{ request()->routeIs('reports.accounting') ? 'active' : '' }}">
                        <i class="ti ti-file-text"></i>{{ __('Relatório contábil') }}
                    </a>
                    <a href="{{ route('reports.client-payments') }}" class="nav-item {{ request()->routeIs('reports.client-payments') ? 'active' : '' }}">
                        <i class="ti ti-users"></i>{{ __('Pagamentos por cliente') }}
                    </a>
                    @if(auth()->user()->isAdmin())
                    <a href="{{ route('audit.index') }}" class="nav-item {{ request()->routeIs('audit.*') ? 'active' : '' }}">
                        <i class="ti ti-history"></i>{{ __('Auditoria') }}
                    </a>
                    @endif
                </div>
                <div class="divider" style="margin: 4px 16px; opacity: 0.5;"></div>
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
                <button id="theme-toggle" class="icon-btn" style="margin-right:0px" title="Alternar tema">
                    <i class="ti ti-moon" id="theme-icon"></i>
                </button>
                <form action="{{ route('logout') }}" method="POST" style="display:inline">
                    @csrf
                    <button type="submit" class="icon-btn">
                        <i class="ti ti-logout" title="{{ __('Sair') }}"></i>
                    </button>
                </form>
            </div>
        </div>

        {{-- MAIN CONTENT --}}
        <div class="main">
            @yield('content')
        </div>

    </div>

    {{-- Toast notifications (persiste entre navegações Turbo) --}}
    <div class="toast" id="toast" data-turbo-permanent>
        <i class="ti ti-circle-check"></i>
        <span id="toast-msg"></span>
    </div>

    {{-- Flash Messages Invisíveis --}}
    @if(session('success'))
    <div id="flash-success" data-message="{{ session('success') }}" style="display:none;"></div>
    @endif
    @if(session('error'))
    <div id="flash-error" data-message="{{ session('error') }}" style="display:none;"></div>
    @endif

    {{-- Botão Menu Mobile --}}
    <button class="mobile-menu-btn" id="mobile-menu-btn" data-turbo-permanent>
        <i class="ti ti-menu-2" style="font-size:24px;"></i>
    </button>

    @stack('scripts')

    <script data-turbo-eval="false">
        (function () {
            if (window.__fincontrolAppBound) {
                return;
            }
            window.__fincontrolAppBound = true;

            window.__fincontrolToastTimer = null;

            window.showToast = function (msg, type) {
                type = type || 'success';
                var toast = document.getElementById('toast');
                var toastMsg = document.getElementById('toast-msg');
                if (!toast || !toastMsg) {
                    return;
                }
                toastMsg.textContent = msg;
                toast.className = 'toast' + (type === 'error' ? ' toast-error' : '');
                toast.classList.add('show');
                clearTimeout(window.__fincontrolToastTimer);
                window.__fincontrolToastTimer = setTimeout(function () {
                    toast.classList.remove('show');
                }, 3000);
            };

            window.openModal = function (id) {
                var modal = document.getElementById(id);
                if (modal) {
                    modal.classList.add('open');
                }
            };

            window.closeModal = function (id) {
                var modal = document.getElementById(id);
                if (modal) {
                    modal.classList.remove('open');
                }
            };

            window.toggleMobileMenu = function () {
                var sidebar = document.querySelector('.sidebar');
                if (sidebar) {
                    sidebar.classList.toggle('open');
                }
            };

            window.setTheme = function (mode) {
                localStorage.setItem('theme', mode);
                var activeTheme = mode;
                if (mode === 'system') {
                    activeTheme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
                }
                document.documentElement.setAttribute('data-theme', activeTheme);
                window.updateThemeUI(mode);
            };

            window.updateThemeUI = function (mode) {
                document.querySelectorAll('.theme-btn').forEach(function (btn) {
                    btn.style.background = 'transparent';
                    btn.style.color = 'var(--color-text-tertiary)';
                    btn.style.boxShadow = 'none';
                });
                var activeBtn = document.querySelector('.theme-btn[data-mode="' + mode + '"]');
                if (activeBtn) {
                    activeBtn.style.background = 'var(--color-background-primary)';
                    activeBtn.style.color = 'var(--color-text-primary)';
                    activeBtn.style.boxShadow = '0 1px 3px rgba(0,0,0,0.1)';
                }
            };

            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape') {
                    document.querySelectorAll('.modal-overlay.open').forEach(function (m) {
                        m.classList.remove('open');
                    });
                }
            });

            document.addEventListener('click', function (e) {
                var sidebar = document.querySelector('.sidebar');
                var btn = document.getElementById('mobile-menu-btn');
                if (sidebar && sidebar.classList.contains('open') && btn &&
                    !sidebar.contains(e.target) && !btn.contains(e.target)) {
                    sidebar.classList.remove('open');
                }
            });

            window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', function (e) {
                if (localStorage.getItem('theme') === 'system' || !localStorage.getItem('theme')) {
                    document.documentElement.setAttribute('data-theme', e.matches ? 'dark' : 'light');
                }
            });

            document.cookie = 'client_tz=' + Intl.DateTimeFormat().resolvedOptions().timeZone + '; path=/; max-age=31536000';

            if (!window.__fincontrolTurboLoadBound) {
                window.__fincontrolTurboLoadBound = true;
                document.addEventListener('turbo:load', function () {
                    var toggleBtn = document.getElementById('theme-toggle');
                    var themeIcon = document.getElementById('theme-icon');
                    var html = document.documentElement;
                    var mobileBtn = document.getElementById('mobile-menu-btn');

                    if (mobileBtn && !mobileBtn.dataset.bound) {
                        mobileBtn.dataset.bound = '1';
                        mobileBtn.addEventListener('click', window.toggleMobileMenu);
                    }

                    var updateIcon = function (theme) {
                        if (!themeIcon) {
                            return;
                        }
                        if (theme === 'amoled') {
                            themeIcon.classList.remove('ti-moon');
                            themeIcon.classList.add('ti-sun');
                        } else {
                            themeIcon.classList.remove('ti-sun');
                            themeIcon.classList.add('ti-moon');
                        }
                    };

                    if (toggleBtn && !toggleBtn.dataset.bound) {
                        toggleBtn.dataset.bound = '1';
                        updateIcon(html.getAttribute('data-theme'));
                        toggleBtn.addEventListener('click', function () {
                            var currentTheme = html.getAttribute('data-theme');
                            var newTheme = currentTheme === 'amoled' ? 'light' : 'amoled';
                            html.setAttribute('data-theme-transitioning', '');
                            html.setAttribute('data-theme', newTheme);
                            localStorage.setItem('theme', newTheme);
                            updateIcon(newTheme);
                            setTimeout(function () {
                                html.removeAttribute('data-theme-transitioning');
                            }, 600);
                        });
                    }

                    document.querySelectorAll('.modal-overlay').forEach(function (overlay) {
                        if (overlay.dataset.bound) {
                            return;
                        }
                        overlay.dataset.bound = '1';
                        overlay.addEventListener('click', function (e) {
                            if (e.target === overlay) {
                                overlay.classList.remove('open');
                            }
                        });
                    });

                    var flashSuccess = document.getElementById('flash-success');
                    if (flashSuccess) {
                        window.showToast(flashSuccess.getAttribute('data-message'));
                        flashSuccess.remove();
                    }

                    var flashError = document.getElementById('flash-error');
                    if (flashError) {
                        window.showToast(flashError.getAttribute('data-message'), 'error');
                        flashError.remove();
                    }

                    window.updateThemeUI(localStorage.getItem('theme') || 'system');

                    if (typeof window.initDashboardPage === 'function') {
                        window.initDashboardPage();
                    }
                });
            }
        })();
    </script>
</body>
</html> 