@extends('layouts.propietario')

@section('title', 'Inicio')

@section('content')

    {{-- 1. IDENTIFICACIÓN DE LA UNIDAD / PROPIETARIO --}}
    <div class="card" style="background: linear-gradient(135deg, #1e3a8a, #1d4ed8); color: #ffffff; border: none; padding: 18px; border-radius: 16px; margin-bottom: 14px;">
        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
            <div>
                <div style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.8px; opacity: 0.85; font-weight: 700;">Panel del Propietario</div>
                <div style="font-size: 18px; font-weight: 800; margin-top: 2px;">{{ $propietario->nombre_completo }}</div>
                <div style="font-size: 12px; opacity: 0.85; margin-top: 2px;">DNI: {{ $propietario->dni ?? '---' }}</div>
            </div>
            <div style="background: rgba(255, 255, 255, 0.2); padding: 6px 12px; border-radius: 10px; text-align: center;">
                <div style="font-size: 9px; text-transform: uppercase; font-weight: 800; opacity: 0.9;">Condición</div>
                <div style="font-size: 12px; font-weight: 800;">
                    {{ $propietario->es_socio ? 'Socio' : 'Persona Normal' }}
                </div>
            </div>
        </div>

        @if($vehiculos->isNotEmpty())
            <div style="margin-top: 14px; padding-top: 12px; border-top: 1px solid rgba(255, 255, 255, 0.15); display: flex; flex-wrap: wrap; gap: 8px;">
                @foreach($vehiculos as $v)
                    <div style="background: rgba(255, 255, 255, 0.15); padding: 5px 10px; border-radius: 8px; font-size: 12px; font-weight: 700; display: flex; align-items: center; gap: 6px;">
                        <i class="fa-solid fa-van-shuttle"></i>
                        <span>#{{ $v->numero_flota ?? '—' }} ({{ $v->placa_form }})</span>
                        @if($v->conductor)
                            <span style="font-size: 10px; opacity: 0.8; font-weight: 400;">• Chofer: {{ $v->conductor->nombre }}</span>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- 2. MÉTRICAS DE VUELTAS (HOY / DÍAS TRABAJADOS / MES) --}}
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
            <div class="stat-lbl">Vueltas Mes</div>
        </div>
    </div>

    {{-- 3. ESTADO FINANCIERO / TRIBUTOS --}}
    <div class="card">
        <div class="card-header">
            <span class="card-title"><i class="fa-solid fa-receipt" style="color: var(--accent); margin-right: 6px;"></i> Estado de Tributos</span>
            <a href="{{ route('propietario.tributos') }}" style="font-size: 11px; font-weight: 700; color: var(--accent); text-decoration: none;">Ver todos &raquo;</a>
        </div>
        <div class="card-body">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                <div>
                    <div style="font-size: 11px; color: var(--text3); font-weight: 600;">TRIBUTO HOY</div>
                    @if($tributosHoy->isEmpty())
                        <span class="pill gray">Sin generar</span>
                    @else
                        @php $tHoy = $tributosHoy->first(); @endphp
                        @if($tHoy->estado === 'pagado')
                            <span class="pill green"><i class="fa-solid fa-circle-check"></i> Pagado (S/ {{ number_format($tHoy->monto, 2) }})</span>
                        @elseif($tHoy->estado === 'exonerado')
                            <span class="pill gold"><i class="fa-solid fa-shield"></i> Exonerado</span>
                        @else
                            <span class="pill red"><i class="fa-solid fa-circle-exclamation"></i> Pendiente (S/ {{ number_format($tHoy->monto, 2) }})</span>
                        @endif
                    @endif
                </div>
                <div style="text-align: right;">
                    <div style="font-size: 11px; color: var(--text3); font-weight: 600;">DEUDA ACUMULADA</div>
                    <div style="font-size: 17px; font-weight: 800; color: {{ $deudaTributos > 0 ? 'var(--red)' : 'var(--green)' }};">
                        S/ {{ number_format($deudaTributos, 2) }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- 4. ALERTAS DE DOCUMENTOS POR VENCER --}}
    @if(count($alertasDocumentos) > 0)
        <div class="card" style="border-left: 4px solid var(--red);">
            <div class="card-header" style="background: #fff5f5;">
                <span class="card-title" style="color: var(--red);">
                    <i class="fa-solid fa-triangle-exclamation"></i> Documentos Próximos a Vencer
                </span>
            </div>
            <div class="card-body" style="padding: 12px;">
                @foreach($alertasDocumentos as $doc)
                    <div style="display: flex; justify-content: space-between; align-items: center; padding: 6px 0; border-bottom: 1px solid var(--border); font-size: 13px;">
                        <div>
                            <b>{{ $doc['vehiculo'] }}</b> — {{ $doc['tipo'] }}
                        </div>
                        <div>
                            <span class="pill {{ $doc['vencido'] ? 'red' : 'gold' }}">
                                {{ $doc['vencido'] ? 'Vencido' : 'Vence' }}: {{ $doc['fecha'] }}
                            </span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- 5. SANCIONES PENDIENTES --}}
    @if($sancionesPendientes->isNotEmpty())
        <div class="card" style="border-left: 4px solid var(--orange);">
            <div class="card-header">
                <span class="card-title" style="color: var(--orange);">
                    <i class="fa-solid fa-circle-exclamation"></i> Sanciones Pendientes ({{ $sancionesPendientes->count() }})
                </span>
                <a href="{{ route('propietario.sanciones') }}" style="font-size: 11px; font-weight: 700; color: var(--accent); text-decoration: none;">Ver detalle</a>
            </div>
            <div class="card-body" style="padding: 12px;">
                @foreach($sancionesPendientes as $s)
                    <div style="display: flex; justify-content: space-between; align-items: center; padding: 6px 0; border-bottom: 1px solid var(--border); font-size: 13px;">
                        <div>
                            <div style="font-weight: 700;">{{ $s->motivo ?? 'Infracción' }}</div>
                            <div style="font-size: 11px; color: var(--text3);">Unidad: {{ $s->vehiculo?->placa }}</div>
                        </div>
                        <span class="pill red">S/ {{ number_format($s->monto, 2) }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- 6. ALERTAS DE OPERATIVOS EN RUTA --}}
    @if($alertasOperativos->isNotEmpty())
        <div class="card">
            <div class="card-header">
                <span class="card-title"><i class="fa-solid fa-bullhorn" style="color: var(--accent); margin-right: 6px;"></i> Alertas en Ruta</span>
                <span class="pill green">{{ $alertasOperativos->count() }} activas</span>
            </div>
            <div class="card-body" style="padding: 10px;">
                @foreach($alertasOperativos as $alerta)
                    <div style="padding: 12px; background: #f8fafc; border-radius: 10px; margin-bottom: 8px; border-left: 4px solid var(--accent); border: 1px solid #e2e8f0; border-left-width: 4px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; gap: 8px; flex-wrap: wrap;">
                            <div style="font-weight: 800; font-size: 13.5px; color: var(--text);">{{ $alerta->titulo }}</div>
                            @if($alerta->tipo)
                                <span style="font-size: 10.5px; font-weight: 800; background: #e0f2fe; color: #0369a1; padding: 2px 7px; border-radius: 6px; text-transform: uppercase; letter-spacing: 0.3px;">
                                    <i class="fa-solid fa-tag" style="font-size: 9px; margin-right: 2px;"></i>{{ $alerta->tipo }}
                                </span>
                            @endif
                        </div>
                        @if($alerta->punto && $alerta->punto !== 'Ubicación General')
                            <div style="font-size: 11.5px; font-weight: 700; color: #d97706; margin-top: 3px;">
                                <i class="fa-solid fa-location-dot" style="font-size: 10px;"></i> Ubicación: {{ $alerta->punto }}
                            </div>
                        @endif
                        @if($alerta->mensaje)
                            <div style="font-size: 12px; color: var(--text2); margin-top: 3px; line-height: 1.35;">{{ $alerta->mensaje }}</div>
                        @endif
                        <div style="font-size: 10.5px; color: var(--text3); margin-top: 5px;">
                            <i class="fa-regular fa-clock"></i> {{ $alerta->created_at->diffForHumans() }}
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

@endsection
