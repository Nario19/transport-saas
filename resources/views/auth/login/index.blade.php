@extends('layouts.auth')

@section('auth_title', 'Iniciar Sesión')

@section('content')
    <form method="POST" action="{{ route('login') }}">
        @csrf

        {{-- Campo: Email / Placa --}}
        <div style="margin-bottom: 16px;">
            <label style="display:block; margin-bottom:6px; font-weight:600; font-size:13px;">Usuario / Placa</label>
            <input type="text" name="email" value="{{ old('email') }}" required autofocus
                style="width:100%; padding:10px; border:1px solid var(--border); border-radius:8px; background:var(--bg);">
            @error('email')
                <span
                    style="color:var(--danger); font-size:12px; font-weight:600; margin-top:4px; display:block;">{{ $message }}</span>
            @enderror
        </div>

        {{-- Campo: Password --}}
        <div style="margin-bottom: 16px;">
            <label style="display:block; margin-bottom:6px; font-weight:600; font-size:13px;">Contraseña</label>
            <div style="position: relative; display: flex; align-items: center;">
                <input type="password" id="password" name="password" required
                    style="width:100%; padding:10px 40px 10px 10px; border:1px solid var(--border); border-radius:8px; background:var(--bg);">
                <button type="button" onclick="togglePasswordVisibility()" 
                    style="position: absolute; right: 10px; background: none; border: none; cursor: pointer; color: var(--text3); padding: 6px; display: flex; align-items: center; justify-content: center; transition: color 0.2s;"
                    onmouseover="this.style.color='var(--accent)'" onmouseout="this.style.color='var(--text3)'"
                    title="Mostrar u ocultar contraseña">
                    <i id="togglePasswordIcon" class="fa-solid fa-eye" style="font-size: 15px;"></i>
                </button>
            </div>
            @error('password')
                <span
                    style="color:var(--danger); font-size:12px; font-weight:600; margin-top:4px; display:block;">{{ $message }}</span>
            @enderror
        </div>

        {{-- Opciones adicionales --}}
        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:20px;">
            <label style="display:flex; align-items:center; gap:8px; font-size:13px; cursor:pointer; color:var(--text2);">
                <input type="checkbox" name="remember"> Recordarme
            </label>
        </div>

        {{-- Botón de Acción --}}
        <button type="submit" class="btn-primary" style="width:100%; padding:14px; font-weight:800; border-radius:10px; justify-content: center;">
            Entrar al Sistema
        </button>

        {{-- Descarga de App Android --}}
        <div style="margin-top: 22px; padding-top: 18px; border-top: 1px solid var(--border); text-align: center;">
            <a href="/descargas/TransJunin.apk" download 
               style="width: 100%; justify-content: center; padding: 12px 16px; font-size: 12.5px; font-weight: 800; letter-spacing: 0.3px; border-radius: 10px; display: inline-flex; align-items: center; gap: 9px; text-decoration: none; color: #065f46; background: #ecfdf5; border: 1.5px solid #a7f3d0; box-shadow: 0 1px 2px rgba(0,0,0,0.05); transition: all 0.2s ease-in-out;"
               onmouseover="this.style.background='#d1fae5'; this.style.borderColor='#6ee7b7';"
               onmouseout="this.style.background='#ecfdf5'; this.style.borderColor='#a7f3d0';">
                <i class="fa-brands fa-android" style="color: #10b981; font-size: 18px;"></i>
                <span>DESCARGAR APLICACIÓN PARA ANDROID</span>
            </a>
        </div>
    </form>

    <script>
        function togglePasswordVisibility() {
            const passInput = document.getElementById('password');
            const icon = document.getElementById('togglePasswordIcon');
            if (passInput.type === 'password') {
                passInput.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                passInput.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
    </script>
@endsection
