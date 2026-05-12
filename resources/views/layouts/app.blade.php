<!doctype html>
<html lang="es" data-bs-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#5c1a2b">
    <title>@yield('title', 'Cuentas')</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="apple-touch-icon" href="{{ asset('favicon.svg') }}">

    {{-- Tipografía elegante --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        // Aplicar tema antes del render para evitar parpadeo
        (function () {
            var t = localStorage.getItem('cuentas-theme');
            if (!t) t = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
            document.documentElement.setAttribute('data-bs-theme', t);
        })();
    </script>

    <style>
        :root {
            --brand:          #6f1d2e;   /* vinotinto principal */
            --brand-strong:   #4a1220;   /* vinotinto oscuro */
            --brand-soft:     #8c2a44;   /* vinotinto medio */
            --brand-glow:     #c44569;   /* vinotinto claro / hover */
            --accent:         #d4af37;   /* dorado, uso muy puntual */
            --info:           #2563eb;   /* azul para "ver" / informativo */
            --info-strong:    #1e40af;
            --positive:       #2a8a5f;
            --negative:       #c0392b;

            --surface:        #ffffff;
            --surface-2:      #faf6f3;
            --surface-3:      #f1ebe7;
            --ink:            #1a0d10;
            --ink-soft:       #5a4b50;
            --border:         #ece2dd;

            --shadow-sm: 0 1px 2px rgba(74,18,32,.05);
            --shadow:    0 8px 24px -10px rgba(74,18,32,.18), 0 2px 6px rgba(0,0,0,.04);
            --shadow-lg: 0 24px 60px -20px rgba(74,18,32,.35);
        }

        [data-bs-theme="dark"] {
            --brand:          #c44569;
            --brand-strong:   #a02d4a;
            --brand-soft:     #d96b89;
            --brand-glow:     #e88aa3;
            --accent:         #e5c869;
            --info:           #60a5fa;
            --info-strong:    #3b82f6;
            --positive:       #4cd297;
            --negative:       #ff6b5a;

            --surface:        #15090d;
            --surface-2:      #1f1014;
            --surface-3:      #2a161c;
            --ink:            #f5ebed;
            --ink-soft:       #c9b3b8;
            --border:         #3a1f27;

            --shadow-sm: 0 1px 2px rgba(0,0,0,.5);
            --shadow:    0 12px 32px -10px rgba(0,0,0,.6), 0 2px 6px rgba(0,0,0,.3);
            --shadow-lg: 0 24px 60px -20px rgba(0,0,0,.8);
        }

        /* Mapear Bootstrap a la paleta */
        :root, [data-bs-theme="light"] {
            --bs-body-bg: var(--surface-2);
            --bs-body-color: var(--ink);
            --bs-primary: var(--brand);
            --bs-primary-rgb: 111, 29, 46;
            --bs-border-color: var(--border);
            --bs-card-bg: var(--surface);
            --bs-secondary-bg: var(--surface-3);
            --bs-tertiary-bg: var(--surface-3);
        }
        [data-bs-theme="dark"] {
            --bs-body-bg: #0a0507;
            --bs-body-color: var(--ink);
            --bs-primary: var(--brand);
            --bs-primary-rgb: 196, 69, 105;
            --bs-border-color: var(--border);
            --bs-card-bg: var(--surface-2);
            --bs-secondary-bg: var(--surface-3);
            --bs-tertiary-bg: var(--surface-3);
            --bs-emphasis-color: var(--ink);
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, system-ui, sans-serif;
            font-feature-settings: "cv02", "cv03", "cv04", "cv11";
            padding-bottom: 6rem;
            background:
                radial-gradient(1200px 600px at 100% -10%, rgba(196,69,105,.08), transparent 60%),
                radial-gradient(900px 500px at -10% 10%, rgba(111,29,46,.06), transparent 60%),
                var(--bs-body-bg);
            background-attachment: fixed;
            color: var(--ink);
            -webkit-font-smoothing: antialiased;
        }

        h1, h2, h3, .display-serif {
            font-family: 'Playfair Display', Georgia, serif;
            font-weight: 700;
            letter-spacing: -.01em;
        }

        /* Navbar elegante */
        .navbar.app-navbar {
            background: var(--surface);
            border-bottom: 1px solid var(--border);
            backdrop-filter: saturate(180%) blur(10px);
        }
        [data-bs-theme="dark"] .navbar.app-navbar {
            background: rgba(21,9,13,.85);
        }
        .navbar-brand.app-brand {
            font-family: 'Playfair Display', serif;
            font-weight: 700;
            letter-spacing: .5px;
            color: var(--brand);
        }
        .navbar-brand.app-brand .brand-mark {
            display: inline-flex;
            width: 32px; height: 32px;
            border-radius: 50%;
            align-items: center; justify-content: center;
            background: linear-gradient(135deg, var(--brand-strong), var(--brand-soft));
            color: #fff;
            margin-right: .5rem;
            box-shadow: 0 2px 8px rgba(111,29,46,.35);
        }
        .navbar-nav { gap: .35rem; }
        .navbar .nav-link {
            color: var(--ink-soft);
            font-weight: 500;
            border-radius: .5rem;
            padding: .4rem .85rem;
            transition: color .15s ease, background .15s ease;
        }
        .navbar .nav-link:hover,
        .navbar .nav-link:focus,
        .navbar .nav-link.active {
            color: var(--brand);
            background: rgba(111,29,46,.06);
        }
        .navbar .nav-link.active {
            font-weight: 600;
            box-shadow: inset 0 -2px 0 var(--brand);
        }
        [data-bs-theme="dark"] .navbar .nav-link:hover,
        [data-bs-theme="dark"] .navbar .nav-link.active { background: rgba(196,69,105,.12); }

        /* Botones */
        .btn-primary,
        .btn-brand {
            background: linear-gradient(135deg, var(--brand-strong), var(--brand));
            border: 0;
            color: #fff;
            font-weight: 600;
            letter-spacing: .2px;
            box-shadow: 0 4px 14px -4px rgba(111,29,46,.5);
            transition: transform .12s ease, box-shadow .15s ease, filter .15s ease;
        }
        .btn-primary:hover,
        .btn-brand:hover {
            color: #fff;
            filter: brightness(1.08);
            box-shadow: 0 6px 18px -4px rgba(111,29,46,.6);
            transform: translateY(-1px);
        }
        .btn-outline-primary {
            --bs-btn-color: var(--brand);
            --bs-btn-border-color: var(--brand);
            --bs-btn-hover-bg: var(--brand);
            --bs-btn-hover-border-color: var(--brand);
            --bs-btn-hover-color: #fff;
            --bs-btn-active-bg: var(--brand);
            --bs-btn-active-border-color: var(--brand);
            --bs-btn-active-color: #fff;
            color: var(--brand);
            border-color: var(--brand);
        }
        .btn-outline-primary:hover {
            background: var(--brand);
            border-color: var(--brand);
            color: #fff;
        }
        .btn-check:checked + .btn-outline-primary,
        .btn-check:active + .btn-outline-primary,
        .btn-outline-primary.active,
        .btn-outline-primary:active {
            background: linear-gradient(135deg, var(--brand-strong), var(--brand)) !important;
            border-color: var(--brand) !important;
            color: #fff !important;
            box-shadow: 0 4px 12px -4px rgba(111,29,46,.5);
        }
        .btn-check:focus-visible + .btn-outline-primary {
            box-shadow: 0 0 0 .2rem rgba(111,29,46,.25);
        }
        .btn-outline-secondary {
            color: var(--ink-soft);
            border-color: var(--border);
        }
        .btn-outline-secondary:hover {
            background: var(--surface-3);
            color: var(--ink);
            border-color: var(--border);
        }
        .btn-light {
            background: var(--surface-3);
            border-color: var(--border);
            color: var(--ink);
        }
        .btn-light:hover { background: var(--border); color: var(--ink); }

        /* Botón informativo (Ver / acción no destructiva) */
        .btn-info {
            background: linear-gradient(135deg, var(--info-strong), var(--info));
            border: 0;
            color: #fff;
            font-weight: 600;
            box-shadow: 0 4px 12px -4px rgba(37,99,235,.45);
            transition: transform .12s ease, filter .15s ease, box-shadow .15s ease;
        }
        .btn-info:hover {
            color: #fff;
            filter: brightness(1.08);
            box-shadow: 0 6px 16px -4px rgba(37,99,235,.6);
            transform: translateY(-1px);
        }

        /* Botón destructivo filled */
        .btn-danger {
            background: linear-gradient(135deg, #a02418, var(--negative));
            border: 0;
            color: #fff;
            font-weight: 600;
            box-shadow: 0 4px 12px -4px rgba(192,57,43,.4);
            transition: transform .12s ease, filter .15s ease, box-shadow .15s ease;
        }
        .btn-danger:hover {
            color: #fff;
            filter: brightness(1.08);
            box-shadow: 0 6px 16px -4px rgba(192,57,43,.55);
            transform: translateY(-1px);
        }

        /* Botón icono cuadrado para acciones en tabla */
        .btn-icon {
            width: 36px; height: 36px;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 10px;
            font-size: 1rem;
        }
        .btn-icon.btn-sm { width: 32px; height: 32px; font-size: .9rem; border-radius: 8px; }

        /* Grupo de acciones siempre en línea y centrado */
        .actions {
            display: inline-flex;
            gap: .4rem;
            align-items: center;
            justify-content: center;
            flex-wrap: nowrap;
        }
        .actions form { margin: 0; display: inline-flex; }
        .table .actions-cell,
        .actions-cell {
            white-space: nowrap;
            text-align: center;
            vertical-align: middle;
        }

        /* Cards */
        .card,
        .card-stat {
            background: var(--bs-card-bg);
            border: 1px solid var(--border);
            border-radius: 16px;
            box-shadow: var(--shadow-sm);
            overflow: hidden;
            transition: box-shadow .2s ease, transform .12s ease, border-color .2s ease;
        }
        .card-stat:hover { box-shadow: var(--shadow); }
        .card-header {
            background: transparent !important;
            border-bottom: 1px solid var(--border);
            font-family: 'Playfair Display', serif;
            font-weight: 700;
            font-size: 1.05rem;
            padding: 1rem 1.25rem;
        }

        /* Tarjetas de estadística destacadas */
        .stat-card {
            position: relative;
            overflow: hidden;
            border-radius: 18px;
            padding: 1.25rem;
            background: var(--bs-card-bg);
            border: 1px solid var(--border);
            box-shadow: var(--shadow-sm);
        }
        .stat-card::before {
            content: "";
            position: absolute; inset: 0 0 auto 0; height: 3px;
            background: linear-gradient(90deg, var(--brand), var(--brand-glow));
            opacity: .9;
        }
        .stat-card .stat-label {
            font-size: .78rem;
            text-transform: uppercase;
            letter-spacing: .12em;
            color: var(--ink-soft);
            font-weight: 600;
        }
        .stat-card .stat-value {
            font-family: 'Playfair Display', serif;
            font-size: 1.7rem;
            font-weight: 700;
            margin-top: .35rem;
        }
        .stat-card.stat-brand::before { background: linear-gradient(90deg, var(--brand-strong), var(--brand-soft)); }
        .stat-card.stat-positive::before { background: linear-gradient(90deg, var(--positive), #5cc99a); }
        .stat-card.stat-negative::before { background: linear-gradient(90deg, var(--negative), #ff7e6c); }
        .stat-card.stat-warning::before { background: linear-gradient(90deg, var(--accent), #f1d273); }

        .balance-positive { color: var(--positive); font-weight: 600; }
        .balance-negative { color: var(--negative); font-weight: 600; }
        .balance-neutral  { color: var(--ink); font-weight: 600; }

        /* Tarjeta de "cuánto le queda" por persona */
        .person-card {
            position: relative;
            padding: 1.25rem;
            border-radius: 16px;
            background: linear-gradient(135deg,
                color-mix(in srgb, var(--person-color) 6%, var(--bs-card-bg)),
                var(--bs-card-bg));
            border: 1px solid var(--border);
            border-left: 4px solid var(--person-color, var(--brand));
            box-shadow: var(--shadow-sm);
            transition: box-shadow .2s ease, transform .12s ease;
        }
        .person-card:hover { box-shadow: var(--shadow); transform: translateY(-1px); }
        .person-card .progress { border-radius: 99px; overflow: hidden; }
        .person-card .badge { font-weight: 600; padding: .35rem .6rem; border-radius: 99px; font-size: .7rem; }

        /* Tablas */
        .table {
            --bs-table-bg: transparent;
            color: var(--ink);
            margin: 0;
        }
        .table > :not(caption) > * > * { border-bottom-color: var(--border); }
        .table thead th {
            font-size: .72rem;
            text-transform: uppercase;
            letter-spacing: .1em;
            color: var(--ink-soft);
            font-weight: 700;
            background: var(--surface-3) !important;
            border-bottom: 1px solid var(--border);
            padding: .9rem .75rem;
            line-height: 1.4;
            vertical-align: middle;
        }
        .table tbody td { padding: .75rem; vertical-align: middle; }
        .table tbody tr:hover { background: rgba(111,29,46,.035); }
        [data-bs-theme="dark"] .table tbody tr:hover { background: rgba(196,69,105,.06); }

        /* Inputs / forms */
        .form-control, .form-select {
            background: var(--surface);
            border: 1px solid var(--border);
            color: var(--ink);
            border-radius: 10px;
            padding: .55rem .75rem;
            transition: border-color .15s ease, box-shadow .15s ease;
        }
        .form-control:focus, .form-select:focus {
            border-color: var(--brand);
            box-shadow: 0 0 0 .2rem rgba(111,29,46,.15);
            background: var(--surface);
            color: var(--ink);
        }
        [data-bs-theme="dark"] .form-control,
        [data-bs-theme="dark"] .form-select { background: var(--surface-3); }
        .form-label {
            font-weight: 600;
            font-size: .82rem;
            color: var(--ink-soft);
            text-transform: uppercase;
            letter-spacing: .06em;
            margin-bottom: .35rem;
        }

        /* Modal */
        .modal-content {
            background: var(--bs-card-bg);
            border: 1px solid var(--border);
            border-radius: 18px;
            box-shadow: var(--shadow-lg);
        }
        .modal-header, .modal-footer { border-color: var(--border); }
        .modal-title { font-family: 'Playfair Display', serif; font-weight: 700; }

        /* Floating Action Button — vinotinto */
        .fab {
            position: fixed;
            right: 1.1rem; bottom: 1.1rem;
            width: 62px; height: 62px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--brand-strong), var(--brand-soft));
            color: #fff;
            border: 0;
            box-shadow: 0 12px 28px -8px rgba(111,29,46,.55), 0 4px 10px rgba(0,0,0,.18);
            display: flex; align-items: center; justify-content: center;
            font-size: 1.7rem;
            z-index: 1050;
            transition: transform .15s ease, box-shadow .2s ease;
        }
        .fab:hover { transform: translateY(-2px) scale(1.03); box-shadow: 0 16px 36px -8px rgba(111,29,46,.65); }
        .fab:active { transform: translateY(0) scale(.98); }
        @media (min-width: 992px) { .fab { display: none; } }
        .quick-add-desktop { display: none; }
        @media (min-width: 992px) { .quick-add-desktop { display: inline-flex; } }

        /* Person dot, category chip */
        .person-dot {
            width: 10px; height: 10px;
            border-radius: 50%;
            display: inline-block;
            margin-right: .4rem;
            box-shadow: 0 0 0 2px var(--surface);
        }
        .category-chip {
            font-size: .72rem;
            padding: .28rem .65rem;
            border-radius: 999px;
            font-weight: 600;
            letter-spacing: .02em;
            display: inline-flex;
            align-items: center;
            gap: .35rem;
        }

        .badge.bg-warning { background: var(--accent) !important; color: #2c1d00 !important; }
        .badge.bg-success { background: var(--positive) !important; color: #fff !important; }

        /* Lista (list-group) */
        .list-group-item {
            background: transparent;
            border-color: var(--border);
            color: var(--ink);
            padding: .85rem 1.25rem;
        }

        /* Theme toggle */
        .theme-toggle {
            border: 1px solid var(--border);
            background: var(--surface);
            color: var(--ink-soft);
            width: 38px; height: 38px;
            border-radius: 10px;
            display: inline-flex; align-items: center; justify-content: center;
            cursor: pointer;
            transition: color .15s ease, border-color .15s ease;
        }
        .theme-toggle:hover { color: var(--brand); border-color: var(--brand); }
        [data-bs-theme="light"] .theme-toggle .icon-moon { display: inline; }
        [data-bs-theme="light"] .theme-toggle .icon-sun  { display: none; }
        [data-bs-theme="dark"]  .theme-toggle .icon-moon { display: none; }
        [data-bs-theme="dark"]  .theme-toggle .icon-sun  { display: inline; }

        /* Alerts */
        .alert {
            border: 1px solid var(--border);
            border-radius: 12px;
            background: var(--surface);
        }
        .alert-success { border-left: 4px solid var(--positive); color: var(--ink); }
        .alert-danger  { border-left: 4px solid var(--negative); color: var(--ink); }

        /* Pequeños detalles */
        .text-muted, .text-secondary { color: var(--ink-soft) !important; }
        a { color: var(--brand); text-decoration: none; }
        a:hover { color: var(--brand-soft); text-decoration: underline; }
        hr { border-color: var(--border); opacity: 1; }

        .page-title {
            font-family: 'Playfair Display', serif;
            font-weight: 700;
            font-size: 1.9rem;
            margin: 0;
        }
        .page-subtitle {
            color: var(--ink-soft);
            font-size: .92rem;
            text-transform: capitalize;
        }

        /* Form-check switch theming */
        .form-check-input:checked { background-color: var(--brand); border-color: var(--brand); }
    </style>
</head>
<body>

<nav class="navbar app-navbar navbar-expand-lg sticky-top">
    <div class="container-fluid">
        <a class="navbar-brand app-brand" href="{{ route('dashboard') }}">
            <span class="brand-mark"><i class="bi bi-gem"></i></span> Cuentas
        </a>
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="mainNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item"><a @class(['nav-link', 'active' => request()->routeIs('dashboard')]) href="{{ route('dashboard') }}"><i class="bi bi-speedometer2"></i> Resumen</a></li>
                <li class="nav-item"><a @class(['nav-link', 'active' => request()->routeIs('incomes.*')]) href="{{ route('incomes.index') }}"><i class="bi bi-arrow-down-circle"></i> Ingresos</a></li>
                <li class="nav-item"><a @class(['nav-link', 'active' => request()->routeIs('expenses.*')]) href="{{ route('expenses.index') }}"><i class="bi bi-arrow-up-circle"></i> Gastos</a></li>
                <li class="nav-item"><a @class(['nav-link', 'active' => request()->routeIs('budgets.*')]) href="{{ route('budgets.index') }}"><i class="bi bi-calendar3"></i> Meses</a></li>
                <li class="nav-item"><a @class(['nav-link', 'active' => request()->routeIs('categories.*')]) href="{{ route('categories.index') }}"><i class="bi bi-tags"></i> Categorías</a></li>
                <li class="nav-item"><a @class(['nav-link', 'active' => request()->routeIs('fixed-expenses.*')]) href="{{ route('fixed-expenses.index') }}"><i class="bi bi-pin-angle"></i> Gastos fijos</a></li>
                @can('manage-users')
                    <li class="nav-item"><a @class(['nav-link', 'active' => request()->routeIs('users.*')]) href="{{ route('users.index') }}"><i class="bi bi-people"></i> Usuarios</a></li>
                @endcan
            </ul>

            <div class="d-flex align-items-center gap-2">
                <button type="button" class="theme-toggle" id="themeToggle"
                        data-bs-toggle="tooltip" title="Cambiar tema claro/oscuro">
                    <i class="bi bi-moon-stars icon-moon"></i>
                    <i class="bi bi-sun icon-sun"></i>
                </button>
                @can('manage')
                    <button type="button" class="btn btn-brand quick-add-desktop"
                            data-bs-toggle="modal" data-bs-target="#quickExpenseModal"
                            title="Registrar un gasto rápido">
                        <i class="bi bi-plus-lg"></i> Agregar gasto
                    </button>
                @endcan
                @auth
                    <div class="dropdown">
                        <button class="theme-toggle dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false"
                                title="{{ auth()->user()->name }} · {{ auth()->user()->roleLabel() }}">
                            <i class="bi bi-person-circle"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow" style="border-radius:12px; border-color: var(--border); background: var(--surface);">
                            <li class="px-3 py-2 small">
                                <strong>{{ auth()->user()->name }}</strong><br>
                                <span class="text-muted">{{ auth()->user()->email }}</span><br>
                                <span class="badge mt-1" style="background: {{ auth()->user()->isManager() ? 'var(--brand)' : 'var(--info)' }}; color:#fff">
                                    {{ auth()->user()->roleLabel() }}
                                </span>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}" class="px-3 py-1">
                                    @csrf
                                    <button class="btn btn-light w-100 btn-sm">
                                        <i class="bi bi-box-arrow-right"></i> Cerrar sesión
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                @endauth
            </div>
        </div>
    </div>
</nav>

<main class="container py-4">
    @yield('content')
</main>

@php
    $flashStatus = session('status');
    $flashError  = session('error');
    $errorList   = $errors->all();
@endphp

@can('manage')
    <button type="button" class="fab" data-bs-toggle="modal" data-bs-target="#quickExpenseModal"
            aria-label="Agregar gasto" title="Agregar gasto">
        <i class="bi bi-plus-lg"></i>
    </button>

    @include('partials.quick_expense_modal')
@endcan

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.getElementById('themeToggle').addEventListener('click', function () {
        var html = document.documentElement;
        var next = html.getAttribute('data-bs-theme') === 'dark' ? 'light' : 'dark';
        html.setAttribute('data-bs-theme', next);
        localStorage.setItem('cuentas-theme', next);
        document.querySelector('meta[name="theme-color"]').setAttribute(
            'content',
            next === 'dark' ? '#0a0507' : '#6f1d2e'
        );
    });

    // Configuración global SweetAlert2 con paleta vinotinto
    const _theme = () => document.documentElement.getAttribute('data-bs-theme') || 'light';
    const swalDefaults = () => ({
        background: _theme() === 'dark' ? '#15090d' : '#ffffff',
        color:      _theme() === 'dark' ? '#f5ebed' : '#1a0d10',
        confirmButtonColor: '#6f1d2e',
        cancelButtonColor:  '#6c757d',
        customClass: { popup: 'swal-cuentas' },
    });
    const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3200,
        timerProgressBar: true,
        ...swalDefaults(),
    });

    // Confirmación de borrado: cualquier <form class="js-confirm-delete">
    document.addEventListener('submit', function (ev) {
        const form = ev.target;
        if (!form.classList || !form.classList.contains('js-confirm-delete')) return;
        if (form.dataset.confirmed === '1') return;
        ev.preventDefault();
        Swal.fire({
            ...swalDefaults(),
            title: '¿Eliminar?',
            text: form.dataset.message || '¿Confirmas que deseas eliminar este elemento?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#c0392b',
            reverseButtons: true,
        }).then(r => {
            if (r.isConfirmed) {
                form.dataset.confirmed = '1';
                form.submit();
            }
        });
    });

    // Mensajes flash → SweetAlert toast / modal
    document.addEventListener('DOMContentLoaded', function () {
        @if ($flashStatus)
            Toast.fire({ icon: 'success', title: @json($flashStatus) });
        @endif
        @if ($flashError)
            Swal.fire({ ...swalDefaults(), icon: 'error', title: 'Ups…', text: @json($flashError) });
        @endif
        @if (count($errorList))
            Swal.fire({
                ...swalDefaults(),
                icon: 'error',
                title: 'Revisa el formulario',
                html: @json('<ul style="text-align:left;margin:0;padding-left:1.2rem">'
                    . collect($errorList)->map(fn ($e) => '<li>'.e($e).'</li>')->implode('') . '</ul>'),
            });
        @endif

        // Tooltips Bootstrap
        document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => {
            new bootstrap.Tooltip(el, { placement: el.dataset.bsPlacement || 'top', container: 'body' });
        });
    });
</script>
@stack('scripts')
</body>
</html>
