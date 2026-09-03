@extends('layouts.propietario')

@section('title', 'Cambiar Contraseña')

@section('content')

    <div style="max-width: 420px; margin: 20px auto 0;">

        {{-- Ícono --}}
        <div style="text-align: center; margin-bottom: 24px;">
            <div style="font-size: 44px; margin-bottom: 8px;">🔑</div>
            @if (auth()->user()->propietario?->primer_ingreso)
                <div style="font-size: 17px; font-weight: 800; margin-bottom: 4px;">Bienvenido, Propietario</div>
                <div style="font-size: 12.5px; color: var(--text3);">
                    Por seguridad, debes cambiar tu contraseña inicial antes de ingresar a tu panel.
                </div>
            @else
                <div style="font-size: 17px; font-weight: 800;">Cambiar Contraseña</div>
            @endif
        </div>

        {{-- Errores --}}
        @if ($errors->any())
            <div style="background: var(--red-l); color: var(--red); border: 1px solid #fca5a5; padding: 12px; border-radius: 10px; margin-bottom: 14px; font-size: 13px;">
                <ul style="margin: 0; padding-left: 16px;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="card">
            <div class="card-body" style="padding: 20px;">
                <form method="POST" action="{{ route('propietario.cambiar-password.store') }}">
                    @csrf

                    <div style="margin-bottom: 14px;">
                        <label style="display: block; font-weight: 700; font-size: 12px; color: var(--text2); margin-bottom: 6px; text-transform: uppercase;">
                            Nueva Contraseña
                        </label>
                        <input type="password" name="password" required placeholder="Mínimo 6 caracteres"
                               style="width: 100%; padding: 10px 12px; border: 1px solid var(--border); border-radius: 8px; font-family: inherit; font-size: 14px;"
                               autocomplete="new-password">
                    </div>

                    <div style="margin-bottom: 20px;">
                        <label style="display: block; font-weight: 700; font-size: 12px; color: var(--text2); margin-bottom: 6px; text-transform: uppercase;">
                            Confirmar Nueva Contraseña
                        </label>
                        <input type="password" name="password_confirmation" required placeholder="Repite tu contraseña"
                               style="width: 100%; padding: 10px 12px; border: 1px solid var(--border); border-radius: 8px; font-family: inherit; font-size: 14px;"
                               autocomplete="new-password">
                    </div>

                    <button type="submit" class="btn-primary" style="display: flex; align-items: center; justify-content: center; gap: 8px;">
                        <i class="fa-solid fa-floppy-disk"></i> Guardar y Continuar
                    </button>
                </form>
            </div>
        </div>
    </div>

@endsection
