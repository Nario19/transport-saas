@extends('layouts.propietario')

@section('title', 'Mi Perfil')

@section('content')

    {{-- Hero Propietario --}}
    <div class="card" style="text-align: center; padding: 24px 16px; background: linear-gradient(135deg, #1e3a8a, #1d4ed8); color: #ffffff; border: none; border-radius: 16px;">
        <div style="width: 64px; height: 64px; border-radius: 50%; background: rgba(255, 255, 255, 0.2); display: flex; align-items: center; justify-content: center; font-size: 26px; font-weight: 800; margin: 0 auto 12px; border: 2px solid rgba(255, 255, 255, 0.3);">
            {{ Auth::user()->iniciales }}
        </div>
        <div style="font-size: 18px; font-weight: 800;">{{ $propietario->nombre_completo }}</div>
        <div style="font-size: 12px; opacity: 0.85; margin-top: 2px;">
            DNI: {{ $propietario->dni ?? '---' }} • {{ $propietario->empresa?->nombre ?? 'Transportes' }}
        </div>
    </div>

    {{-- Datos Personales --}}
    <div class="card">
        <div class="card-header">
            <span class="card-title"><i class="fa-solid fa-user" style="color: var(--accent); margin-right: 6px;"></i> Información del Propietario</span>
        </div>
        <div class="card-body" style="padding: 0;">
            <div style="padding: 12px 16px; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between;">
                <span style="color: var(--text3); font-weight: 600;">Teléfono</span>
                <span style="font-weight: 700;">{{ $propietario->telefono ?? '—' }}</span>
            </div>
            <div style="padding: 12px 16px; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between;">
                <span style="color: var(--text3); font-weight: 600;">Correo</span>
                <span style="font-weight: 700;">{{ $propietario->email ?? '—' }}</span>
            </div>
            <div style="padding: 12px 16px; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between;">
                <span style="color: var(--text3); font-weight: 600;">Dirección</span>
                <span style="font-weight: 700;">{{ $propietario->direccion ?? '—' }}</span>
            </div>
            <div style="padding: 12px 16px; display: flex; justify-content: space-between;">
                <span style="color: var(--text3); font-weight: 600;">Tipo de Persona</span>
                <span class="pill {{ $propietario->es_socio ? 'gold' : 'blue' }}">
                    {{ $propietario->es_socio ? 'Socio' : 'Persona Normal' }}
                </span>
            </div>
        </div>
    </div>

    {{-- Seguridad y Contraseña --}}
    <div class="card">
        <div class="card-header">
            <span class="card-title"><i class="fa-solid fa-lock" style="color: var(--accent); margin-right: 6px;"></i> Seguridad de la Cuenta</span>
        </div>
        <div class="card-body">
            <p style="font-size: 13px; color: var(--text2); margin-bottom: 14px;">
                Puedes cambiar tu contraseña de acceso en cualquier momento.
            </p>
            <a href="{{ route('propietario.cambiar-password') }}" class="btn-primary" style="display: inline-block; width: auto; padding: 10px 18px; font-size: 13px;">
                <i class="fa-solid fa-key" style="margin-right: 6px;"></i> Cambiar Contraseña
            </a>
        </div>
    </div>

@endsection
