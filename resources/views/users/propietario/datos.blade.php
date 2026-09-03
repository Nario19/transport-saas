@extends('layouts.propietario')
@section('title', 'Mi Flota')

@section('content')

    @php 
        $primerVehiculo = $vehiculos->first(); 
    @endphp

    {{-- Hero - Centrado en el Vehículo / Flota --}}
    <div class="conductor-hero"
        style="flex-direction:column; text-align:center; padding:24px 20px; background:linear-gradient(135deg, #1e293b 0%, #0f172a 100%); color:white; border-bottom:none; margin: -16px -16px 0 -16px; border-radius: 0 0 20px 20px;">
        <div class="conductor-av"
            style="width:64px; height:64px; font-size:26px; margin:0 auto 10px; background:rgba(255,255,255,0.1); border:2px solid rgba(255,255,255,0.2); box-shadow:0 4px 12px rgba(0,0,0,0.1); display: flex; align-items: center; justify-content: center; border-radius: 50%;">
            <i class="fa-solid fa-car-side" style="color: #60a5fa;"></i>
        </div>
        <div class="conductor-hero-name" style="font-size:20px; font-weight:800; letter-spacing: 0.3px;">
            {{ $primerVehiculo ? $primerVehiculo->placa_form : 'Mi Flota' }}
        </div>
        <div class="conductor-hero-sub" style="opacity:0.85; font-size:12.5px; margin-top:4px;">
            @if($primerVehiculo)
                Flota #{{ $primerVehiculo->numero_flota ?? 'S/N' }} • {{ $propietario->nombre_completo }}
            @else
                {{ $propietario->nombre_completo }}
            @endif
        </div>
    </div>

    <div style="margin-top:-14px; padding:0 4px;">

        @forelse($vehiculos as $vehiculo)
            @if($vehiculos->count() > 1)
                <div style="font-size: 13px; font-weight: 800; color: var(--accent); margin: 18px 0 8px 4px; display: flex; align-items: center; gap: 6px;">
                    <i class="fa-solid fa-car"></i> Unidad #{{ $vehiculo->numero_flota }} ({{ $vehiculo->placa_form }})
                </div>
            @endif

            {{-- 1. Especificaciones de la Flota --}}
            <div class="card" style="margin-bottom: 14px; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.05);">
                <div class="card-header" style="background:#f8fafc; border-bottom:1px solid #f1f5f9; padding: 12px 16px; display: flex; align-items: center; justify-content: space-between;">
                    <span class="card-title" style="font-size:13.5px; color:#1e293b; font-weight: 800; display: flex; align-items: center; gap: 6px;">
                        <i class="fa-solid fa-car" style="color: var(--accent);"></i> Especificaciones de la Flota
                    </span>
                    <span class="pill {{ $vehiculo->estado === 'activo' ? 'green' : 'red' }}" style="font-size: 10px;">
                        {{ strtoupper($vehiculo->estado) }}
                    </span>
                </div>
                <div class="card-body" style="padding: 14px;">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px; font-size: 12px;">
                        <div><span style="color: var(--text3);">Padrón / Nro. Flota:</span> <b style="color: #2563eb;">#{{ $vehiculo->numero_flota ?? '—' }}</b></div>
                        <div><span style="color: var(--text3);">Marca / Modelo:</span> <b>{{ $vehiculo->marca }} {{ $vehiculo->modelo }} ({{ $vehiculo->anio }})</b></div>
                        <div><span style="color: var(--text3);">Color:</span> <b>{{ $vehiculo->color ?? '—' }}</b></div>
                        <div><span style="color: var(--text3);">Número de Motor:</span> <b style="font-family: monospace;">{{ $vehiculo->numero_motor ?? '—' }}</b></div>
                        <div style="grid-column: span 2;"><span style="color: var(--text3);">Número de Chasis:</span> <b style="font-family: monospace;">{{ $vehiculo->numero_chasis ?? '—' }}</b></div>
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
                <div class="card-body" style="padding: 14px;">
                    <form action="{{ route('propietario.vehiculos.update-documentos', $vehiculo->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div style="display: flex; flex-direction: column; gap: 12px;">
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
                                <input type="date" name="soat_vence" value="{{ old('soat_vence', $vehiculo->soat_vence ? \Carbon\Carbon::parse($vehiculo->soat_vence)->format('Y-m-d') : '') }}" style="width: 100%; border: 1px solid var(--border); border-radius: 8px; padding: 8px 12px; font-family: inherit; font-size: 13px; font-weight: 600; color: var(--text); background: white;">
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
                                <input type="date" name="rev_tecnica_vence" value="{{ old('rev_tecnica_vence', $vehiculo->rev_tecnica_vence ? \Carbon\Carbon::parse($vehiculo->rev_tecnica_vence)->format('Y-m-d') : '') }}" style="width: 100%; border: 1px solid var(--border); border-radius: 8px; padding: 8px 12px; font-family: inherit; font-size: 13px; font-weight: 600; color: var(--text); background: white;">
                            </div>

                            {{-- Tarjeta de Propiedad --}}
                            @if($vehiculo->tarjeta_prop_vence)
                                <div style="display: flex; justify-content: space-between; align-items: center; padding: 8px 10px; background: #f8fafc; border-radius: 6px; font-size: 12px;">
                                    <span style="color: var(--text2);"><i class="fa-solid fa-id-card" style="color: #64748b;"></i> Tarjeta Propiedad:</span>
                                    <b>{{ $vehiculo->tarjeta_prop_vence->format('d/m/Y') }}</b>
                                </div>
                            @endif

                            <button type="submit" class="btn btn-primary" style="padding: 10px; font-size: 13px; font-weight: 700; border-radius: 8px; display: flex; align-items: center; justify-content: center; gap: 6px; margin-top: 4px; background: var(--accent); color: #fff; border: none; cursor: pointer;">
                                <i class="fa-solid fa-floppy-disk"></i> Guardar Vencimientos
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- 3. Personal Asignado (Conductor de la Unidad) --}}
            <div class="card" style="margin-bottom: 14px; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.05);">
                <div class="card-header" style="background:#f8fafc; border-bottom:1px solid #f1f5f9; padding: 12px 16px; display: flex; align-items: center;">
                    <span class="card-title" style="font-size:13.5px; color:#1e293b; font-weight: 800; display: flex; align-items: center; gap: 6px;">
                        <i class="fa-solid fa-user-tie" style="color: var(--accent);"></i> Personal de Conducción
                    </span>
                </div>
                <div class="card-body" style="padding: 14px; display: flex; flex-direction: column; gap: 10px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; padding-bottom: 8px; border-bottom: 1px solid #f1f5f9;">
                        <span style="font-size: 12px; color: var(--text3);">Nombre:</span>
                        <b style="font-size: 13px; color: var(--text);">{{ $vehiculo->conductor ? $vehiculo->conductor->nombre_completo : 'Sin conductor asignado' }}</b>
                    </div>
                    @if($vehiculo->conductor)
                        <div style="display: flex; justify-content: space-between; align-items: center; padding-bottom: 8px; border-bottom: 1px solid #f1f5f9;">
                            <span style="font-size: 12px; color: var(--text3);">DNI:</span>
                            <b style="font-size: 13px; font-family: monospace;">{{ $vehiculo->conductor->dni ?? '—' }}</b>
                        </div>
                        <div style="display: flex; justify-content: space-between; align-items: center; padding-bottom: 8px; border-bottom: 1px solid #f1f5f9;">
                            <span style="font-size: 12px; color: var(--text3);">Licencia:</span>
                            <b style="font-size: 13px;">{{ $vehiculo->conductor->tipo_licencia ?? '—' }}</b>
                        </div>
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span style="font-size: 12px; color: var(--text3);">Contacto Chofer:</span>
                            @if($vehiculo->conductor->telefono)
                                <a href="tel:{{ $vehiculo->conductor->telefono }}" style="font-weight: 700; color: #2563eb; text-decoration: none; font-size: 13px; display: flex; align-items: center; gap: 4px;">
                                    <i class="fa-solid fa-phone"></i> {{ $vehiculo->conductor->telefono }}
                                </a>
                            @else
                                <span style="color: var(--text3); font-size: 12px;">—</span>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        @empty
            <div class="card" style="border:2px dashed #e2e8f0; background:transparent; text-align:center; padding:30px 20px; margin-bottom: 14px;">
                <div style="font-size:32px; margin-bottom:10px; color: var(--text3);"><i class="fa-solid fa-car"></i></div>
                <div style="font-size:14px; color:#64748b; font-weight:600;">Sin flota asignada</div>
                <div style="font-size:12px; color:#94a3b8; margin-top:4px;">No hay una flota vinculada a esta cuenta.</div>
            </div>
        @endforelse

        {{-- 4. SECCIÓN MONTO DE INGRESO Y LAS 3 CUOTAS --}}
        <div class="card" style="margin-bottom: 24px; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.05); border: 1.5px solid var(--gold-l);">
            <div class="card-header" style="background: var(--gold-l); border-bottom: 1px solid #fde68a; padding: 12px 16px; display: flex; align-items: center; justify-content: space-between;">
                <span class="card-title" style="font-size:13.5px; color: #92400e; font-weight: 800; display: flex; align-items: center; gap: 6px;">
                    <i class="fa-solid fa-money-bill-transfer"></i> Monto de Ingreso a la Empresa
                </span>
                <span class="pill {{ $propietario->es_socio ? 'gold' : ($propietario->monto_ingreso_deuda > 0 ? 'red' : 'green') }}" style="font-size: 10px;">
                    {{ $propietario->estado_ingreso }}
                </span>
            </div>
            <div class="card-body" style="padding: 14px;">
                @if($propietario->es_socio)
                    <div style="background: #fef3c7; border: 1px solid #fde68a; border-radius: 10px; padding: 14px; text-align: center;">
                        <i class="fa-solid fa-award" style="font-size: 32px; color: var(--gold); margin-bottom: 6px;"></i>
                        <div style="font-weight: 800; font-size: 14px; color: #92400e;">Socio de la Empresa</div>
                        <div style="font-size: 11.5px; color: #b45309; margin-top: 2px;">
                            Como socio registrado estás 100% exonerado del pago de monto de ingreso.
                        </div>
                    </div>
                @else
                    @php
                        $deuda = $propietario->monto_ingreso_deuda;
                        $totalAbonado = $propietario->monto_ingreso_total;
                        $totalObligado = $propietario->total_ingreso_obligado;
                        $porcentaje = $totalObligado > 0 ? min(100, round(($totalAbonado / $totalObligado) * 100)) : 100;
                    @endphp

                    {{-- Resumen Consolidado --}}
                    <div style="background: #f8fafc; padding: 12px; border-radius: 10px; margin-bottom: 12px; border: 1px solid var(--border);">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                            <div>
                                <div style="font-size: 10px; color: var(--text3); font-weight: 700; text-transform: uppercase;">Total Abonado</div>
                                <div style="font-size: 18px; font-weight: 800; color: var(--green);">
                                    S/ {{ number_format($totalAbonado, 2) }}
                                </div>
                            </div>
                            <div style="text-align: right;">
                                <div style="font-size: 10px; color: var(--text3); font-weight: 700; text-transform: uppercase;">Saldo Pendiente</div>
                                <div style="font-size: 18px; font-weight: 800; color: {{ $deuda > 0 ? 'var(--red)' : 'var(--green)' }};">
                                    S/ {{ number_format($deuda, 2) }}
                                </div>
                            </div>
                        </div>

                        {{-- Barra de Progreso --}}
                        <div style="background: #e2e8f0; border-radius: 99px; height: 7px; overflow: hidden;">
                            <div style="background: {{ $deuda > 0 ? 'var(--accent)' : 'var(--green)' }}; width: {{ $porcentaje }}%; height: 100%; border-radius: 99px; transition: width 0.3s;"></div>
                        </div>
                        <div style="display: flex; justify-content: space-between; font-size: 10px; color: var(--text3); margin-top: 4px; font-weight: 600;">
                            <span>{{ $porcentaje }}% abonado</span>
                            <span>Total Obligado: S/ {{ number_format($totalObligado, 2) }}</span>
                        </div>
                    </div>

                    @if($vehiculos->isNotEmpty())
                        @foreach($vehiculos as $v)
                            @php
                                $montoInicial = ($v->monto_inicial > 0 || $v->fecha_monto_inicial) ? $v->monto_inicial : ($propietario->monto_inicial ?? 0);
                                $fechaMontoInicial = $v->fecha_monto_inicial ?? $propietario->fecha_monto_inicial;

                                $cuota1 = ($v->cuota_1 > 0 || $v->fecha_cuota_1) ? $v->cuota_1 : ($propietario->cuota_1 ?? 0);
                                $fechaCuota1 = $v->fecha_cuota_1 ?? $propietario->fecha_cuota_1;

                                $cuota2 = ($v->cuota_2 > 0 || $v->fecha_cuota_2) ? $v->cuota_2 : ($propietario->cuota_2 ?? 0);
                                $fechaCuota2 = $v->fecha_cuota_2 ?? $propietario->fecha_cuota_2;

                                $cuota3 = ($v->cuota_3 > 0 || $v->fecha_cuota_3) ? $v->cuota_3 : ($propietario->cuota_3 ?? 0);
                                $fechaCuota3 = $v->fecha_cuota_3 ?? $propietario->fecha_cuota_3;
                            @endphp

                            @if($vehiculos->count() > 1)
                                <div style="font-size: 12px; font-weight: 800; color: var(--accent); margin-top: 10px; margin-bottom: 6px;">
                                    <i class="fa-solid fa-car"></i> Pagos de Flota #{{ $v->numero_flota }} ({{ $v->placa_form }}):
                                </div>
                            @endif

                            <div style="display: flex; flex-direction: column; gap: 8px;">
                                {{-- Monto Inicial --}}
                                <div style="display: flex; justify-content: space-between; align-items: center; padding: 10px 12px; background: #ffffff; border: 1px solid var(--border); border-radius: 8px;">
                                    <div style="display: flex; align-items: center; gap: 8px;">
                                        <div style="width: 26px; height: 26px; border-radius: 6px; background: {{ $montoInicial > 0 ? 'var(--green-l)' : '#f1f5f9' }}; color: {{ $montoInicial > 0 ? 'var(--green)' : 'var(--text3)' }}; display: flex; align-items: center; justify-content: center; font-size: 11px;">
                                            <i class="fa-solid fa-money-bill"></i>
                                        </div>
                                        <div>
                                            <div style="font-weight: 700; font-size: 12.5px;">Monto Inicial</div>
                                            <div style="font-size: 10.5px; color: var(--text3);">
                                                {{ $fechaMontoInicial ? \Carbon\Carbon::parse($fechaMontoInicial)->format('d/m/Y') : 'Sin fecha registrada' }}
                                            </div>
                                        </div>
                                    </div>
                                    <div style="text-align: right;">
                                        <span style="font-weight: 800; font-size: 13.5px; color: {{ $montoInicial > 0 ? 'var(--green)' : 'var(--text3)' }}; font-family: monospace;">
                                            S/ {{ number_format($montoInicial, 2) }}
                                        </span>
                                    </div>
                                </div>

                                {{-- Cuota 1 --}}
                                <div style="display: flex; justify-content: space-between; align-items: center; padding: 10px 12px; background: #ffffff; border: 1px solid var(--border); border-radius: 8px;">
                                    <div style="display: flex; align-items: center; gap: 8px;">
                                        <div style="width: 26px; height: 26px; border-radius: 6px; background: {{ $cuota1 > 0 ? 'var(--green-l)' : '#f1f5f9' }}; color: {{ $cuota1 > 0 ? 'var(--green)' : 'var(--text3)' }}; display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: 800;">
                                            1
                                        </div>
                                        <div>
                                            <div style="font-weight: 700; font-size: 12.5px;">Cuota 1</div>
                                            <div style="font-size: 10.5px; color: var(--text3);">
                                                {{ $fechaCuota1 ? \Carbon\Carbon::parse($fechaCuota1)->format('d/m/Y') : 'Pendiente' }}
                                            </div>
                                        </div>
                                    </div>
                                    <div style="text-align: right;">
                                        <span style="font-weight: 800; font-size: 13.5px; color: {{ $cuota1 > 0 ? 'var(--green)' : 'var(--text3)' }}; font-family: monospace;">
                                            S/ {{ number_format($cuota1, 2) }}
                                        </span>
                                    </div>
                                </div>

                                {{-- Cuota 2 --}}
                                <div style="display: flex; justify-content: space-between; align-items: center; padding: 10px 12px; background: #ffffff; border: 1px solid var(--border); border-radius: 8px;">
                                    <div style="display: flex; align-items: center; gap: 8px;">
                                        <div style="width: 26px; height: 26px; border-radius: 6px; background: {{ $cuota2 > 0 ? 'var(--green-l)' : '#f1f5f9' }}; color: {{ $cuota2 > 0 ? 'var(--green)' : 'var(--text3)' }}; display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: 800;">
                                            2
                                        </div>
                                        <div>
                                            <div style="font-weight: 700; font-size: 12.5px;">Cuota 2</div>
                                            <div style="font-size: 10.5px; color: var(--text3);">
                                                {{ $fechaCuota2 ? \Carbon\Carbon::parse($fechaCuota2)->format('d/m/Y') : 'Pendiente' }}
                                            </div>
                                        </div>
                                    </div>
                                    <div style="text-align: right;">
                                        <span style="font-weight: 800; font-size: 13.5px; color: {{ $cuota2 > 0 ? 'var(--green)' : 'var(--text3)' }}; font-family: monospace;">
                                            S/ {{ number_format($cuota2, 2) }}
                                        </span>
                                    </div>
                                </div>

                                {{-- Cuota 3 --}}
                                <div style="display: flex; justify-content: space-between; align-items: center; padding: 10px 12px; background: #ffffff; border: 1px solid var(--border); border-radius: 8px;">
                                    <div style="display: flex; align-items: center; gap: 8px;">
                                        <div style="width: 26px; height: 26px; border-radius: 6px; background: {{ $cuota3 > 0 ? 'var(--green-l)' : '#f1f5f9' }}; color: {{ $cuota3 > 0 ? 'var(--green)' : 'var(--text3)' }}; display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: 800;">
                                            3
                                        </div>
                                        <div>
                                            <div style="font-weight: 700; font-size: 12.5px;">Cuota 3</div>
                                            <div style="font-size: 10.5px; color: var(--text3);">
                                                {{ $fechaCuota3 ? \Carbon\Carbon::parse($fechaCuota3)->format('d/m/Y') : 'Pendiente' }}
                                            </div>
                                        </div>
                                    </div>
                                    <div style="text-align: right;">
                                        <span style="font-weight: 800; font-size: 13.5px; color: {{ $cuota3 > 0 ? 'var(--green)' : 'var(--text3)' }}; font-family: monospace;">
                                            S/ {{ number_format($cuota3, 2) }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        @php
                            $montoInicial = $propietario->monto_inicial ?? 0;
                            $cuota1 = $propietario->cuota_1 ?? 0;
                            $cuota2 = $propietario->cuota_2 ?? 0;
                            $cuota3 = $propietario->cuota_3 ?? 0;
                        @endphp
                        <div style="display: flex; flex-direction: column; gap: 8px;">
                            <div style="display: flex; justify-content: space-between; align-items: center; padding: 10px 12px; background: #ffffff; border: 1px solid var(--border); border-radius: 8px;">
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <div style="width: 26px; height: 26px; border-radius: 6px; background: {{ $montoInicial > 0 ? 'var(--green-l)' : '#f1f5f9' }}; color: {{ $montoInicial > 0 ? 'var(--green)' : 'var(--text3)' }}; display: flex; align-items: center; justify-content: center; font-size: 11px;">
                                        <i class="fa-solid fa-money-bill"></i>
                                    </div>
                                    <div>
                                        <div style="font-weight: 700; font-size: 12.5px;">Monto Inicial</div>
                                        <div style="font-size: 10.5px; color: var(--text3);">
                                            {{ $propietario->fecha_monto_inicial ? $propietario->fecha_monto_inicial->format('d/m/Y') : 'Sin fecha registrada' }}
                                        </div>
                                    </div>
                                </div>
                                <div style="text-align: right;">
                                    <span style="font-weight: 800; font-size: 13.5px; color: {{ $montoInicial > 0 ? 'var(--green)' : 'var(--text3)' }}; font-family: monospace;">
                                        S/ {{ number_format($montoInicial, 2) }}
                                    </span>
                                </div>
                            </div>

                            <div style="display: flex; justify-content: space-between; align-items: center; padding: 10px 12px; background: #ffffff; border: 1px solid var(--border); border-radius: 8px;">
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <div style="width: 26px; height: 26px; border-radius: 6px; background: {{ $cuota1 > 0 ? 'var(--green-l)' : '#f1f5f9' }}; color: {{ $cuota1 > 0 ? 'var(--green)' : 'var(--text3)' }}; display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: 800;">1</div>
                                    <div>
                                        <div style="font-weight: 700; font-size: 12.5px;">Cuota 1</div>
                                        <div style="font-size: 10.5px; color: var(--text3);">
                                            {{ $propietario->fecha_cuota_1 ? $propietario->fecha_cuota_1->format('d/m/Y') : 'Pendiente' }}
                                        </div>
                                    </div>
                                </div>
                                <div style="text-align: right;">
                                    <span style="font-weight: 800; font-size: 13.5px; color: {{ $cuota1 > 0 ? 'var(--green)' : 'var(--text3)' }}; font-family: monospace;">
                                        S/ {{ number_format($cuota1, 2) }}
                                    </span>
                                </div>
                            </div>

                            <div style="display: flex; justify-content: space-between; align-items: center; padding: 10px 12px; background: #ffffff; border: 1px solid var(--border); border-radius: 8px;">
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <div style="width: 26px; height: 26px; border-radius: 6px; background: {{ $cuota2 > 0 ? 'var(--green-l)' : '#f1f5f9' }}; color: {{ $cuota2 > 0 ? 'var(--green)' : 'var(--text3)' }}; display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: 800;">2</div>
                                    <div>
                                        <div style="font-weight: 700; font-size: 12.5px;">Cuota 2</div>
                                        <div style="font-size: 10.5px; color: var(--text3);">
                                            {{ $propietario->fecha_cuota_2 ? $propietario->fecha_cuota_2->format('d/m/Y') : 'Pendiente' }}
                                        </div>
                                    </div>
                                </div>
                                <div style="text-align: right;">
                                    <span style="font-weight: 800; font-size: 13.5px; color: {{ $cuota2 > 0 ? 'var(--green)' : 'var(--text3)' }}; font-family: monospace;">
                                        S/ {{ number_format($cuota2, 2) }}
                                    </span>
                                </div>
                            </div>

                            <div style="display: flex; justify-content: space-between; align-items: center; padding: 10px 12px; background: #ffffff; border: 1px solid var(--border); border-radius: 8px;">
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <div style="width: 26px; height: 26px; border-radius: 6px; background: {{ $cuota3 > 0 ? 'var(--green-l)' : '#f1f5f9' }}; color: {{ $cuota3 > 0 ? 'var(--green)' : 'var(--text3)' }}; display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: 800;">3</div>
                                    <div>
                                        <div style="font-weight: 700; font-size: 12.5px;">Cuota 3</div>
                                        <div style="font-size: 10.5px; color: var(--text3);">
                                            {{ $propietario->fecha_cuota_3 ? $propietario->fecha_cuota_3->format('d/m/Y') : 'Pendiente' }}
                                        </div>
                                    </div>
                                </div>
                                <div style="text-align: right;">
                                    <span style="font-weight: 800; font-size: 13.5px; color: {{ $cuota3 > 0 ? 'var(--green)' : 'var(--text3)' }}; font-family: monospace;">
                                        S/ {{ number_format($cuota3, 2) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    @endif
                @endif
            </div>
        </div>

    </div>

@endsection
