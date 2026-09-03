@extends('layouts.propietario')

@section('title', 'Vueltas de la Flota')

@section('content')

    {{-- 1. FILTRO DE FECHA --}}
    <div class="card">
        <div class="card-body" style="padding: 12px;">
            <form method="GET" action="{{ route('propietario.vueltas') }}" style="display: flex; gap: 8px; align-items: center;">
                <input type="date" name="fecha" value="{{ $fecha }}" class="form-control"
                       style="flex: 1; padding: 9px 12px; border: 1px solid var(--border); border-radius: 8px; font-size: 13px; font-family: inherit; font-weight: 600;"
                       onchange="this.form.submit()">
                <a href="{{ route('propietario.vueltas') }}" class="btn-secondary" style="padding: 9px 12px;">Hoy</a>
            </form>
        </div>
    </div>

    {{-- 2. CONTADORES CLAVE (HOY, DÍAS TRABAJADOS, TOTAL MES) --}}
    <div class="stat-grid">
        <div class="stat-card">
            <div class="stat-val">{{ $vueltasHoy }}</div>
            <div class="stat-lbl">Vueltas Hoy</div>
        </div>
        <div class="stat-card">
            <div class="stat-val" style="color: var(--gold);">{{ $diasTrabajadosMes }}</div>
            <div class="stat-lbl">Días Trab. Mes</div>
        </div>
        <div class="stat-card">
            <div class="stat-val" style="color: var(--green);">{{ $vueltasMes }}</div>
            <div class="stat-lbl">Vueltas Flota Mes</div>
        </div>
    </div>

    {{-- 3. LISTADO DE VUELTAS DE LA FECHA --}}
    <div class="card">
        <div class="card-header">
            <span class="card-title">
                Vueltas Realizadas ({{ \Carbon\Carbon::parse($fecha)->format('d/m/Y') }})
            </span>
            <span class="pill blue">{{ count($vueltas) }} registradas</span>
        </div>
        <div class="card-body" style="padding: 12px;">
            @forelse($vueltas as $vuelta)
                @php
                    $badge = $vuelta->badge_estado;
                @endphp
                <div style="background: #ffffff; border: 1px solid var(--border); border-radius: 12px; padding: 14px; margin-bottom: 12px; box-shadow: var(--shadow); display: flex; flex-direction: column; gap: 10px;">
                    
                    {{-- Cabecera Vuelta: Número + Ruta + Placa --}}
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <div style="width: 28px; height: 28px; background: var(--accent); border-radius: 6px; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 13px; color: #fff; flex-shrink: 0;">
                                {{ $vuelta->numero_vuelta }}
                            </div>
                            <div>
                                <div style="font-weight: 700; font-size: 14px; color: var(--text);">
                                    {{ $vuelta->ruta?->nombre ?? 'Sin ruta' }}
                                </div>
                                @if($vuelta->conductor)
                                    <div style="font-size: 11px; color: var(--text3);">
                                        <i class="fa-solid fa-id-card"></i> Chofer: {{ $vuelta->conductor->nombre_completo }}
                                    </div>
                                @endif
                            </div>
                        </div>
                        <div style="text-align: right;">
                            <span class="pill gray" style="font-weight: 800;">
                                #{{ $vuelta->vehiculo?->numero_flota ?? '—' }} ({{ $vuelta->vehiculo?->placa_form ?? '—' }})
                            </span>
                        </div>
                    </div>

                    {{-- Origen -> Destino --}}
                    @if ($vuelta->ruta)
                        <div style="font-size: 12px; color: var(--text2); display: flex; align-items: center; gap: 6px; background: #f8fafc; padding: 6px 10px; border-radius: 6px;">
                            <i class="fa-solid fa-route" style="color: var(--accent);"></i>
                            <span>{{ $vuelta->paraderoSalida?->nombre ?? $vuelta->ruta->origen }} ➔ {{ $vuelta->paraderoLlegada?->nombre ?? $vuelta->ruta->destino }}</span>
                        </div>
                    @endif

                    {{-- Horas Salida / Llegada --}}
                    <div style="display: flex; justify-content: space-between; align-items: center; background: #f8fafc; padding: 8px 12px; border-radius: 8px; border: 1px solid var(--border);">
                        <div style="display: flex; flex-direction: column; gap: 2px;">
                            <span style="font-size: 10px; color: var(--text3); text-transform: uppercase; font-weight: 700;">Salida</span>
                            <span style="font-size: 13px; font-weight: 800; color: var(--text); font-family: monospace;">
                                {{ $vuelta->hora_salida ? \Carbon\Carbon::parse($vuelta->hora_salida)->format('h:i A') : '--:--' }}
                            </span>
                        </div>
                        <div style="height: 18px; width: 1px; background: var(--border);"></div>
                        <div style="display: flex; flex-direction: column; gap: 2px; text-align: right;">
                            <span style="font-size: 10px; color: var(--text3); text-transform: uppercase; font-weight: 700;">Llegada</span>
                            <span style="font-size: 13px; font-weight: 800; color: var(--text); font-family: monospace;">
                                {{ $vuelta->hora_llegada ? \Carbon\Carbon::parse($vuelta->hora_llegada)->format('h:i A') : '--:--' }}
                            </span>
                        </div>
                    </div>

                    {{-- Duración y Badge de Estado --}}
                    <div style="display: flex; align-items: center; justify-content: space-between; font-size: 12px; padding-top: 4px;">
                        <div>
                            @if($vuelta->hora_llegada)
                                @php
                                    $sec = \Carbon\Carbon::parse($vuelta->hora_salida)->diffInSeconds(\Carbon\Carbon::parse($vuelta->hora_llegada));
                                    $hh = floor($sec / 3600);
                                    $mm = floor(($sec % 3600) / 60);
                                    $ss = $sec % 60;
                                    $dur = ($hh > 0 ? "{$hh}h " : "") . "{$mm}m {$ss}s";
                                @endphp
                                <span style="color: var(--text2); font-weight: 600;">
                                    <i class="fa-regular fa-clock" style="color: var(--accent);"></i> {{ $dur }}
                                </span>
                            @else
                                <span style="color: var(--green); font-weight: 700;">
                                    <i class="fa-solid fa-spinner fa-spin"></i> En ruta...
                                </span>
                            @endif
                        </div>

                        {{-- Badge con Código de Colores --}}
                        <div>
                            <span style="font-size: 11px; font-weight: 800; padding: 4px 10px; border-radius: 99px; background: {{ $badge['bg'] }}; color: {{ $badge['color'] }}; border: 1.2px solid {{ $badge['border'] }}; display: inline-block;">
                                {{ $badge['label'] }}
                            </span>
                        </div>
                    </div>

                </div>
            @empty
                <div class="empty-state">
                    <i class="fa-solid fa-road" style="font-size: 28px; margin-bottom: 8px; display: block; opacity: 0.5;"></i>
                    No hay vueltas registradas para esta fecha.
                </div>
            @endforelse
        </div>
    </div>

@endsection
