@extends('layouts.conductor')
@section('title', 'Mi Flota')

@section('content')

    @php $vehiculo = $conductor->vehiculos->first(); @endphp

    {{-- Hero - Centrado en el Vehículo --}}
    <div class="conductor-hero"
        style="flex-direction:column; text-align:center; padding:24px 20px; background:linear-gradient(135deg, #1e293b 0%, #0f172a 100%); color:white; border-bottom:none;">
        <div class="conductor-av"
            style="width:64px; height:64px; font-size:26px; margin:0 auto 10px; background:rgba(255,255,255,0.1); border:2px solid rgba(255,255,255,0.2); box-shadow:0 4px 12px rgba(0,0,0,0.1); display: flex; align-items: center; justify-content: center; border-radius: 50%;">
            <i class="fa-solid fa-car-side" style="color: #60a5fa;"></i>
        </div>
        <div class="conductor-hero-name" style="font-size:20px; font-weight:800; letter-spacing: 0.3px;">
            {{ $vehiculo?->placa_form ?? 'Sin Placa' }}
        </div>
        <div class="conductor-hero-sub" style="opacity:0.85; font-size:12.5px; margin-top:4px;">
            Flota #{{ $vehiculo?->numero_flota ?? 'S/N' }} • {{ $conductor->empresa?->nombre ?? 'Transporte SaaS' }}
        </div>
    </div>

    <div style="margin-top:-16px; padding:0 16px;">
        <form action="{{ route('conductor.perfil.update') }}" method="POST">
            @csrf
            @method('PUT')

            {{-- 1. Datos de la Flota --}}
            @if ($vehiculo)
                <div class="card" style="margin-bottom: 14px; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.05);">
                    <div class="card-header" style="background:#f8fafc; border-bottom:1px solid #f1f5f9; padding: 12px 16px; display: flex; align-items: center; justify-content: space-between;">
                        <span class="card-title" style="font-size:13.5px; color:#1e293b; font-weight: 800; display: flex; align-items: center; gap: 6px;">
                            <i class="fa-solid fa-car" style="color: var(--accent);"></i> Especificaciones del Vehículo
                        </span>
                        <span class="pill {{ $vehiculo->estado === 'activo' ? 'green' : 'red' }}" style="font-size: 10px;">
                            {{ strtoupper($vehiculo->estado) }}
                        </span>
                    </div>
                    <div class="card-body" style="padding: 14px;">
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px; font-size: 12px;">
                            <div><span style="color: var(--text3);">Padrón:</span> <b style="color: #2563eb;">#{{ $vehiculo->numero_flota ?? '—' }}</b></div>
                            <div><span style="color: var(--text3);">Marca:</span> <b>{{ $vehiculo->marca ?? '—' }}</b></div>
                            <div><span style="color: var(--text3);">Modelo:</span> <b>{{ $vehiculo->modelo ?? '—' }}</b></div>
                            <div><span style="color: var(--text3);">Año:</span> <b>{{ $vehiculo->anio ?? '—' }}</b></div>
                            <div><span style="color: var(--text3);">Color:</span> <b>{{ $vehiculo->color ?? '—' }}</b></div>
                            <div><span style="color: var(--text3);">N° Motor:</span> <b style="font-family: monospace;">{{ $vehiculo->numero_motor ?? '—' }}</b></div>
                            <div style="grid-column: span 2;"><span style="color: var(--text3);">N° Chasis:</span> <b style="font-family: monospace;">{{ $vehiculo->numero_chasis ?? '—' }}</b></div>
                            <div style="grid-column: span 2; margin-top: 4px; padding-top: 6px; border-top: 1px dashed var(--border);">
                                <span style="color: var(--text3);">Ruta Asignada:</span>
                                <b style="color: #16a34a;">{{ $vehiculo->rutas->where('pivot.activo', true)->first()?->nombre ?? 'Sin ruta' }}</b>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- 2. Documentos del Vehículo (SOAT y Rev. Técnica Editables) --}}
                <div class="card" style="margin-bottom: 14px; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.05); border: 1.5px solid var(--accent-l);">
                    <div class="card-header" style="background: var(--accent-l); border-bottom:1px solid #bfdbfe; padding: 12px 16px; display: flex; align-items: center; justify-content: space-between;">
                        <span class="card-title" style="font-size:13.5px; color: var(--accent); font-weight: 800; display: flex; align-items: center; gap: 6px;">
                            <i class="fa-solid fa-file-shield"></i> Documentación del Vehículo (Editable)
                        </span>
                        <span style="font-size: 10.5px; color: var(--accent); font-weight: 700;">
                            Actualizable
                        </span>
                    </div>
                    <div class="card-body" style="padding: 14px; display: flex; flex-direction: column; gap: 12px;">
                        {{-- SOAT --}}
                        <div>
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">
                                <label style="font-weight: 700; font-size: 12.5px; color: var(--text); display: flex; align-items: center; gap: 6px;">
                                    <i class="fa-solid fa-shield-halved" style="color: #0284c7;"></i> SOAT
                                </label>
                                @if($vehiculo->soat_vence)
                                    @php
                                        $diasSoat = now()->startOfDay()->diffInDays(\Carbon\Carbon::parse($vehiculo->soat_vence)->startOfDay(), false);
                                    @endphp
                                    @if($diasSoat < 0)
                                        <span class="pill red" style="font-size: 9.5px; padding: 2px 6px;">Vencido</span>
                                    @elseif($diasSoat <= 15)
                                        <span class="pill gold" style="font-size: 9.5px; padding: 2px 6px;">Vence en {{ $diasSoat }}d</span>
                                    @else
                                        <span class="pill green" style="font-size: 9.5px; padding: 2px 6px;">Vigente</span>
                                    @endif
                                @else
                                    <span class="pill gray" style="font-size: 9.5px; padding: 2px 6px;">No registrado</span>
                                @endif
                            </div>
                            <input type="date" name="soat_vence" value="{{ old('soat_vence', $vehiculo->soat_vence ? $vehiculo->soat_vence->toDateString() : '') }}" style="width: 100%; border: 1px solid var(--border); border-radius: 8px; padding: 8px 12px; font-family: inherit; font-size: 13px; font-weight: 600; color: var(--text); background: white;">
                        </div>

                        {{-- Revisión Técnica --}}
                        <div>
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">
                                <label style="font-weight: 700; font-size: 12.5px; color: var(--text); display: flex; align-items: center; gap: 6px;">
                                    <i class="fa-solid fa-screwdriver-wrench" style="color: #d97706;"></i> Revisión Técnica
                                </label>
                                @if($vehiculo->rev_tecnica_vence)
                                    @php
                                        $diasRev = now()->startOfDay()->diffInDays(\Carbon\Carbon::parse($vehiculo->rev_tecnica_vence)->startOfDay(), false);
                                    @endphp
                                    @if($diasRev < 0)
                                        <span class="pill red" style="font-size: 9.5px; padding: 2px 6px;">Vencida</span>
                                    @elseif($diasRev <= 15)
                                        <span class="pill gold" style="font-size: 9.5px; padding: 2px 6px;">Vence en {{ $diasRev }}d</span>
                                    @else
                                        <span class="pill green" style="font-size: 9.5px; padding: 2px 6px;">Vigente</span>
                                    @endif
                                @else
                                    <span class="pill gray" style="font-size: 9.5px; padding: 2px 6px;">No registrada</span>
                                @endif
                            </div>
                            <input type="date" name="rev_tecnica_vence" value="{{ old('rev_tecnica_vence', $vehiculo->rev_tecnica_vence ? $vehiculo->rev_tecnica_vence->toDateString() : '') }}" style="width: 100%; border: 1px solid var(--border); border-radius: 8px; padding: 8px 12px; font-family: inherit; font-size: 13px; font-weight: 600; color: var(--text); background: white;">
                        </div>

                        {{-- Tarjeta de Propiedad --}}
                        @if($vehiculo->tarjeta_prop_vence)
                            <div style="display: flex; justify-content: space-between; align-items: center; padding: 8px 10px; background: #f8fafc; border-radius: 6px; font-size: 12px;">
                                <span style="color: var(--text2);"><i class="fa-solid fa-id-card" style="color: #64748b;"></i> Tarjeta Propiedad:</span>
                                <b>{{ $vehiculo->tarjeta_prop_vence->format('d/m/Y') }}</b>
                            </div>
                        @endif
                    </div>
                </div>
            @else
                <div class="card" style="border:2px dashed #e2e8f0; background:transparent; text-align:center; padding:30px 20px; margin-bottom: 14px;">
                    <div style="font-size:32px; margin-bottom:10px; color: var(--text3);"><i class="fa-solid fa-car"></i></div>
                    <div style="font-size:14px; color:#64748b; font-weight:600;">Sin flota asignada</div>
                    <div style="font-size:12px; color:#94a3b8; margin-top:4px;">No hay una flota vinculada a esta cuenta.</div>
                </div>
            @endif

            {{-- 3. Personal Asignado (El Conductor de esta cuenta) --}}
            <div class="card" style="margin-bottom: 14px; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.05);">
                <div class="card-header" style="background:#f8fafc; border-bottom:1px solid #f1f5f9; padding: 12px 16px; display: flex; align-items: center;">
                    <span class="card-title" style="font-size:13.5px; color:#1e293b; font-weight: 800; display: flex; align-items: center; gap: 6px;">
                        <i class="fa-solid fa-user-tie" style="color: var(--accent);"></i> Mis Datos de Conductor
                    </span>
                </div>
                <div class="card-body" style="padding: 14px; display: flex; flex-direction: column; gap: 10px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; padding-bottom: 8px; border-bottom: 1px solid #f1f5f9;">
                        <span style="font-size: 12px; color: var(--text3);">Nombre Completo:</span>
                        <b style="font-size: 13px; color: var(--text);">{{ $conductor->nombre_completo }}</b>
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: center; padding-bottom: 8px; border-bottom: 1px solid #f1f5f9;">
                        <span style="font-size: 12px; color: var(--text3);">DNI:</span>
                        <b style="font-size: 13px; font-family: monospace;">{{ $conductor->dni ?? '—' }}</b>
                    </div>

                    {{-- Licencia --}}
                    <div>
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">
                            <label style="font-weight: 600; font-size: 12px; color: var(--text2);">Categoría Licencia</label>
                            <label style="font-weight: 600; font-size: 12px; color: var(--text2);">Fecha Vencimiento</label>
                        </div>
                        <div style="display: grid; grid-template-columns: 1fr 1.5fr; gap: 8px;">
                            <input type="text" name="tipo_licencia" value="{{ old('tipo_licencia', $conductor->tipo_licencia) }}" placeholder="Ej: AII-B" style="border: 1px solid var(--border); border-radius: 8px; padding: 8px 10px; font-family: inherit; font-size: 12.5px; font-weight: 600; color: var(--text); background: white;">
                            <input type="date" name="licencia_vence" value="{{ old('licencia_vence', $conductor->licencia_vence ? $conductor->licencia_vence->toDateString() : '') }}" style="border: 1px solid var(--border); border-radius: 8px; padding: 8px 10px; font-family: inherit; font-size: 12.5px; font-weight: 600; color: var(--text); background: white;">
                        </div>
                    </div>

                    {{-- Carnet Habilitación --}}
                    <div>
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">
                            <label style="font-weight: 600; font-size: 12px; color: var(--text2);">Carnet Habilitación</label>
                            <label style="font-weight: 600; font-size: 12px; color: var(--text2);">Vence Carnet</label>
                        </div>
                        <div style="display: grid; grid-template-columns: 1fr 1.5fr; gap: 8px;">
                            <input type="text" name="carnet_habilitacion_tipo" value="{{ old('carnet_habilitacion_tipo', $conductor->carnet_habilitacion_tipo) }}" placeholder="Ej: Cat. A" style="border: 1px solid var(--border); border-radius: 8px; padding: 8px 10px; font-family: inherit; font-size: 12.5px; font-weight: 600; color: var(--text); background: white;">
                            <input type="date" name="carnet_habilitacion_vence" value="{{ old('carnet_habilitacion_vence', $conductor->carnet_habilitacion_vence ? \Carbon\Carbon::parse($conductor->carnet_habilitacion_vence)->toDateString() : '') }}" style="border: 1px solid var(--border); border-radius: 8px; padding: 8px 10px; font-family: inherit; font-size: 12.5px; font-weight: 600; color: var(--text); background: white;">
                        </div>
                    </div>

                    {{-- Teléfono --}}
                    <div>
                        <label style="font-weight: 600; font-size: 12px; color: var(--text2); display: block; margin-bottom: 4px;">Teléfono Celular</label>
                        <input type="text" name="telefono" value="{{ old('telefono', $conductor->telefono) }}" placeholder="Ej: 987654321" style="width: 100%; border: 1px solid var(--border); border-radius: 8px; padding: 8px 12px; font-family: inherit; font-size: 13px; font-weight: 600; color: var(--text); background: white;">
                    </div>
                </div>
            </div>

            {{-- 4. Propietario / Socio Responsable --}}
            @if ($vehiculo && $vehiculo->propietario)
                <div class="card" style="margin-bottom: 14px; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.05);">
                    <div class="card-header" style="background:#f8fafc; border-bottom:1px solid #f1f5f9; padding: 12px 16px; display: flex; align-items: center;">
                        <span class="card-title" style="font-size:13.5px; color:#1e293b; font-weight: 800; display: flex; align-items: center; gap: 6px;">
                            <i class="fa-solid fa-handshake" style="color: var(--gold);"></i> Propietario Responsable
                        </span>
                    </div>
                    <div class="card-body" style="padding: 14px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                            <span style="font-size: 12px; color: var(--text3);">Propietario:</span>
                            <b style="font-size: 13px; color: var(--text);">{{ $vehiculo->propietario->nombre_completo }}</b>
                        </div>
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span style="font-size: 12px; color: var(--text3);">Teléfono:</span>
                            @if($vehiculo->propietario->telefono)
                                <a href="tel:{{ $vehiculo->propietario->telefono }}" style="font-weight: 700; color: #2563eb; text-decoration: none; font-size: 13px; display: flex; align-items: center; gap: 4px;">
                                    <i class="fa-solid fa-phone"></i> {{ $vehiculo->propietario->telefono }}
                                </a>
                            @else
                                <span style="color: var(--text3); font-size: 12px;">No registrado</span>
                            @endif
                        </div>
                    </div>
                </div>
            @endif

            {{-- Acciones --}}
            <div style="display:flex; flex-direction:column; gap:10px; margin-top:16px; margin-bottom:30px;">
                <button type="submit" class="btn btn-primary btn-block"
                    style="justify-content:center; padding:13px; font-weight:700; font-size: 14px; border-radius:12px; display: flex; align-items: center; gap: 8px; background: #2563eb; color: white; border: none; cursor: pointer;">
                    <i class="fa-solid fa-floppy-disk"></i> Guardar Cambios de Perfil y Vehículo
                </button>
                <a href="{{ route('conductor.cambiar-password') }}" class="btn btn-secondary btn-block"
                    style="justify-content:center; padding:12px; font-weight:600; font-size: 13px; border-radius:12px; display: flex; align-items: center; gap: 6px; text-decoration: none; background: #f1f5f9; color: var(--text); border: 1px solid var(--border);">
                    <i class="fa-solid fa-key"></i> Cambiar Mi Contraseña
                </a>
        </form>

        <form method="POST" action="{{ route('logout') }}" style="margin-top: 4px;">
            @csrf
            <button type="submit" class="btn btn-danger btn-block"
                style="justify-content:center; padding:12px; font-weight:600; font-size: 13px; border-radius:12px; background:#ef4444; color: white; border: none; width: 100%; display: flex; align-items: center; gap: 6px; cursor: pointer;">
                <i class="fa-solid fa-arrow-right-from-bracket"></i> Cerrar Sesión
            </button>
        </form>
    </div>

    </div>

@endsection
