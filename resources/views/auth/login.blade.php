<!doctype html>
<html lang="es" data-bs-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#5c1a2b">
    <title>Iniciar sesión · Cuentas</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        (function(){
            var t = localStorage.getItem('cuentas-theme') ||
                    (matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
            document.documentElement.setAttribute('data-bs-theme', t);
        })();
    </script>
    <style>
        :root {
            --brand: #6f1d2e; --brand-strong: #4a1220; --brand-soft: #8c2a44; --brand-glow: #c44569;
            --surface: #ffffff; --surface-2: #faf6f3; --ink: #1a0d10; --ink-soft: #5a4b50; --border: #ece2dd;
        }
        [data-bs-theme="dark"] {
            --brand: #c44569; --brand-strong: #a02d4a; --brand-soft: #d96b89; --brand-glow: #e88aa3;
            --surface: #15090d; --surface-2: #1f1014; --ink: #f5ebed; --ink-soft: #c9b3b8; --border: #3a1f27;
        }
        body {
            font-family: 'Inter', sans-serif;
            background:
                radial-gradient(1200px 600px at 100% -10%, rgba(196,69,105,.18), transparent 60%),
                radial-gradient(900px 500px at -10% 110%, rgba(111,29,46,.18), transparent 60%),
                var(--surface-2);
            min-height: 100vh;
            display: flex; align-items: center; justify-content: center;
            color: var(--ink);
            padding: 1rem;
        }
        .login-card {
            width: 100%; max-width: 420px;
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 20px;
            padding: 2.5rem 2rem;
            box-shadow: 0 30px 80px -30px rgba(74,18,32,.5);
        }
        .brand-mark {
            width: 64px; height: 64px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--brand-strong), var(--brand-soft));
            color: #fff;
            display: inline-flex; align-items: center; justify-content: center;
            font-size: 1.6rem;
            margin: 0 auto 1rem;
            box-shadow: 0 8px 24px -8px rgba(111,29,46,.6);
        }
        h1 { font-family: 'Playfair Display', serif; font-weight: 700; margin: 0; }
        .form-control {
            background: var(--surface);
            border: 1px solid var(--border);
            color: var(--ink);
            border-radius: 10px;
            padding: .65rem .85rem;
        }
        .form-control:focus {
            border-color: var(--brand);
            box-shadow: 0 0 0 .2rem rgba(111,29,46,.15);
            background: var(--surface);
            color: var(--ink);
        }
        [data-bs-theme="dark"] .form-control { background: var(--surface-2); }
        .form-label { font-weight: 600; font-size: .82rem; color: var(--ink-soft); text-transform: uppercase; letter-spacing: .06em; }
        .btn-brand {
            width: 100%;
            background: linear-gradient(135deg, var(--brand-strong), var(--brand));
            color: #fff; border: 0; padding: .7rem;
            font-weight: 600; border-radius: 10px;
            box-shadow: 0 6px 18px -4px rgba(111,29,46,.5);
        }
        .btn-brand:hover { color: #fff; filter: brightness(1.08); transform: translateY(-1px); }
    </style>
</head>
<body>
    <div class="login-card text-center">
        <div class="brand-mark"><i class="bi bi-gem"></i></div>
        <h1>Cuentas</h1>
        <p class="text-muted mb-4" style="color: var(--ink-soft) !important">Gestiona el presupuesto del hogar.</p>

        <form method="POST" action="{{ route('login') }}" class="text-start">
            @csrf
            <div class="mb-3">
                <label class="form-label">Correo</label>
                <input type="email" name="email" class="form-control" value="{{ old('email') }}" required autofocus>
            </div>
            <div class="mb-3">
                <label class="form-label">Contraseña</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <div class="form-check mb-3">
                <input type="checkbox" name="remember" class="form-check-input" id="remember">
                <label class="form-check-label" for="remember">Recordarme</label>
            </div>
            <button class="btn-brand"><i class="bi bi-box-arrow-in-right"></i> Entrar</button>
        </form>
    </div>

    @if ($errors->any())
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                Swal.fire({
                    icon: 'error',
                    title: 'No pudimos iniciar sesión',
                    text: @json($errors->first()),
                    confirmButtonColor: '#6f1d2e',
                    background: document.documentElement.getAttribute('data-bs-theme') === 'dark' ? '#15090d' : '#fff',
                    color: document.documentElement.getAttribute('data-bs-theme') === 'dark' ? '#f5ebed' : '#1a0d10',
                });
            });
        </script>
    @endif
</body>
</html>
