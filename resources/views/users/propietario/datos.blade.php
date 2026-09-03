@extends('layouts.propietario')

@section('title', 'Mi Flota y Documentación')

@section('content')

    {{-- 1. SECCIÓN MONTO DE INGRESO Y LAS 3 CUOTAS --}}
    <div class="card" style="border: 2px solid var(--accent); background: #ffffff;">
        <div class="card-header" style="background: var(--accent-l);">
            <span class="card-title" style="color: var(--accent); font-size: 15px;">
                <i class="fa-solid fa-money-bill-transfer"></i> Monto de Ingreso a la Empresa
            </span>
            <span class="pill {{ $propietario->es_socio ? 'gold' : ($propietario->monto_ingreso_deuda > 0 ? 'red' : 'green') }}">
                {{ $propietario->estado_ingreso }}
            </span>
        </div>
        <div class="card-body">
            @if($propietario->es_socio)
                <div style="background: #fef3c7; border: 1px solid #fde68a; border-radius: 10px; padding: 14px; text-align: center;">
                    <i class="fa-solid fa-award" style="font-size: 32px; color: var(--gold); margin-bottom: 6px;"></i>
                    <div style="font-weight: 800; font-size: 15px; color: #92400e;">Socio de la Empresa</div>
                    <div style="font-size: 12px; color: #b45309; margin-top: 2px;">
                        Como socio estás 100% exonerado del pago de monto de ingreso.
                    </div>
                </div>
            @else
                @php
                    $deuda = $propietario->monto_ingreso_deuda;
                    $totalAbonado = $propietario->monto_ingreso_total;
                @endphp

                {{-- Resumen Consolidado --}}
                <div style="display: flex; justify-content: space-between; align-items: center; background: #f8fafc; padding: 12px; border-radius: 10px; margin-bottom: 14px; border: 1px solid var(--border);">
                    <div>
                        <div style="font-size: 10.5px; color: var(--text3); font-weight: 700; text-transform: uppercase;">Total Abonado</div>
                        <div style="font-size: 18px; font-weight: 800; color: var(--green);">
                            S/ {{ number_format($totalAbonado, 2) }}
                        </div>
                    </div>
                    <div style="text-align: right;">
                        <div style="font-size: 10.5px; color: var(--text3); font-weight: 700; text-transform: uppercase;">Saldo Pendiente</div>
                        <div style="font-size: 18px; font-weight: 800; color: {{ $deuda > 0 ? 'var(--red)' : 'var(--green)' }};">
                            S/ {{ number_format($deuda, 2) }}
                        </div>
                    </div>
                </div>

                @if($vehiculos->isNotEmpty())
                    @foreach($vehiculos as $v)
                        @php
                            // Priorizar pagos registrados en el vehículo o en el propietario
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
                            <div style="font-size: 13px; font-weight: 800; color: var(--accent); margin-top: 10px; margin-bottom: 6px;">
                                <i class="fa-solid fa-car"></i> Pagos de Flota #{{ $v->numero_flota }} ({{ $v->placa_form }}):
                            </div>
                        @else
                            <div style="font-size: 12px; font-weight: 800; color: var(--text2); text-transform: uppercase; margin-bottom: 8px; letter-spacing: 0.5px;">
                                Desglose de Pagos Registrados:
                            </div>
                        @endif

                        <div style="display: flex; flex-direction: column; gap: 8px; margin-bottom: 12px;">
                            {{-- Monto Inicial --}}
                            <div style="display: flex; justify-content: space-between; align-items: center; padding: 10px 12px; background: #ffffff; border: 1px solid var(--border); border-radius: 8px;">
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <div style="width: 24px; height: 24px; border-radius: 6px; background: {{ $montoInicial > 0 ? 'var(--green-l)' : '#f1f5f9' }}; color: {{ $montoInicial > 0 ? 'var(--green)' : 'var(--text3)' }}; display: flex; align-items: center; justify-content: center; font-size: 11px;">
                                        <i class="fa-solid fa-money-bill"></i>
                                    </div>
                                    <div>
                                        <div style="font-weight: 700; font-size: 13px;">Monto Inicial</div>
                                        <div style="font-size: 11px; color: var(--text3);">
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
                                    <div style="width: 24px; height: 24px; border-radius: 6px; background: {{ $cuota1 > 0 ? 'var(--green-l)' : '#f1f5f9' }}; color: {{ $cuota1 > 0 ? 'var(--green)' : 'var(--text3)' }}; display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: 800;">
                                        1
                                    </div>
                                    <div>
                                        <div style="font-weight: 700; font-size: 13px;">Cuota 1</div>
                                        <div style="font-size: 11px; color: var(--text3);">
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
                                    <div style="width: 24px; height: 24px; border-radius: 6px; background: {{ $cuota2 > 0 ? 'var(--green-l)' : '#f1f5f9' }}; color: {{ $cuota2 > 0 ? 'var(--green)' : 'var(--text3)' }}; display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: 800;">
                                        2
                                    </div>
                                    <div>
                                        <div style="font-weight: 700; font-size: 13px;">Cuota 2</div>
                                        <div style="font-size: 11px; color: var(--text3);">
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
                                    <div style="width: 24px; height: 24px; border-radius: 6px; background: {{ $cuota3 > 0 ? 'var(--green-l)' : '#f1f5f9' }}; color: {{ $cuota3 > 0 ? 'var(--green)' : 'var(--text3)' }}; display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: 800;">
                                        3
                                    </div>
                                    <div>
                                        <div style="font-weight: 700; font-size: 13px;">Cuota 3</div>
                                        <div style="font-size: 11px; color: var(--text3);">
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
                    {{-- Sin vehículos asignados aún: tomar de propietario --}}
                    @php
                        $montoInicial = $propietario->monto_inicial ?? 0;
                        $cuota1 = $propietario->cuota_1 ?? 0;
                        $cuota2 = $propietario->cuota_2 ?? 0;
                        $cuota3 = $propietario->cuota_3 ?? 0;
                    @endphp
                    <div style="display: flex; flex-direction: column; gap: 8px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; padding: 10px 12px; background: #ffffff; border: 1px solid var(--border); border-radius: 8px;">
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <div style="width: 24px; height: 24px; border-radius: 6px; background: {{ $montoInicial > 0 ? 'var(--green-l)' : '#f1f5f9' }}; color: {{ $montoInicial > 0 ? 'var(--green)' : 'var(--text3)' }}; display: flex; align-items: center; justify-content: center; font-size: 11px;">
                                    <i class="fa-solid fa-money-bill"></i>
                                </div>
                                <div>
                                    <div style="font-weight: 700; font-size: 13px;">Monto Inicial</div>
                                    <div style="font-size: 11px; color: var(--text3);">
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
                                <div style="width: 24px; height: 24px; border-radius: 6px; background: {{ $cuota1 > 0 ? 'var(--green-l)' : '#f1f5f9' }}; color: {{ $cuota1 > 0 ? 'var(--green)' : 'var(--text3)' }}; display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: 800;">1</div>
                                <div>
                                    <div style="font-weight: 700; font-size: 13px;">Cuota 1</div>
                                    <div style="font-size: 11px; color: var(--text3);">
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
                                <div style="width: 24px; height: 24px; border-radius: 6px; background: {{ $cuota2 > 0 ? 'var(--green-l)' : '#f1f5f9' }}; color: {{ $cuota2 > 0 ? 'var(--green)' : 'var(--text3)' }}; display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: 800;">2</div>
                                <div>
                                    <div style="font-weight: 700; font-size: 13px;">Cuota 2</div>
                                    <div style="font-size: 11px; color: var(--text3);">
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
                                <div style="width: 24px; height: 24px; border-radius: 6px; background: {{ $cuota3 > 0 ? 'var(--green-l)' : '#f1f5f9' }}; color: {{ $cuota3 > 0 ? 'var(--green)' : 'var(--text3)' }}; display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: 800;">3</div>
                                <div>
                                    <div style="font-weight: 700; font-size: 13px;">Cuota 3</div>
                                    <div style="font-size: 11px; color: var(--text3);">
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

    {{-- 2. DATOS DE LOS VEHÍCULOS DE LA FLOTA --}}
    @forelse($vehiculos as $vehiculo)
        <div class="card">
            <div class="card-header">
                <div>
                    <span class="card-title">
                        <i class="fa-solid fa-car" style="color: var(--accent); margin-right: 6px;"></i>
                        Unidad #{{ $vehiculo->numero_flota ?? '—' }} ({{ $vehiculo->placa_form }})
                    </span>
                </div>
                <span class="pill {{ $vehiculo->estado === 'activo' ? 'green' : 'red' }}">
                    {{ strtoupper($vehiculo->estado) }}
                </span>
            </div>
            <div class="card-body">
                
                {{-- Chofer Asignado --}}
                <div style="background: #f8fafc; padding: 10px 12px; border-radius: 8px; margin-bottom: 14px; display: flex; align-items: center; justify-content: space-between; border: 1px solid var(--border);">
                    <div>
                        <div style="font-size: 10.5px; color: var(--text3); font-weight: 700; text-transform: uppercase;">Conductor Asignado</div>
                        <div style="font-weight: 800; font-size: 14px; color: var(--text);">
                            {{ $vehiculo->conductor ? $vehiculo->conductor->nombre_completo : 'Sin conductor asignado' }}
                        </div>
                        @if($vehiculo->conductor)
                            <div style="font-size: 11px; color: var(--text3);">DNI: {{ $vehiculo->conductor->dni }} • Tel: {{ $vehiculo->conductor->telefono ?? '—' }}</div>
                        @endif
                    </div>
                    <i class="fa-solid fa-user-tie" style="font-size: 24px; color: var(--accent); opacity: 0.8;"></i>
                </div>

                {{-- Ficha Técnica --}}
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px; font-size: 12.5px; margin-bottom: 14px;">
                    <div><span style="color: var(--text3);">Marca:</span> <b>{{ $vehiculo->marca ?? '—' }}</b></div>
                    <div><span style="color: var(--text3);">Modelo:</span> <b>{{ $vehiculo->modelo ?? '—' }}</b></div>
                    <div><span style="color: var(--text3);">Año:</span> <b>{{ $vehiculo->anio ?? '—' }}</b></div>
                    <div><span style="color: var(--text3);">Color:</span> <b>{{ $vehiculo->color ?? '—' }}</b></div>
                    <div><span style="color: var(--text3);">N° Motor:</span> <b style="font-family: monospace;">{{ $vehiculo->numero_motor ?? '—' }}</b></div>
                    <div><span style="color: var(--text3);">N° Chasis:</span> <b style="font-family: monospace;">{{ $vehiculo->numero_chasis ?? '—' }}</b></div>
                </div>

                {{-- Documentación y Vigencias --}}
                <div style="font-size: 12px; font-weight: 800; color: var(--text2); text-transform: uppercase; margin-bottom: 8px;">
                    Vigencia de Documentación:
                </div>

                <div style="display: flex; flex-direction: column; gap: 6px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; padding: 8px 10px; background: #f8fafc; border-radius: 6px; font-size: 12px;">
                        <span><b>SOAT:</b> {{ $vehiculo->soat_vence ? \Carbon\Carbon::parse($vehiculo->soat_vence)->format('d/m/Y') : 'No registrado' }}</span>
                        @if($vehiculo->soat_vence)
                            <span class="pill {{ \Carbon\Carbon::parse($vehiculo->soat_vence)->isPast() ? 'red' : 'green' }}" style="font-size: 10px;">
                                {{ \Carbon\Carbon::parse($vehiculo->soat_vence)->isPast() ? 'Vencido' : 'Vigente' }}
                            </span>
                        @endif
                    </div>

                    <div style="display: flex; justify-content: space-between; align-items: center; padding: 8px 10px; background: #f8fafc; border-radius: 6px; font-size: 12px;">
                        <span><b>Revisión Técnica:</b> {{ $vehiculo->rev_tecnica_vence ? \Carbon\Carbon::parse($vehiculo->rev_tecnica_vence)->format('d/m/Y') : 'No registrada' }}</span>
                        @if($vehiculo->rev_tecnica_vence)
                            <span class="pill {{ \Carbon\Carbon::parse($vehiculo->rev_tecnica_vence)->isPast() ? 'red' : 'green' }}" style="font-size: 10px;">
                                {{ \Carbon\Carbon::parse($vehiculo->rev_tecnica_vence)->isPast() ? 'Vencida' : 'Vigente' }}
                            </span>
                        @endif
                    </div>
                </div>

            </div>
        </div>
    @empty
        <div class="empty-state">
            <i class="fa-solid fa-car" style="font-size: 28px; margin-bottom: 8px; display: block; opacity: 0.5;"></i>
            No tienes vehículos asignados actualmente.
        </div>
    @endforelse

@endsection
