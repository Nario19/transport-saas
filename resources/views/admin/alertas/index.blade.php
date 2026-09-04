@extends('layouts.admin')

@php
    $pageTitle = 'Alertas';
    $pageSubtitle = 'Gestión y control de alertas en tiempo real';
@endphp

@section('content')
<div class="panel">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;flex-wrap:wrap;gap:15px;">
        <div>
            <h2 style="font-size:20px;font-weight:800;">Centro de Alertas y Operativos</h2>
            <div style="font-size:12px;color:var(--text3);">
                <i class="fa-solid fa-bullhorn" style="color:var(--accent);"></i> Emisión de comunicados, operativos y desvíos en tiempo real para la flota
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert success" style="margin-bottom: 20px;">
            <i class="fa-solid fa-circle-check"></i> {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert error" style="margin-bottom: 20px;">
            <i class="fa-solid fa-circle-xmark"></i> {{ session('error') }}
        </div>
    @endif

    <div style="display: grid; grid-template-columns: 1fr 1.65fr; gap: 24px; align-items: start;">
        
        {{-- COLUMNA 1: FORMULARIO REGISTRO & OPCIONES PREDEFINIDAS --}}
        <div style="display: flex; flex-direction: column; gap: 20px;">
            
            {{-- EMITIR ALERTA / GUARDAR NUEVA ALERTA --}}
            <div class="card">
                <div class="card-header" style="background: var(--bg2); border-bottom: 1px solid var(--border);">
                    <span class="card-title" style="font-size: 14px; font-weight: 800;">
                        <i class="fa-solid fa-bullhorn" style="color:var(--accent); margin-right:6px;"></i> Emitir Nueva Alerta
                    </span>
                </div>
                <div class="card-body" style="padding: 20px;">
                    <form action="{{ route('admin.alertas.store') }}" method="POST">
                        @csrf
                        
                        {{-- Nombre / Título de la Alerta --}}
                        <div class="field" style="margin-bottom: 16px;">
                            <label style="font-weight:700; font-size:13px; color:var(--text); margin-bottom:6px; display:block;">
                                Nombre / Título de la Alerta <span style="color:var(--red);">*</span>
                            </label>
                            <input type="text" name="titulo" required class="form-control" 
                                   style="width: 100%; padding: 10px 12px; border-radius: 8px; border: 1px solid var(--border); font-size:13px; font-weight:700; color:var(--text); background:var(--bg);">
                        </div>

                        {{-- Tipo de Alerta & Duración Activa en Minutos --}}
                        <div style="display: grid; grid-template-columns: 1.1fr 0.9fr; gap: 12px; margin-bottom: 16px;">
                            <div class="field">
                                <label style="font-weight:700; font-size:12.5px; color:var(--text); margin-bottom:6px; display:block;">
                                    Tipo de Alerta
                                </label>
                                <select name="tipo" class="form-control" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid var(--border); font-size:12.5px; font-weight:700; color:var(--text); background:var(--bg);">
                                    @if(isset($tiposAlerta) && $tiposAlerta->count() > 0)
                                        @foreach($tiposAlerta as $t)
                                            <option value="{{ $t->nombre }}">{{ $t->nombre }}</option>
                                        @endforeach
                                    @else
                                        <option value="Operativo / Control">Operativo / Control</option>
                                        <option value="Desvío / Obras">Desvío / Obras</option>
                                        <option value="Fiscalización">Fiscalización</option>
                                        <option value="Aviso General">Aviso General</option>
                                    @endif
                                </select>
                            </div>
                            <div class="field">
                                <label style="font-weight:700; font-size:12.5px; color:var(--text); margin-bottom:6px; display:block;">
                                    Duración Activa (min)
                                </label>
                                <input type="number" name="duracion_minutos" value="60" min="5" max="1440" required class="form-control" 
                                       style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid var(--border); font-size:13px; font-weight:700; color:var(--text); background:var(--bg);">
                            </div>
                        </div>

                        {{-- Punto Crítico / Ubicación --}}
                        <div class="field" style="margin-bottom: 16px;">
                            <label style="font-weight:700; font-size:12.5px; color:var(--text); margin-bottom:6px; display:block;">
                                Punto Crítico / Ubicación (Opcional)
                            </label>
                            <select name="punto" class="form-control" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid var(--border); font-size:13px; font-weight:700; color:var(--text); background:var(--bg);">
                                <option value="">-- Ubicación General / Toda la Ruta --</option>
                                @foreach($puntos as $punto)
                                    <option value="{{ $punto->nombre }}">{{ $punto->nombre }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Mensaje / Indicaciones para el Conductor --}}
                        <div class="field" style="margin-bottom: 20px;">
                            <label style="font-weight:700; font-size:12.5px; color:var(--text); margin-bottom:6px; display:block;">
                                Mensaje / Indicaciones para el Conductor
                            </label>
                            <textarea name="mensaje" rows="2" class="form-control" 
                                      style="width: 100%; padding: 10px 12px; border-radius: 8px; border: 1px solid var(--border); font-size:12.5px; color:var(--text); background:var(--bg); resize: vertical; font-family: inherit;"></textarea>
                        </div>

                        <button type="submit" class="btn-primary" style="width: 100%; height: 46px; display: flex; align-items: center; justify-content: center; gap: 8px; font-size: 14px; font-weight: 800; border-radius: 10px; cursor: pointer;">
                            <i class="fa-solid fa-paper-plane"></i> Emitir Alerta a la Flota
                        </button>
                    </form>
                </div>
            </div>

            {{-- GESTIÓN DE TIPOS DE ALERTA PREDEFINIDOS --}}
            <div class="card">
                <div class="card-header" style="display:flex; justify-content:space-between; align-items:center;">
                    <span class="card-title" style="font-size: 13.5px; font-weight: 800;">
                        <i class="fa-solid fa-tags" style="color:var(--accent); margin-right:5px;"></i> Tipos de Alerta Predefinidos
                    </span>
                </div>
                <div class="card-body" style="padding: 16px 20px;">
                    <form action="{{ route('admin.tipos-alerta.store') }}" method="POST" style="margin-bottom: 14px; display: flex; gap: 8px;">
                        @csrf
                        <div class="field" style="margin: 0; flex: 1;">
                            <input type="text" name="nombre" required class="form-control" style="width: 100%; padding: 8px 12px; border-radius: 8px; border: 1px solid var(--border); font-size:12.5px; font-weight:700; height: 38px;">
                        </div>
                        <button type="submit" class="btn-primary" style="height: 38px; padding: 0 14px; font-size: 12px; font-weight: 700; border-radius: 8px; flex-shrink:0;">
                            + Agregar
                        </button>
                    </form>

                    <div style="max-height: 160px; overflow-y: auto; border: 1px solid var(--border); border-radius: 8px; padding: 4px;">
                        @forelse($tiposAlerta as $tp)
                            <div style="display: flex; justify-content: space-between; align-items: center; padding: 7px 10px; border-bottom: 1px solid var(--border);">
                                <span style="font-weight: 700; font-size: 12.5px; color: var(--text);">{{ $tp->nombre }}</span>
                                <form action="{{ route('admin.tipos-alerta.destroy', $tp->id) }}" method="POST" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" style="background: none; border: none; color: var(--red); cursor: pointer; font-size: 12px; padding: 4px;" onclick="return confirm('¿Seguro que deseas eliminar este tipo de alerta?')" title="Eliminar tipo">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                </form>
                            </div>
                        @empty
                            <div style="text-align: center; color: var(--text3); font-size: 12px; padding: 12px;">
                                No hay tipos de alerta registrados.
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- GESTIÓN DE PUNTOS DE CONTROL PREDEFINIDOS --}}
            <div class="card">
                <div class="card-header" style="display:flex; justify-content:space-between; align-items:center;">
                    <span class="card-title" style="font-size: 13.5px; font-weight: 800;">
                        <i class="fa-solid fa-map-pin" style="color:var(--accent); margin-right:5px;"></i> Puntos de Control Predefinidos
                    </span>
                </div>
                <div class="card-body" style="padding: 16px 20px;">
                    <form action="{{ route('admin.puntos.store') }}" method="POST" style="margin-bottom: 14px; display: flex; gap: 8px;">
                        @csrf
                        <div class="field" style="margin: 0; flex: 1;">
                            <input type="text" name="nombre" required class="form-control" style="width: 100%; padding: 8px 12px; border-radius: 8px; border: 1px solid var(--border); font-size:12.5px; font-weight:700; height: 38px;">
                        </div>
                        <button type="submit" class="btn-primary" style="height: 38px; padding: 0 14px; font-size: 12px; font-weight: 700; border-radius: 8px; flex-shrink:0;">
                            + Agregar
                        </button>
                    </form>

                    <div style="max-height: 160px; overflow-y: auto; border: 1px solid var(--border); border-radius: 8px; padding: 4px;">
                        @forelse($puntos as $pt)
                            <div style="display: flex; justify-content: space-between; align-items: center; padding: 7px 10px; border-bottom: 1px solid var(--border);">
                                <span style="font-weight: 700; font-size: 12.5px; color: var(--text);">{{ $pt->nombre }}</span>
                                <form action="{{ route('admin.puntos.destroy', $pt->id) }}" method="POST" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" style="background: none; border: none; color: var(--red); cursor: pointer; font-size: 12px; padding: 4px;" onclick="return confirm('¿Seguro que deseas eliminar este punto de control?')" title="Eliminar punto">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                </form>
                            </div>
                        @empty
                            <div style="text-align: center; color: var(--text3); font-size: 12px; padding: 12px;">
                                No hay puntos registrados aún.
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

        </div>

        {{-- COLUMNA 2: ALERTAS GUARDADAS & HISTORIAL --}}
        <div style="display: flex; flex-direction: column; gap: 20px;">
            
            {{-- 1. ALERTAS GUARDADAS (LISTAS PARA GESTIONAR, EMITIR Y FINALIZAR) --}}
            <div class="card">
                <div class="card-header" style="display:flex; justify-content:space-between; align-items:center;">
                    <span class="card-title" style="font-size: 14.5px; font-weight: 800;">
                        <i class="fa-solid fa-layer-group" style="color:var(--accent); margin-right:6px;"></i> Alertas Guardadas
                    </span>
                    <span class="badge" style="background:var(--accent-l); color:var(--accent); font-weight:800; font-size:11px; padding:3px 8px; border-radius:6px;">
                        {{ $alertasGuardadas->count() }} configuradas
                    </span>
                </div>
                <div class="card-body" style="padding: 0;">
                    <div class="tbl-wrap">
                        <table class="tbl tbl-modern">
                            <thead>
                                <tr>
                                    <th>Alerta / Detalle</th>
                                    <th>Ubicación</th>
                                    <th style="text-align:center;">Visible para Conductor</th>
                                    <th>Estado en Ruta</th>
                                    <th class="col-actions" style="text-align: right;">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($alertasGuardadas as $alerta)
                                    @php
                                        $activaInstancia = \App\Models\AlertaOperativo::where('empresa_id', auth()->user()->empresa_id)
                                            ->where('titulo', $alerta->titulo)
                                            ->where('punto', $alerta->punto)
                                            ->where('estado', 'activo')
                                            ->where('expires_at', '>', now())
                                            ->first();
                                    @endphp
                                    <tr>
                                        <td>
                                            <div style="font-weight: 800; color: var(--text); font-size: 13px; display: flex; align-items: center; gap: 6px; flex-wrap: wrap;">
                                                <span class="pill blue" style="font-size: 9.5px; font-weight: 800; padding: 2px 6px;">
                                                    {{ strtoupper($alerta->tipo ?: 'GENERAL') }}
                                                </span>
                                                {{ $alerta->titulo }}
                                            </div>
                                            @if($alerta->mensaje)
                                                <div style="font-size: 11.5px; color: var(--text2); margin-top: 3px; max-width: 240px; line-height: 1.3;">
                                                    {{ $alerta->mensaje }}
                                                </div>
                                            @endif
                                        </td>
                                        <td style="font-weight: 700; font-size: 12px; color: var(--text2);">
                                            {{ $alerta->punto ?: 'Ubicación General' }}
                                        </td>
                                        <td style="text-align: center;">
                                            <form action="{{ route('admin.alertas.toggle-visibilidad', $alerta) }}" method="POST" style="margin: 0; display: inline-flex; align-items: center;">
                                                @csrf
                                                <label style="display: inline-flex; align-items: center; gap: 6px; cursor: pointer; font-size: 12px; font-weight: 700; color: {{ $alerta->visible_conductor ? 'var(--green)' : 'var(--text3)' }};" title="Marcar para que los conductores la puedan emitir desde su app">
                                                    <input type="checkbox" onchange="this.form.submit()" {{ $alerta->visible_conductor ? 'checked' : '' }} style="width: 16px; height: 16px; accent-color: var(--green); cursor: pointer;">
                                                    <span>{{ $alerta->visible_conductor ? 'Visible' : 'Oculto' }}</span>
                                                </label>
                                            </form>
                                        </td>
                                        <td>
                                            @if($activaInstancia)
                                                @php
                                                    $diffSec = now()->diffInSeconds($activaInstancia->expires_at, false);
                                                    $mins = floor($diffSec / 60);
                                                    $secs = $diffSec % 60;
                                                @endphp
                                                <span class="pill green" style="font-size: 10.5px; font-weight: 800; padding: 3px 8px; display: inline-flex; align-items: center; gap: 4px;">
                                                    <i class="fa-solid fa-circle-dot"></i> Activa ({{ sprintf("%02dm %02ds", $mins, $secs) }})
                                                </span>
                                            @else
                                                <span class="pill gray" style="font-size: 10.5px; font-weight: 700; padding: 3px 8px; display: inline-flex; align-items: center; gap: 4px;">
                                                    <i class="fa-solid fa-circle-pause"></i> Inactiva
                                                </span>
                                            @endif
                                        </td>
                                        <td class="col-actions" style="text-align: right; white-space: nowrap;">
                                            <div style="display: inline-flex; align-items: center; gap: 6px;">
                                                @if($activaInstancia)
                                                    {{-- Botón Finalizar --}}
                                                    <form action="{{ route('admin.alertas.finalizar', $activaInstancia->id) }}" method="POST" style="display: inline;">
                                                        @csrf
                                                        <button type="submit" class="btn-secondary" style="font-size: 11px; padding: 5px 10px; background: #fee2e2; color: #dc2626; border: 1px solid #fecaca; font-weight: 800; border-radius: 6px; cursor: pointer; display: inline-flex; align-items: center; gap: 4px;" title="Finalizar emisión activa">
                                                            <i class="fa-solid fa-stop"></i> Finalizar
                                                        </button>
                                                    </form>
                                                @else
                                                    {{-- Botón Emitir --}}
                                                    <form action="{{ route('admin.alertas.reemitir', $alerta) }}" method="POST" style="display: inline-flex; align-items: center; gap: 4px;">
                                                        @csrf
                                                        <input type="number" name="duracion_minutos" value="60" min="5" max="1440" style="width: 54px; font-size: 11px; padding: 3px 6px; border-radius: 6px; border: 1px solid var(--border); font-weight: 700; background: var(--bg); color: var(--text);" title="Minutos de duración">
                                                        <button type="submit" class="btn-primary" style="font-size: 11px; padding: 5px 10px; font-weight: 800; border-radius: 6px; background: #2563eb; display: inline-flex; align-items: center; gap: 4px; cursor: pointer;" title="Emitir alerta a la flota">
                                                            <i class="fa-solid fa-paper-plane"></i> Emitir
                                                        </button>
                                                    </form>
                                                @endif

                                                {{-- Botón Eliminar --}}
                                                <form action="{{ route('admin.alertas.destroy', $alerta) }}" method="POST" style="display: inline;" onsubmit="return confirm('¿Seguro que deseas eliminar esta alerta?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" style="background: none; border: none; color: var(--red); cursor: pointer; padding: 4px 6px; font-size: 12.5px;" title="Eliminar alerta">
                                                        <i class="fa-solid fa-trash-can"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" style="text-align:center; padding:35px; color:var(--text3); font-size:12.5px;">
                                            <div style="font-size:28px; margin-bottom:8px;"><i class="fa-solid fa-folder-open" style="opacity: 0.5;"></i></div>
                                            No hay alertas guardadas aún. Completa el formulario de la izquierda para emitir la primera.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- 2. HISTORIAL DE TODAS LAS ALERTAS EMITIDAS --}}
            <div class="card">
                <div class="card-header" style="display:flex; justify-content:space-between; align-items:center;">
                    <span class="card-title" style="font-size: 14px; font-weight: 800;">
                        <i class="fa-solid fa-clock-rotate-left" style="color:var(--accent); margin-right:6px;"></i> Historial de Alertas Emitidas
                    </span>
                    <span style="font-size: 11px; color: var(--text3); font-weight: 600;">
                        Registro cronológico de avisos emitidos a la flota
                    </span>
                </div>
                <div class="card-body" style="padding: 0;">
                    <div class="tbl-wrap">
                        <table class="tbl tbl-modern">
                            <thead>
                                <tr>
                                    <th>Alerta / Detalle</th>
                                    <th>Ubicación</th>
                                    <th>Emitido Por</th>
                                    <th>Fecha y Hora</th>
                                    <th style="text-align: right;">Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($historial as $item)
                                    <tr>
                                        <td>
                                            <div style="font-weight: 800; font-size: 12.5px; color: var(--text); display: flex; align-items: center; gap: 6px; flex-wrap: wrap;">
                                                <span class="pill blue" style="font-size: 9.5px; font-weight: 800; padding: 2px 6px;">
                                                    {{ strtoupper($item->tipo ?: 'GENERAL') }}
                                                </span>
                                                {{ $item->titulo }}
                                            </div>
                                            @if($item->mensaje)
                                                <div style="font-size: 11px; color: var(--text2); margin-top: 2px; max-width: 220px; line-height: 1.3;">
                                                    {{ $item->mensaje }}
                                                </div>
                                            @endif
                                        </td>
                                        <td style="font-weight: 700; font-size: 12px; color: var(--text2);">
                                            {{ $item->punto ?: 'Ubicación General' }}
                                        </td>
                                        <td style="font-size: 11.5px;">
                                            @if($item->conductor)
                                                <div style="display: flex; align-items: center; gap: 5px; font-weight: 700; color: #0284c7;">
                                                    <i class="fa-solid fa-id-card"></i> {{ $item->conductor->nombre_completo }}
                                                </div>
                                                <span style="font-size: 10px; color: var(--text3);">Conductor</span>
                                            @else
                                                <div style="display: flex; align-items: center; gap: 5px; font-weight: 700; color: var(--accent);">
                                                    <i class="fa-solid fa-user-shield"></i> {{ $item->user?->name ?? 'Administración' }}
                                                </div>
                                                <span style="font-size: 10px; color: var(--text3);">Admin</span>
                                            @endif
                                        </td>
                                        <td style="font-size: 11px; color: var(--text3);">
                                            <div style="font-weight: 700; color: var(--text2);">{{ $item->created_at->format('d/m/Y') }}</div>
                                            <div style="font-size: 10.5px;">{{ $item->created_at->format('h:i A') }}</div>
                                        </td>
                                        <td style="text-align: right;">
                                            @if($item->estado === 'activo' && $item->expires_at > now())
                                                <span class="pill green" style="font-size: 10px; font-weight: 800;">
                                                    <i class="fa-solid fa-circle-dot"></i> Activa
                                                </span>
                                            @elseif($item->estado === 'finalizado')
                                                <span class="pill gray" style="font-size: 10px; font-weight: 700;">
                                                    Finalizada
                                                </span>
                                            @else
                                                <span class="pill orange" style="font-size: 10px; font-weight: 700;">
                                                    Expirada
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" style="text-align:center; padding:30px; color:var(--text3); font-size:12px;">
                                            Sin historial de emisiones registrado.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>

    </div>

</div>
@endsection
