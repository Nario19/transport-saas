<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>@yield('title', 'Flota+') — Panel Propietario</title>
    @php
        $logoFavicon = Auth::check() && Auth::user()->empresa && Auth::user()->empresa->logo_path
            ? asset('storage/' . Auth::user()->empresa->logo_path)
            : asset('images/logo.png');
    @endphp
    <link rel="icon" type="image/png" href="{{ $logoFavicon }}">
    <link
        href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;600&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        :root {
            --bg: #f0f2f7;
            --card: #ffffff;
            --border: #e2e6ef;
            --border2: #d0d6e3;
            --accent: #1d4ed8;
            --accent-l: #dbeafe;
            --gold: #d97706;
            --gold-l: #fef3c7;
            --green: #16a34a;
            --green-l: #dcfce7;
            --red: #dc2626;
            --red-l: #fee2e2;
            --orange: #ea580c;
            --orange-l: #ffedd5;
            --text: #0f172a;
            --text2: #475569;
            --text3: #94a3b8;
            --shadow: 0 1px 3px rgba(0, 0, 0, 0.07), 0 1px 2px rgba(0, 0, 0, 0.04);
            --shadow-m: 0 4px 12px rgba(0, 0, 0, 0.08), 0 2px 4px rgba(0, 0, 0, 0.04);
            --nav-h: 64px;
            --top-h: 56px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            -webkit-tap-highlight-color: transparent;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: var(--bg);
            color: var(--text);
            font-size: 14px;
            min-height: 100vh;
            padding-bottom: var(--nav-h);
        }

        /* ── TOPBAR ── */
        .c-topbar {
            position: sticky;
            top: 0;
            z-index: 100;
            background: var(--card);
            border-bottom: 1px solid var(--border);
            height: var(--top-h);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 18px;
            box-shadow: var(--shadow);
        }

        .c-topbar-left {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .c-brand-icon {
            width: 32px;
            height: 32px;
            background: var(--accent);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 13px;
            color: #fff;
        }

        .c-topbar-title {
            font-size: 15px;
            font-weight: 700;
        }

        .c-topbar-sub {
            font-size: 11px;
            color: var(--text3);
        }

        .c-topbar-right {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .c-av {
            width: 34px;
            height: 34px;
            background: var(--accent);
            border-radius: 9px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: 700;
            color: #fff;
            text-decoration: none;
        }

        /* ── CONTENT ── */
        .c-content {
            padding: 16px 16px 8px;
            max-width: 600px;
            margin: 0 auto;
        }

        /* ── NAV INFERIOR ── */
        .c-nav {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            height: var(--nav-h);
            background: var(--card);
            border-top: 1px solid var(--border);
            display: flex;
            align-items: stretch;
            z-index: 100;
            box-shadow: 0 -2px 12px rgba(0, 0, 0, 0.06);
            max-width: 600px;
            margin: 0 auto;
        }

        .c-nav-item {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 4px;
            text-decoration: none;
            color: var(--text3);
            font-size: 10px;
            font-weight: 600;
            transition: color .15s;
            position: relative;
            padding-bottom: 4px;
        }

        .c-nav-item.active {
            color: var(--accent);
        }

        .c-nav-item.active::before {
            content: '';
            position: absolute;
            top: 0;
            left: 20%;
            right: 20%;
            height: 3px;
            background: var(--accent);
            border-radius: 0 0 4px 4px;
        }

        .c-nav-icon {
            font-size: 19px;
            line-height: 1;
        }

        /* ── CARDS ── */
        .card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 14px;
            box-shadow: var(--shadow);
            margin-bottom: 14px;
            overflow: hidden;
        }

        .card-header {
            padding: 14px 16px;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .card-title {
            font-size: 14px;
            font-weight: 700;
        }

        .card-body {
            padding: 16px;
        }

        /* ── STATS ── */
        .stat-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
            margin-bottom: 14px;
        }

        .stat-card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 12px;
            box-shadow: var(--shadow);
            text-align: center;
        }

        .stat-val {
            font-size: 20px;
            font-weight: 800;
            color: var(--accent);
            line-height: 1.2;
        }

        .stat-lbl {
            font-size: 10.5px;
            color: var(--text3);
            margin-top: 3px;
            font-weight: 600;
            text-transform: uppercase;
        }

        /* ── PILLS & BADGES ── */
        .pill {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 4px 10px;
            border-radius: 99px;
            font-size: 11px;
            font-weight: 700;
        }

        .pill.green { background: var(--green-l); color: var(--green); }
        .pill.red   { background: var(--red-l); color: var(--red); }
        .pill.blue  { background: var(--accent-l); color: var(--accent); }
        .pill.gold  { background: var(--gold-l); color: var(--gold); }
        .pill.gray  { background: #f1f5f9; color: var(--text2); }

        .btn-primary {
            display: block;
            width: 100%;
            background: var(--accent);
            color: #fff;
            border: none;
            padding: 13px;
            border-radius: 10px;
            font-weight: 700;
            font-size: 14px;
            cursor: pointer;
            text-align: center;
            text-decoration: none;
            font-family: inherit;
            box-shadow: 0 4px 10px rgba(29, 78, 216, 0.25);
        }

        .btn-secondary {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: #f1f5f9;
            color: var(--text);
            border: 1px solid var(--border);
            padding: 8px 14px;
            border-radius: 8px;
            font-weight: 700;
            font-size: 12px;
            cursor: pointer;
            text-decoration: none;
        }

        .empty-state {
            padding: 30px;
            text-align: center;
            color: var(--text3);
            font-size: 13px;
        }
    </style>
</head>

<body>

    <!-- TOPBAR -->
    <header class="c-topbar">
        <div class="c-topbar-left">
            <div class="c-brand-icon">
                <i class="fa-solid fa-crown"></i>
            </div>
            <div>
                <div class="c-topbar-title">Flota+ Propietario</div>
                <div class="c-topbar-sub">{{ Auth::user()->empresa?->nombre ?? 'Transportes' }}</div>
            </div>
        </div>
        <div class="c-topbar-right">
            <a href="{{ route('propietario.perfil') }}" class="c-av">
                {{ Auth::user()->iniciales }}
            </a>
            <form method="POST" action="{{ route('logout') }}" style="display:inline;" onsubmit="return confirm('¿Deseas cerrar sesión?');">
                @csrf
                <button type="submit" style="background:none; border:none; color:var(--text3); font-size:16px; cursor:pointer; padding:6px;">
                    <i class="fa-solid fa-arrow-right-from-bracket"></i>
                </button>
            </form>
        </div>
    </header>

    <!-- CONTENT -->
    <main class="c-content">
        @if (session('success'))
            <div style="background:var(--green-l); color:var(--green); padding:12px 14px; border-radius:10px; font-weight:700; font-size:13px; margin-bottom:14px; border:1px solid #86efac; display:flex; align-items:center; gap:8px;">
                <i class="fa-solid fa-circle-check"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if (session('error'))
            <div style="background:var(--red-l); color:var(--red); padding:12px 14px; border-radius:10px; font-weight:700; font-size:13px; margin-bottom:14px; border:1px solid #fca5a5; display:flex; align-items:center; gap:8px;">
                <i class="fa-solid fa-circle-exclamation"></i>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        @yield('content')
    </main>

    <!-- BOTTOM NAVIGATION (5 TABS) -->
    <nav class="c-nav">
        <a href="{{ route('propietario.dashboard') }}"
           class="c-nav-item {{ request()->routeIs('propietario.dashboard') ? 'active' : '' }}">
            <i class="fa-solid fa-house c-nav-icon"></i>
            <span>Inicio</span>
        </a>

        <a href="{{ route('propietario.vueltas') }}"
           class="c-nav-item {{ request()->routeIs('propietario.vueltas') ? 'active' : '' }}">
            <i class="fa-solid fa-arrows-rotate c-nav-icon"></i>
            <span>Vueltas</span>
        </a>

        <a href="{{ route('propietario.tributos') }}"
           class="c-nav-item {{ request()->routeIs('propietario.tributos') ? 'active' : '' }}">
            <i class="fa-solid fa-receipt c-nav-icon"></i>
            <span>Tributos</span>
        </a>

        <a href="{{ route('propietario.sanciones') }}"
           class="c-nav-item {{ request()->routeIs('propietario.sanciones') ? 'active' : '' }}">
            <i class="fa-solid fa-triangle-exclamation c-nav-icon"></i>
            <span>Sanciones</span>
        </a>

        <a href="{{ route('propietario.datos') }}"
           class="c-nav-item {{ request()->routeIs('propietario.datos') ? 'active' : '' }}">
            <i class="fa-solid fa-car c-nav-icon"></i>
            <span>Mi Flota</span>
        </a>
    </nav>

    @yield('scripts')
</body>

</html>
