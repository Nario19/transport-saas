@extends('layouts.propietario')

@section('title', 'Mi Flota y Documentación')

@section('content')

    {{-- ENCABEZADO DE PÁGINA --}}
    <div style="margin-bottom: 16px; display: flex; justify-content: space-between; align-items: center;">
        <div>
            <h2 style="font-size: 18px; font-weight: 800; color: var(--text); margin: 0; display: flex; align-items: center; gap: 8px;">
                <i class="fa-solid fa-car" style="color: var(--accent);"></i> Mi Flota y Documentación
            </h2>
            <p style="font-size: 12px; color: var(--text3); margin: 2px 0 0 0;">
                Ficha técnica, SOAT, Revisión Técnica y Monto de Ingreso
            </p>
        </div>
        <span class="pill blue" style="font-size: 11px; font-weight: 800;">
            {{ $vehiculos->count() }} {{ $vehiculos->count() == 1 ? 'UNIDAD' : 'UNIDADES' }}
        </span>
    </div>

    {{-- 1. VEHÍCULOS DE LA FLOTA --}}
    @forelse($vehiculos as $vehiculo)
        <div class="card" style="margin-bottom: 16px; border-radius: 14px; overflow: hidden; box-shadow: var(--shadow);">
            {{-- Header de la Unidad --}}
            <div class="card-header" style="background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); color: #ffffff; padding: 12px 16px;">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <div style="width: 36px; height: 36px; border-radius: 10px; background: rgba(255,255,255,0.15); display: flex; align-items: center; justify-content: center; font-size: 16px;">
                        <i class="fa-solid fa-car-side" style="color: #60a5fa;"></i>
                    </div>
                    <div>
                        <div style="font-weight: 800; font-size: 15px; letter-spacing: 0.3px;">
                            Flota #{{ $vehiculo->numero_flota ?? '—' }} • {{ $vehiculo->placa_form }}
                        </div>
                        <div style="font-size: 11px; color: #94a3b8;">
                            {{ $vehiculo->marca }} {{ $vehiculo->modelo }} {{ $vehiculo->anio ? '('.$vehiculo->anio.')' : '' }}
                        </div>
                    </div>
                </div>
                <span class="pill {{ $vehiculo->estado === 'activo' ? 'green' : 'red' }}" style="font-size: 10.5px;">
                    {{ strtoupper($vehiculo->estado) }}
                </span>
            </div>

            <div class="card-body" style="padding: 16px;">
                {{-- Conductor Asignado --}}
                <div style="background: #f8fafc; padding: 12px; border-radius: 10px; margin-bottom: 14px; border: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between;">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <div style="width: 32px; height: 32px; border-radius: 50%; background: #e0f2fe; color: #0284c7; display: flex; align-items: center; justify-content: center; font-size: 14px;">
                            <i class="fa-solid fa-user-tie"></i>
                        </div>
                        <div>
                            <div style="font-size: 10px; color: var(--text3); font-weight: 700; text-transform: uppercase;">Conductor Asignado</div>
                            <div style="font-weight: 800; font-size: 13.5px; color: var(--text);">
                                {{ $vehiculo->conductor ? $vehiculo->conductor->nombre_completo : 'Sin conductor asignado' }}
                            </div>
                            @if($vehiculo->conductor)
                                <div style="font-size: 11px; color: var(--text3);">
                                    DNI: {{ $vehiculo->conductor->dni }} • Tel: {{ $vehiculo->conductor->telefono ?? '—' }}
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Ficha Técnica (Grilla) --}}
                <div style="font-size: 11.5px; font-weight: 800; color: var(--text2); text-transform: uppercase; margin-bottom: 8px; letter-spacing: 0.5px;">
                    <i class="fa-solid fa-circle-info" style="color: var(--accent);"></i> Especificaciones Técnicas
                </div>
                <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 8px; font-size: 12px; background: #f8fafc; padding: 12px; border-radius: 10px; border: 1px solid var(--border); margin-bottom: 16px;">
                    <div><span style="color: var(--text3);">Marca:</span> <b>{{ $vehiculo->marca ?? '—' }}</b></div>
                    <div><span style="color: var(--text3);">Modelo:</span> <b>{{ $vehiculo->modelo ?? '—' }}</b></div>
                    <div><span style="color: var(--text3);">Año:</span> <b>{{ $vehiculo->anio ?? '—' }}</b></div>
                    <div><span style="color: var(--text3);">Color:</span> <b>{{ $vehiculo->color ?? '—' }}</b></div>
                    <div><span style="color: var(--text3);">N° Motor:</span> <b style="font-family: monospace;">{{ $vehiculo->numero_motor ?? '—' }}</b></div>
                    <div><span style="color: var(--text3);">N° Chasis:</span> <b style="font-family: monospace;">{{ $vehiculo->numero_chasis ?? '—' }}</b></div>
                </div>

                {{-- Documentación y Actualización de Vigencias --}}
                <div style="font-size: 11.5px; font-weight: 800; color: var(--text2); text-transform: uppercase; margin-bottom: 8px; letter-spacing: 0.5px;">
                    <i class="fa-solid fa-file-shield" style="color: var(--accent);"></i> Documentación y Vencimientos
                </div>

                <form action="{{ route('propietario.vehiculos.update-documentos', $vehiculo->id) }}" method="POST" style="background: #ffffff; border: 1.5px solid var(--border); border-radius: 12px; padding: 14px;">
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
                            <input type="date" name="soat_vence" value="{{ old('soat_vence', $vehiculo->soat_vence ? \Carbon\Carbon::parse($vehiculo->soat_vence)->format('Y-m-d') : '') }}" style="width: 100%; padding: 8px 12px; border-radius: 8px; border: 1px solid var(--border); font-size: 13px; font-weight: 600; color: var(--text); background: #f8fafc;">
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
                            <input type="date" name="rev_tecnica_vence" value="{{ old('rev_tecnica_vence', $vehiculo->rev_tecnica_vence ? \Carbon\Carbon::parse($vehiculo->rev_tecnica_vence)->format('Y-m-d') : '') }}" style="width: 100%; padding: 8px 12px; border-radius: 8px; border: 1px solid var(--border); font-size: 13px; font-weight: 600; color: var(--text); background: #f8fafc;">
                        </div>

                        {{-- Botón Guardar Documentos --}}
                        <button type="submit" class="btn btn-primary" style="padding: 10px; font-size: 12.5px; font-weight: 700; border-radius: 8px; display: flex; align-items: center; justify-content: center; gap: 6px; margin-top: 4px; background: var(--accent); color: #fff; border: none; cursor: pointer;">
                            <i class="fa-solid fa-floppy-disk"></i> Guardar Vencimientos de Flota #{{ $vehiculo->numero_flota }}
                        </button>
                    </div>
                </form>

            </div>
        </div>
    @empty
        <div class="card" style="text-align: center; padding: 30px 20px; border-radius: 12px; margin-bottom: 16px;">
            <i class="fa-solid fa-car" style="font-size: 32px; margin-bottom: 10px; color: var(--text3); opacity: 0.5;"></i>
            <div style="font-size: 14px; color: var(--text2); font-weight: 700;">Sin vehículos asignados</div>
            <div style="font-size: 12px; color: var(--text3); margin-top: 4px;">Actualmente no tienes unidades vehiculares vinculadas.</div>
        </div>
    @endforelse


    {{-- 2. SECCIÓN MONTO DE INGRESO Y LAS 3 CUOTAS --}}
    <div class="card" style="border: 2px solid var(--accent); background: #ffffff; border-radius: 14px; overflow: hidden; box-shadow: var(--shadow);">
        <div class="card-header" style="background: var(--accent-l); padding: 12px 16px;">
            <span class="card-title" style="color: var(--accent); font-size: 14.5px; font-weight: 800; display: flex; align-items: center; gap: 8px;">
                <i class="fa-solid fa-money-bill-transfer"></i> Monto de Ingreso a la Empresa
            </span>
            <span class="pill {{ $propietario->es_socio ? 'gold' : ($propietario->monto_ingreso_deuda > 0 ? 'red' : 'green') }}">
                {{ $propietario->estado_ingreso }}
            </span>
        </div>
        <div class="card-body" style="padding: 16px;">
            @if($propietario->es_socio)
                <div style="background: #fef3c7; border: 1px solid #fde68a; border-radius: 12px; padding: 16px; text-align: center;">
                    <i class="fa-solid fa-award" style="font-size: 36px; color: var(--gold); margin-bottom: 8px;"></i>
                    <div style="font-weight: 800; font-size: 15px; color: #92400e;">Socio de la Empresa</div>
                    <div style="font-size: 12px; color: #b45309; margin-top: 4px; line-height: 1.4;">
                        Como socio registrado de la empresa estás 100% exonerado del pago de monto de ingreso por tus unidades.
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
                <div style="background: #f8fafc; padding: 14px; border-radius: 12px; margin-bottom: 14px; border: 1px solid var(--border);">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                        <div>
                            <div style="font-size: 10px; color: var(--text3); font-weight: 700; text-transform: uppercase;">Total Abonado</div>
                            <div style="font-size: 19px; font-weight: 800; color: var(--green);">
                                S/ {{ number_format($totalAbonado, 2) }}
                            </div>
                        </div>
                        <div style="text-align: right;">
                            <div style="font-size: 10px; color: var(--text3); font-weight: 700; text-transform: uppercase;">Saldo Pendiente</div>
                            <div style="font-size: 19px; font-weight: 800; color: {{ $deuda > 0 ? 'var(--red)' : 'var(--green)' }};">
                                S/ {{ number_format($deuda, 2) }}
                            </div>
                        </div>
                    </div>

                    {{-- Barra de Progreso --}}
                    <div style="background: #e2e8f0; border-radius: 99px; height: 8px; overflow: hidden; position: relative;">
                        <div style="background: {{ $deuda > 0 ? 'var(--accent)' : 'var(--green)' }}; width: {{ $porcentaje }}%; height: 100%; border-radius: 99px; transition: width 0.3s;"></div>
                    </div>
                    <div style="display: flex; justify-content: space-between; font-size: 10.5px; color: var(--text3); margin-top: 4px; font-weight: 600;">
                        <span>{{ $porcentaje }}% completado</span>
                        <span>Total: S/ {{ number_format($totalObligado, 2) }}</span>
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
                            <div style="font-size: 12.5px; font-weight: 800; color: var(--accent); margin-top: 14px; margin-bottom: 8px; display: flex; align-items: center; gap: 6px;">
                                <i class="fa-solid fa-car"></i> Pagos de Flota #{{ $v->numero_flota }} ({{ $v->placa_form }}):
                            </div>
                        @else
                            <div style="font-size: 11.5px; font-weight: 800; color: var(--text2); text-transform: uppercase; margin-bottom: 8px; letter-spacing: 0.5px;">
                                Desglose de Pagos Registrados:
                            </div>
                        @endif

                        <div style="display: flex; flex-direction: column; gap: 8px; margin-bottom: 8px;">
                            {{-- Monto Inicial --}}
                            <div style="display: flex; justify-content: space-between; align-items: center; padding: 10px 12px; background: #ffffff; border: 1px solid var(--border); border-radius: 10px;">
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <div style="width: 28px; height: 28px; border-radius: 8px; background: {{ $montoInicial > 0 ? 'var(--green-l)' : '#f1f5f9' }}; color: {{ $montoInicial > 0 ? 'var(--green)' : 'var(--text3)' }}; display: flex; align-items: center; justify-content: center; font-size: 12px;">
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
                                    <span style="font-weight: 800; font-size: 14px; color: {{ $montoInicial > 0 ? 'var(--green)' : 'var(--text3)' }}; font-family: monospace;">
                                        S/ {{ number_format($montoInicial, 2) }}
                                    </span>
                                </div>
                            </div>

                            {{-- Cuota 1 --}}
                            <div style="display: flex; justify-content: space-between; align-items: center; padding: 10px 12px; background: #ffffff; border: 1px solid var(--border); border-radius: 10px;">
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <div style="width: 28px; height: 28px; border-radius: 8px; background: {{ $cuota1 > 0 ? 'var(--green-l)' : '#f1f5f9' }}; color: {{ $cuota1 > 0 ? 'var(--green)' : 'var(--text3)' }}; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 800;">
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
                                    <span style="font-weight: 800; font-size: 14px; color: {{ $cuota1 > 0 ? 'var(--green)' : 'var(--text3)' }}; font-family: monospace;">
                                        S/ {{ number_format($cuota1, 2) }}
                                    </span>
                                </div>
                            </div>

                            {{-- Cuota 2 --}}
                            <div style="display: flex; justify-content: space-between; align-items: center; padding: 10px 12px; background: #ffffff; border: 1px solid var(--border); border-radius: 10px;">
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <div style="width: 28px; height: 28px; border-radius: 8px; background: {{ $cuota2 > 0 ? 'var(--green-l)' : '#f1f5f9' }}; color: {{ $cuota2 > 0 ? 'var(--green)' : 'var(--text3)' }}; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 800;">
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
                                    <span style="font-weight: 800; font-size: 14px; color: {{ $cuota2 > 0 ? 'var(--green)' : 'var(--text3)' }}; font-family: monospace;">
                                        S/ {{ number_format($cuota2, 2) }}
                                    </span>
                                </div>
                            </div>

                            {{-- Cuota 3 --}}
                            <div style="display: flex; justify-content: space-between; align-items: center; padding: 10px 12px; background: #ffffff; border: 1px solid var(--border); border-radius: 10px;">
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <div style="width: 28px; height: 28px; border-radius: 8px; background: {{ $cuota3 > 0 ? 'var(--green-l)' : '#f1f5f9' }}; color: {{ $cuota3 > 0 ? 'var(--green)' : 'var(--text3)' }}; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 800;">
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
                                    <span style="font-weight: 800; font-size: 14px; color: {{ $cuota3 > 0 ? 'var(--green)' : 'var(--text3)' }}; font-family: monospace;">
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
                        <div style="display: flex; justify-content: space-between; align-items: center; padding: 10px 12px; background: #ffffff; border: 1px solid var(--border); border-radius: 10px;">
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <div style="width: 28px; height: 28px; border-radius: 8px; background: {{ $montoInicial > 0 ? 'var(--green-l)' : '#f1f5f9' }}; color: {{ $montoInicial > 0 ? 'var(--green)' : 'var(--text3)' }}; display: flex; align-items: center; justify-content: center; font-size: 12px;">
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
                                <span style="font-weight: 800; font-size: 14px; color: {{ $montoInicial > 0 ? 'var(--green)' : 'var(--text3)' }}; font-family: monospace;">
                                    S/ {{ number_format($montoInicial, 2) }}
                                </span>
                            </div>
                        </div>

                        <div style="display: flex; justify-content: space-between; align-items: center; padding: 10px 12px; background: #ffffff; border: 1px solid var(--border); border-radius: 10px;">
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <div style="width: 28px; height: 28px; border-radius: 8px; background: {{ $cuota1 > 0 ? 'var(--green-l)' : '#f1f5f9' }}; color: {{ $cuota1 > 0 ? 'var(--green)' : 'var(--text3)' }}; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 800;">1</div>
                                <div>
                                    <div style="font-weight: 700; font-size: 13px;">Cuota 1</div>
                                    <div style="font-size: 11px; color: var(--text3);">
                                        {{ $propietario->fecha_cuota_1 ? $propietario->fecha_cuota_1->format('d/m/Y') : 'Pendiente' }}
                                    </div>
                                </div>
                            </div>
                            <div style="text-align: right;">
                                <span style="font-weight: 800; font-size: 14px; color: {{ $cuota1 > 0 ? 'var(--green)' : 'var(--text3)' }}; font-family: monospace;">
                                    S/ {{ number_format($cuota1, 2) }}
                                </span>
                            </div>
                        </div>

                        <div style="display: flex; justify-content: space-between; align-items: center; padding: 10px 12px; background: #ffffff; border: 1px solid var(--border); border-radius: 10px;">
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <div style="width: 28px; height: 28px; border-radius: 8px; background: {{ $cuota2 > 0 ? 'var(--green-l)' : '#f1f5f9' }}; color: {{ $cuota2 > 0 ? 'var(--green)' : 'var(--text3)' }}; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 800;">2</div>
                                <div>
                                    <div style="font-weight: 700; font-size: 13px;">Cuota 2</div>
                                    <div style="font-size: 11px; color: var(--text3);">
                                        {{ $propietario->fecha_cuota_2 ? $propietario->fecha_cuota_2->format('d/m/Y') : 'Pendiente' }}
                                    </div>
                                </div>
                            </div>
                            <div style="text-align: right;">
                                <span style="font-weight: 800; font-size: 14px; color: {{ $cuota2 > 0 ? 'var(--green)' : 'var(--text3)' }}; font-family: monospace;">
                                    S/ {{ number_format($cuota2, 2) }}
                                </span>
                            </div>
                        </div>

                        <div style="display: flex; justify-content: space-between; align-items: center; padding: 10px 12px; background: #ffffff; border: 1px solid var(--border); border-radius: 10px;">
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <div style="width: 28px; height: 28px; border-radius: 8px; background: {{ $cuota3 > 0 ? 'var(--green-l)' : '#f1f5f9' }}; color: {{ $cuota3 > 0 ? 'var(--green)' : 'var(--text3)' }}; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 800;">3</div>
                                <div>
                                    <div style="font-weight: 700; font-size: 13px;">Cuota 3</div>
                                    <div style="font-size: 11px; color: var(--text3);">
                                        {{ $propietario->fecha_cuota_3 ? $propietario->fecha_cuota_3->format('d/m/Y') : 'Pendiente' }}
                                    </div>
                                </div>
                            </div>
                            <div style="text-align: right;">
                                <span style="font-weight: 800; font-size: 14px; color: {{ $cuota3 > 0 ? 'var(--green)' : 'var(--text3)' }}; font-family: monospace;">
                                    S/ {{ number_format($cuota3, 2) }}
                                </span>
                            </div>
                        </div>
                    </div>
                @endif
            @endif
        </div>
    </div>

@endsection
