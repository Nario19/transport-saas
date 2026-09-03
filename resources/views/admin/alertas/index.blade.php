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

    <div style="display: grid; grid-template-columns: 1fr 1.6fr; gap: 24px; align-items: start;">
        
        {{-- COLUMNA 1: FORMULARIO REGISTRO & OPCIONES PREDEFINIDAS --}}
        <div style="display: flex; flex-direction: column; gap: 20px;">
            
            {{-- EMITIR ALERTA PERSONALIZADA --}}
            <div class="card">
                <div class="card-header" style="background: var(--bg2); border-bottom: 1px solid var(--border);">
                    <span class="card-title" style="font-size: 14px; font-weight: 800;">
                        <i class="fa-solid fa-paper-plane" style="color:var(--accent); margin-right:6px;"></i> Emitir Nueva Alerta
                    </span>
                </div>
                <div class="card-body" style="padding: 20px;">
                    <form action="{{ route('admin.alertas.store') }}" method="POST">
                        @csrf
                        
                        {{-- Título de la alerta --}}
                        <div class="field" style="margin-bottom: 16px;">
                            <label style="font-weight:700; font-size:13px; color:var(--text); margin-bottom:6px; display:block;">
                                Nombre / Título de la Alerta <span style="color:var(--red);">*</span>
                            </label>
                            <input type="text" name="titulo" required class="form-control" 
                                   placeholder="Ej: Operativo Policial SUTRAN / Desvío por Obras" 
                                   style="width: 100%; padding: 10px 12px; border-radius: 8px; border: 1px solid var(--border); font-size:13px; font-weight:700; color:var(--text); background:var(--bg);">
                        </div>

                        {{-- Tipo de Alerta & Duración --}}
                        <div style="display: grid; grid-template-columns: 1.1fr 0.9fr; gap: 12px; margin-bottom: 16px;">
                            <div class="field">
                                <label style="font-weight:700; font-size:12.5px; color:var(--text); margin-bottom:6px; display:block;">Tipo de Alerta</label>
                                <select name="tipo" class="form-control" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid var(--border); font-size:12.5px; font-weight:700; color:var(--text); background:var(--bg);">
                                    <option value="Operativo / Control">🚨 Operativo / Control</option>
                                    <option value="Desvío / Obras">🚧 Desvío / Obras</option>
                                    <option value="Urgente / Tránsito Cerrado">⚠️ Urgente / Tránsito Cerrado</option>
                                    <option value="Aviso General">ℹ️ Aviso General</option>
                                    @if(isset($tiposAlerta) && $tiposAlerta->count() > 0)
                                        <optgroup label="Mis Tipos Personalizados">
                                            @foreach($tiposAlerta as $t)
                                                <option value="{{ $t->nombre }}">🔔 {{ $t->nombre }}</option>
                                            @endforeach
                                        </optgroup>
                                    @endif
                                </select>
                            </div>
                            <div class="field">
                                <label style="font-weight:700; font-size:12.5px; color:var(--text); margin-bottom:6px; display:block;">Duración Activa</label>
                                <select name="duracion_minutos" class="form-control" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid var(--border); font-size:12.5px; font-weight:700; color:var(--text); background:var(--bg);">
                                    <option value="30">30 minutos</option>
                                    <option value="60" selected>1 hora</option>
                                    <option value="120">2 horas</option>
                                    <option value="240">4 horas</option>
                                    <option value="480">8 horas</option>
                                    <option value="1440">Todo el día (24h)</option>
                                </select>
                            </div>
                        </div>

                        {{-- Punto / Ubicación --}}
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

                        {{-- Mensaje / Descripción --}}
                        <div class="field" style="margin-bottom: 16px;">
                            <label style="font-weight:700; font-size:12.5px; color:var(--text); margin-bottom:6px; display:block;">
                                Mensaje / Indicaciones para el Conductor
                            </label>
                            <textarea name="mensaje" rows="2" class="form-control" 
                                      placeholder="Ej: Inspección de documentos, SOAT y revisión técnica. Manejar con precaución."
                                      style="width: 100%; padding: 10px 12px; border-radius: 8px; border: 1px solid var(--border); font-size:12.5px; color:var(--text); background:var(--bg); resize: vertical; font-family: inherit;"></textarea>
                        </div>

                        {{-- Switch de Visibilidad --}}
                        <div style="background: var(--bg); border: 1.5px solid var(--border); border-radius: 10px; padding: 12px 14px; margin-bottom: 20px; display: flex; align-items: center; gap: 12px; cursor: pointer;">
                            <input type="checkbox" id="visible_conductor" name="visible_conductor" value="1" checked style="width: 18px; height: 18px; cursor: pointer; accent-color: var(--green);">
                            <label for="visible_conductor" style="font-weight: 700; font-size: 13px; color: var(--text); cursor: pointer; margin: 0; user-select: none;">
                                Mostrar en la pantalla de los conductores
                                <span style="display: block; font-size: 11px; font-weight: normal; color: var(--text3); margin-top: 1px;">
                                    Si desmarcas este check, la alerta quedará guardada solo en administración y oculta para los choferes.
                                </span>
                            </label>
                        </div>

                        <button type="submit" class="btn-primary" style="width: 100%; height: 46px; display: flex; align-items: center; justify-content: center; gap: 8px; font-size: 14px; font-weight: 800; border-radius: 10px;">
                            <i class="fa-solid fa-bullhorn"></i> Emitir Alerta a la Flota
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
                            <input type="text" name="nombre" placeholder="Ej: Continental / SUTRAN / Fiscalización" required class="form-control" style="width: 100%; padding: 8px 12px; border-radius: 8px; border: 1px solid var(--border); font-size:12.5px; font-weight:700; height: 38px;">
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
                                No has agregado tipos personalizados aún.
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
                            <input type="text" name="nombre" placeholder="Ej: Óvalo Sumar / Quebrada" required class="form-control" style="width: 100%; padding: 8px 12px; border-radius: 8px; border: 1px solid var(--border); font-size:12.5px; font-weight:700; height: 38px;">
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

        {{-- COLUMNA 2: LISTADOS --}}
        <div style="display: flex; flex-direction: column; gap: 20px;">
            
            {{-- ALERTAS ACTIVAS --}}
            <div class="card">
                <div class="card-header" style="display:flex; justify-content:space-between; align-items:center;">
                    <span class="card-title" style="font-size: 14.5px; font-weight: 800;">
                        <i class="fa-solid fa-triangle-exclamation" style="color:var(--red); margin-right:6px;"></i> Alertas Activas en Ruta
                    </span>
                    <span class="badge" style="background:var(--red-l); color:var(--red); font-weight:800; font-size:11px; padding:3px 8px; border-radius:6px;">{{ $activas->count() }} activas</span>
                </div>
                <div class="card-body" style="padding: 0;">
                    <div class="tbl-wrap">
                        <table class="tbl tbl-modern">
                            <thead>
                                <tr>
                                    <th>Alerta / Detalle</th>
                                    <th>Ubicación</th>
                                    <th style="text-align:center;">Visibilidad Chofer</th>
                                    <th>Expira en</th>
                                    <th class="col-actions">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($activas as $alerta)
                                    <tr>
                                        <td>
                                            <div style="font-weight: 800; color: var(--text); font-size: 13.5px; display: flex; align-items: center; gap: 6px; flex-wrap: wrap;">
                                                <span class="pill" style="background: #fee2e2; color: #991b1b; font-size: 10px; font-weight: 800; padding: 2px 6px;">
                                                    {{ strtoupper($alerta->tipo ?: 'ALERTA') }}
                                                </span>
                                                {{ $alerta->titulo ?: 'Control / Operativo' }}
                                            </div>
                                            @if($alerta->mensaje)
                                                <div style="font-size: 11.5px; color: var(--text2); margin-top: 3px; max-width: 240px; line-height: 1.3;">
                                                    {{ $alerta->mensaje }}
                                                </div>
                                            @endif
                                            <div style="font-size: 10.5px; color: var(--text3); margin-top: 3px;">
                                                @if($alerta->conductor)
                                                    Conductor: <b>{{ $alerta->conductor->nombre_completo }}</b>
                                                @else
                                                    Admin: <b>{{ $alerta->user?->name ?? 'Sistema' }}</b>
                                                @endif
                                                • {{ $alerta->created_at->format('h:i A') }}
                                            </div>
                                        </td>
                                        <td style="font-weight: 700; font-size: 12.5px; color: var(--text2);">
                                            {{ $alerta->punto ?: 'Ubicación General' }}
                                        </td>
                                        <td style="text-align: center;">
                                            <form action="{{ route('admin.alertas.toggle-visibilidad', $alerta) }}" method="POST" style="display: inline;">
                                                @csrf
                                                @if($alerta->visible_conductor)
                                                    <button type="submit" class="pill green" style="border: none; cursor: pointer; font-size: 10.5px; font-weight: 800; padding: 4px 8px; display: inline-flex; align-items: center; gap: 4px;" title="Clic para ocultar a los conductores">
                                                        <i class="fa-solid fa-eye"></i> Visible
                                                    </button>
                                                @else
                                                    <button type="submit" class="pill gray" style="border: none; cursor: pointer; font-size: 10.5px; font-weight: 800; padding: 4px 8px; display: inline-flex; align-items: center; gap: 4px;" title="Clic para mostrar a los conductores">
                                                        <i class="fa-solid fa-eye-slash"></i> Oculta
                                                    </button>
                                                @endif
                                            </form>
                                        </td>
                                        <td style="font-family: monospace; font-weight: 800; font-size: 12px;">
                                            @php
                                                $diffSeconds = now()->diffInSeconds($alerta->expires_at, false);
                                                if ($diffSeconds > 0) {
                                                    $mins = floor($diffSeconds / 60);
                                                    $secs = $diffSeconds % 60;
                                                    echo sprintf("%02dm %02ds", $mins, $secs);
                                                } else {
                                                    echo 'Expirando...';
                                                }
                                            @endphp
                                        </td>
                                        <td class="col-actions">
                                            <form action="{{ route('admin.alertas.finalizar', $alerta) }}" method="POST" style="display: inline;">
                                                @csrf
                                                <button type="submit" class="btn-secondary" style="font-size: 11px; padding: 6px 10px; background: var(--green); color: white; border: none; font-weight: 700; border-radius: 6px; cursor: pointer;" title="Finalizar alerta">
                                                    <i class="fa-solid fa-check-double"></i> Finalizar
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" style="text-align:center; padding:40px; color:var(--text3);">
                                            <div style="font-size:32px; margin-bottom:10px;">🛡️</div>
                                            <div style="font-weight:700; font-size:14px; color:var(--text);">No hay reportes de operativos activos</div>
                                            <div style="font-size:11px; margin-top:2px;">Todo parece estar libre en la ruta por ahora.</div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- HISTORIAL RECIENTE --}}
            <div class="card">
                <div class="card-header">
                    <span class="card-title" style="font-size: 13.5px; font-weight: 800;">
                        <i class="fa-solid fa-clock-rotate-left" style="color:var(--text3); margin-right:5px;"></i> Historial Reciente de Alertas
                    </span>
                </div>
                <div class="card-body" style="padding: 0;">
                    <div class="tbl-wrap">
                        <table class="tbl tbl-modern">
                            <thead>
                                <tr>
                                    <th>Alerta</th>
                                    <th>Ubicación</th>
                                    <th>Emisor</th>
                                    <th>Fecha y Hora</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($historial as $alerta)
                                    <tr>
                                        <td>
                                            <div style="font-weight: 700; font-size: 12.5px;">{{ $alerta->titulo ?: 'Control / Operativo' }}</div>
                                            @if($alerta->mensaje)
                                                <div style="font-size: 11px; color: var(--text3); margin-top: 1px;">{{ Str::limit($alerta->mensaje, 40) }}</div>
                                            @endif
                                        </td>
                                        <td style="font-weight: 600; font-size: 12px;">{{ $alerta->punto ?: 'General' }}</td>
                                        <td style="font-size: 11.5px;">
                                            @if($alerta->conductor)
                                                Conductor: {{ $alerta->conductor->nombre_completo }}
                                            @else
                                                Admin: {{ $alerta->user?->name ?? 'Sistema' }}
                                            @endif
                                        </td>
                                        <td style="font-size: 11.5px;">{{ $alerta->created_at->format('d/m/Y h:i A') }}</td>
                                        <td>
                                            @if($alerta->estado === 'finalizado')
                                                <span class="pill green" style="font-size: 10px; font-weight:800; padding:2px 6px;">Retirado</span>
                                            @else
                                                <span class="pill gray" style="font-size: 10px; font-weight:800; padding:2px 6px;">Expirado</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" style="text-align:center; padding:30px; color:var(--text3); font-size:12px;">
                                            Sin registros anteriores.
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
