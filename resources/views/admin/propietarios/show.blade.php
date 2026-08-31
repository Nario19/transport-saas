@extends('layouts.admin')

@php
    $pageTitle = 'Expediente de Propietario';
    $pageSubtitle = 'Detalles legales, vehículos asignados y control de cuotas de ingreso';
    $totalVehiculos = $propietario->vehiculos->count();
@endphp

@section('back_url', route('propietarios.index'))

@section('content')
    <div class="panel">
        
        {{-- 1. CABECERA CON PERFIL Y ACCIONES (SIN AVATAR) --}}
        <div class="card-header-actions" style="margin-bottom: 24px;">
            <div class="flex-v" style="gap: 6px;">
                <h2 style="font-size: 24px; font-weight: 800; color: var(--text); margin: 0;">
                    {{ $propietario->nombre }} {{ $propietario->apellidos }}
                </h2>
                <div class="flex-h" style="gap: 8px; align-items: center; flex-wrap: wrap;">
                    <span class="pill {{ $propietario->activo ? 'green' : 'red' }}" style="font-weight: 800; font-size: 11px;">
                        {{ $propietario->activo ? 'VIGENTE' : 'INACTIVO' }}
                    </span>
                    @if($propietario->es_socio)
                        <span class="pill blue" style="font-size: 11px; font-weight: 800; padding: 4px 10px;">
                            <i class="fa-solid fa-star"></i> SOCIO DE LA EMPRESA (EXONERADO)
                        </span>
                    @else
                        <span class="pill gray" style="font-size: 11px; font-weight: 700; padding: 4px 10px;">
                            Persona / Tercero Normal
                        </span>
                    @endif
                    @if($propietario->conductor)
                        <span class="pill gold" style="font-size: 11px; font-weight: 800;">
                            <i class="fa-solid fa-id-card"></i> SOCIO-CONDUCTOR
                        </span>
                    @endif
                    <span style="font-size: 12.5px; color: var(--text3); font-weight: 600;">ID: #{{ $propietario->id }}</span>
                </div>
            </div>
            <div class="flex-h" style="gap: 10px;">
                <a href="{{ route('reportes.deudas', ['tipo' => 'monto_ingreso', 'propietario_id' => $propietario->id]) }}" class="btn-secondary" style="text-decoration: none;">
                    <i class="fa-solid fa-receipt"></i> Ver en Reporte de Deudas
                </a>
                <a href="{{ route('propietarios.edit', $propietario->id) }}" class="btn-primary" style="text-decoration: none;">
                    <i class="fa-solid fa-user-pen"></i> Editar Propietario
                </a>
            </div>
        </div>

        {{-- 2. TARJETAS DE MÉTRICAS / RESUMEN RÁPIDO --}}
        <div class="g-4" style="margin-bottom: 24px; display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px;">
            
            {{-- Condición --}}
            <div class="card" style="padding: 18px; border-left: 4px solid var(--accent); background: var(--card);">
                <div style="font-size: 12px; font-weight: 700; color: var(--text3); text-transform: uppercase;">Condición</div>
                <div style="font-size: 16px; font-weight: 800; color: var(--text); margin-top: 6px;">
                    {{ $propietario->es_socio ? 'Socio de la Empresa' : 'Persona Normal' }}
                </div>
                <div style="font-size: 11.5px; color: {{ $propietario->es_socio ? 'var(--accent)' : 'var(--text3)' }}; font-weight: 600; margin-top: 4px;">
                    {{ $propietario->es_socio ? '⭐ Exonerado de Ingreso' : 'Registro de Propietario' }}
                </div>
            </div>

            {{-- Unidades Asignadas --}}
            <div class="card" style="padding: 18px; border-left: 4px solid #0284c7; background: var(--card);">
                <div style="font-size: 12px; font-weight: 700; color: var(--text3); text-transform: uppercase;">Vehículos Asignados</div>
                <div style="font-size: 20px; font-weight: 900; color: var(--text); margin-top: 6px;">
                    {{ $totalVehiculos }} <span style="font-size: 13px; font-weight: 600; color: var(--text3);">{{ $totalVehiculos === 1 ? 'unidad' : 'unidades' }}</span>
                </div>
                <div style="font-size: 11.5px; color: var(--text3); font-weight: 600; margin-top: 4px;">
                    Unidades vinculadas
                </div>
            </div>

            {{-- Total Recaudado --}}
            <div class="card" style="padding: 18px; border-left: 4px solid var(--green); background: var(--card);">
                <div style="font-size: 12px; font-weight: 700; color: var(--text3); text-transform: uppercase;">Total Recaudado (Ingreso)</div>
                <div style="font-size: 20px; font-weight: 900; color: var(--green); margin-top: 6px;">
                    S/. {{ number_format($propietario->monto_ingreso_total, 2) }}
                </div>
                <div style="font-size: 11.5px; color: var(--text3); font-weight: 600; margin-top: 4px;">
                    {{ $propietario->es_socio ? 'Exonerado de pago' : 'Total amortizado' }}
                </div>
            </div>

            {{-- Saldo Deuda --}}
            <div class="card" style="padding: 18px; border-left: 4px solid {{ $propietario->es_socio ? 'var(--accent)' : ($propietario->monto_ingreso_deuda > 0 ? 'var(--red)' : 'var(--green)') }}; background: var(--card);">
                <div style="font-size: 12px; font-weight: 700; color: var(--text3); text-transform: uppercase;">Saldo Pendiente (Deuda)</div>
                <div style="font-size: 20px; font-weight: 900; color: {{ $propietario->es_socio ? 'var(--accent)' : ($propietario->monto_ingreso_deuda > 0 ? 'var(--red)' : 'var(--green)') }}; margin-top: 6px;">
                    S/. {{ number_format($propietario->monto_ingreso_deuda, 2) }}
                </div>
                <div style="font-size: 11.5px; font-weight: 700; margin-top: 4px;">
                    @if($propietario->es_socio)
                        <span style="color: var(--accent);"><i class="fa-solid fa-circle-check"></i> Socio Exonerado</span>
                    @elseif($propietario->monto_ingreso_deuda > 0)
                        <span style="color: var(--red);"><i class="fa-solid fa-circle-exclamation"></i> Deuda Pendiente</span>
                    @else
                        <span style="color: var(--green);"><i class="fa-solid fa-circle-check"></i> Totalmente Cancelado</span>
                    @endif
                </div>
            </div>
        </div>

        {{-- 3. CUERPO PRINCIPAL EN DOS COLUMNAS --}}
        <div class="g-2-1" style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px;">
            
            {{-- COLUMNA PRINCIPAL (IZQUIERDA) --}}
            <div class="flex-v" style="gap: 24px;">
                
                {{-- Bloque: Datos Personales y de Identificación --}}
                <div class="card">
                    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                        <div class="card-title" style="display: flex; align-items: center; gap: 8px;">
                            <i class="fa-solid fa-address-card" style="color: var(--accent);"></i>
                            Datos de Identificación y Contacto
                        </div>
                    </div>
                    <div class="card-body" style="padding: 0;">
                        <table class="tbl">
                            <tbody>
                                <tr>
                                    <td style="width: 200px; color: var(--text3); font-weight: 700;">Condición / Tipo</td>
                                    <td>
                                        @if($propietario->es_socio)
                                            <span class="pill blue" style="font-weight: 800; font-size: 11.5px;">
                                                <i class="fa-solid fa-star"></i> SOCIO DE LA EMPRESA (Exonerado de Ingreso)
                                            </span>
                                        @else
                                            <span class="pill gray" style="font-weight: 700; font-size: 11.5px;">
                                                Persona / Tercero Normal
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td style="color: var(--text3); font-weight: 700;">DNI / RUC</td>
                                    <td>
                                        <span class="mono" style="font-weight: 800; font-size: 14px;">{{ $propietario->dni ?? 'No especificado' }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="color: var(--text3); font-weight: 700;">Teléfono / Celular</td>
                                    <td>
                                        @if($propietario->telefono)
                                            <div style="display: flex; align-items: center; gap: 12px;">
                                                <a href="tel:{{ $propietario->telefono }}" style="text-decoration: none; color: var(--text); font-weight: 800;">
                                                    <i class="fa-solid fa-phone" style="font-size: 12px; color: var(--green); margin-right: 4px;"></i>
                                                    {{ $propietario->telefono }}
                                                </a>
                                                <a href="https://wa.me/51{{ $propietario->telefono }}" target="_blank" class="pill green" style="text-decoration: none; font-size: 10px; font-weight: 800;">
                                                    <i class="fa-brands fa-whatsapp"></i> WhatsApp
                                                </a>
                                            </div>
                                        @else
                                            <span style="color: var(--text3);">No registrado</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td style="color: var(--text3); font-weight: 700;">Correo Electrónico</td>
                                    <td>
                                        @if($propietario->email)
                                            <a href="mailto:{{ $propietario->email }}" style="text-decoration: none; color: var(--accent); font-weight: 600;">
                                                {{ $propietario->email }}
                                            </a>
                                        @else
                                            <span style="color: var(--text3);">No registrado</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td style="color: var(--text3); font-weight: 700;">Domicilio Fiscal</td>
                                    <td>{{ $propietario->direccion ?? 'No especificado' }}</td>
                                </tr>
                                @if($propietario->notas)
                                    <tr>
                                        <td style="color: var(--text3); font-weight: 700;">Observaciones</td>
                                        <td style="font-size: 12.5px; color: var(--text2); font-style: italic;">{{ $propietario->notas }}</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Bloque: Control de Monto de Ingreso por Vehículo --}}
                @if($propietario->es_socio)
                    {{-- Banner Exonerado para Socio --}}
                    <div class="card" style="border: 1.5px solid #bfdbfe; background: #eff6ff;">
                        <div class="card-body" style="padding: 20px;">
                            <div style="display: flex; gap: 16px; align-items: flex-start;">
                                <div style="width: 44px; height: 44px; border-radius: 12px; background: #dbeafe; display: flex; align-items: center; justify-content: center; color: #1d4ed8; font-size: 20px; flex-shrink: 0;">
                                    <i class="fa-solid fa-shield-halved"></i>
                                </div>
                                <div>
                                    <h4 style="color: #1e40af; font-weight: 800; font-size: 16px; margin: 0 0 6px 0;">
                                        Socio de la Empresa — Exonerado
                                    </h4>
                                    <p style="color: #1e3a8a; font-size: 13px; margin: 0; line-height: 1.5;">
                                        Por su condición oficial de <b>Socio de la Empresa</b>, no se genera cobro de monto de ingreso por registrar su vehículo actual ni por ningún vehículo adicional que se le asigne.
                                    </p>
                                    <div style="margin-top: 12px; display: flex; gap: 12px; align-items: center;">
                                        <span class="pill blue" style="font-size: 12px; font-weight: 900; background: #dbeafe; color: #1d4ed8; padding: 4px 12px;">
                                            EXONERADO (SOCIO)
                                        </span>
                                        <span class="pill green" style="font-size: 12px; font-weight: 800;">
                                            DEUDA: S/. 0.00
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @else
                    {{-- Propietario Normal sin vehículos --}}
                    @if($totalVehiculos === 0)
                        <div class="card">
                            <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                                <div class="card-title" style="display: flex; align-items: center; gap: 8px;">
                                    <i class="fa-solid fa-hand-holding-dollar" style="color: var(--accent);"></i>
                                    Control de Monto de Ingreso
                                </div>
                                @php
                                    $estado = $propietario->estado_ingreso;
                                    $badgeClass = $estado === 'PAGADO' ? 'green' : 'red';
                                @endphp
                                <span class="pill {{ $badgeClass }}" style="font-weight: 800; font-size: 11px;">
                                    {{ $estado }}
                                </span>
                            </div>
                            <div class="card-body" style="padding: 0;">
                                <table class="tbl">
                                    <thead>
                                        <tr>
                                            <th style="width: 180px;">Concepto</th>
                                            <th>Monto Pagado</th>
                                            <th>Fecha de Pago</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td style="color: var(--text3); font-weight: 700;">Monto Inicial</td>
                                            <td style="font-weight: 800; font-size: 13.5px;">S/. {{ number_format($propietario->monto_inicial ?? 0, 2) }}</td>
                                            <td>
                                                @if($propietario->fecha_monto_inicial)
                                                    <i class="fa-solid fa-calendar-day" style="color: var(--accent); font-size: 11px; margin-right: 4px;"></i>
                                                    {{ $propietario->fecha_monto_inicial->format('d/m/Y') }}
                                                @else
                                                    <span style="color: var(--text3); font-size: 12px;">—</span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="color: var(--text3); font-weight: 700;">Cuota 1</td>
                                            <td style="font-weight: 800; font-size: 13.5px;">S/. {{ number_format($propietario->cuota_1 ?? 0, 2) }}</td>
                                            <td>
                                                @if($propietario->fecha_cuota_1)
                                                    <i class="fa-solid fa-calendar-day" style="color: var(--accent); font-size: 11px; margin-right: 4px;"></i>
                                                    {{ $propietario->fecha_cuota_1->format('d/m/Y') }}
                                                @else
                                                    <span style="color: var(--text3); font-size: 12px;">—</span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="color: var(--text3); font-weight: 700;">Cuota 2</td>
                                            <td style="font-weight: 800; font-size: 13.5px;">S/. {{ number_format($propietario->cuota_2 ?? 0, 2) }}</td>
                                            <td>
                                                @if($propietario->fecha_cuota_2)
                                                    <i class="fa-solid fa-calendar-day" style="color: var(--accent); font-size: 11px; margin-right: 4px;"></i>
                                                    {{ $propietario->fecha_cuota_2->format('d/m/Y') }}
                                                @else
                                                    <span style="color: var(--text3); font-size: 12px;">—</span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="color: var(--text3); font-weight: 700;">Cuota 3</td>
                                            <td style="font-weight: 800; font-size: 13.5px;">S/. {{ number_format($propietario->cuota_3 ?? 0, 2) }}</td>
                                            <td>
                                                @if($propietario->fecha_cuota_3)
                                                    <i class="fa-solid fa-calendar-day" style="color: var(--accent); font-size: 11px; margin-right: 4px;"></i>
                                                    {{ $propietario->fecha_cuota_3->format('d/m/Y') }}
                                                @else
                                                    <span style="color: var(--text3); font-size: 12px;">—</span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr style="background: var(--bg); font-weight: 800;">
                                            <td style="color: var(--text); font-weight: 800;">Total Recaudado</td>
                                            <td style="color: var(--accent); font-weight: 800; font-size: 14px;" colspan="2">
                                                S/. {{ number_format($propietario->monto_ingreso_total, 2) }}
                                            </td>
                                        </tr>
                                        @if($propietario->monto_ingreso_deuda > 0)
                                            <tr style="background: #fef2f2; color: #b91c1c; font-weight: 800;">
                                                <td style="font-weight: 800;">Saldo Pendiente (Deuda)</td>
                                                <td style="font-weight: 800; font-size: 14px;" colspan="2">
                                                    S/. {{ number_format($propietario->monto_ingreso_deuda, 2) }}
                                                </td>
                                            </tr>
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @else
                        {{-- Propietario con 1 o más vehículos asignados --}}
                        <div class="flex-v" style="gap: 16px;">
                            <div style="font-size: 15px; font-weight: 800; color: var(--text); display: flex; align-items: center; justify-content: space-between;">
                                <span><i class="fa-solid fa-hand-holding-dollar" style="color: var(--accent); margin-right: 6px;"></i> Detalle de Monto de Ingreso por Vehículo</span>
                            </div>

                            @foreach($propietario->vehiculos as $v)
                                <div class="card" style="border: 1px solid var(--border); overflow: hidden;">
                                    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; background: var(--bg);">
                                        <div style="display: flex; align-items: center; gap: 10px;">
                                            <div style="font-weight: 900; font-size: 15px; color: var(--accent);">
                                                Flota #{{ $v->numero_flota }}
                                            </div>
                                            <div class="mono" style="font-weight: 800; font-size: 13px; color: var(--text);">
                                                {{ $v->placa }}
                                            </div>
                                            <span style="font-size: 12px; color: var(--text3);">({{ $v->marca }} {{ $v->modelo }})</span>
                                        </div>
                                        <div>
                                            @if($v->monto_ingreso_deuda <= 0)
                                                <span class="pill green" style="font-weight: 800; font-size: 11px;">
                                                    PAGADO
                                                </span>
                                            @else
                                                <span class="pill red" style="font-weight: 800; font-size: 11px;">
                                                    DEUDA: S/. {{ number_format($v->monto_ingreso_deuda, 2) }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="card-body" style="padding: 0;">
                                        <table class="tbl">
                                            <thead>
                                                <tr>
                                                    <th style="width: 180px;">Concepto</th>
                                                    <th>Monto Pagado</th>
                                                    <th>Fecha de Pago</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td style="color: var(--text3); font-weight: 700;">Monto Inicial</td>
                                                    <td style="font-weight: 800; font-size: 13.5px;">S/. {{ number_format($v->monto_inicial ?? 0, 2) }}</td>
                                                    <td>
                                                        @if($v->fecha_monto_inicial)
                                                            <i class="fa-solid fa-calendar-day" style="color: var(--accent); font-size: 11px; margin-right: 4px;"></i>
                                                            {{ $v->fecha_monto_inicial->format('d/m/Y') }}
                                                        @else
                                                            <span style="color: var(--text3); font-size: 12px;">—</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td style="color: var(--text3); font-weight: 700;">Cuota 1</td>
                                                    <td style="font-weight: 800; font-size: 13.5px;">S/. {{ number_format($v->cuota_1 ?? 0, 2) }}</td>
                                                    <td>
                                                        @if($v->fecha_cuota_1)
                                                            <i class="fa-solid fa-calendar-day" style="color: var(--accent); font-size: 11px; margin-right: 4px;"></i>
                                                            {{ $v->fecha_cuota_1->format('d/m/Y') }}
                                                        @else
                                                            <span style="color: var(--text3); font-size: 12px;">—</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td style="color: var(--text3); font-weight: 700;">Cuota 2</td>
                                                    <td style="font-weight: 800; font-size: 13.5px;">S/. {{ number_format($v->cuota_2 ?? 0, 2) }}</td>
                                                    <td>
                                                        @if($v->fecha_cuota_2)
                                                            <i class="fa-solid fa-calendar-day" style="color: var(--accent); font-size: 11px; margin-right: 4px;"></i>
                                                            {{ $v->fecha_cuota_2->format('d/m/Y') }}
                                                        @else
                                                            <span style="color: var(--text3); font-size: 12px;">—</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td style="color: var(--text3); font-weight: 700;">Cuota 3</td>
                                                    <td style="font-weight: 800; font-size: 13.5px;">S/. {{ number_format($v->cuota_3 ?? 0, 2) }}</td>
                                                    <td>
                                                        @if($v->fecha_cuota_3)
                                                            <i class="fa-solid fa-calendar-day" style="color: var(--accent); font-size: 11px; margin-right: 4px;"></i>
                                                            {{ $v->fecha_cuota_3->format('d/m/Y') }}
                                                        @else
                                                            <span style="color: var(--text3); font-size: 12px;">—</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                                <tr style="background: var(--bg); font-weight: 800;">
                                                    <td style="color: var(--text); font-weight: 800;">Total Recaudado en Unidad</td>
                                                    <td style="color: var(--accent); font-weight: 800; font-size: 14px;" colspan="2">
                                                        S/. {{ number_format($v->monto_ingreso_total, 2) }}
                                                    </td>
                                                </tr>
                                                @if($v->monto_ingreso_deuda > 0)
                                                    <tr style="background: #fef2f2; color: #b91c1c; font-weight: 800;">
                                                        <td style="font-weight: 800;">Saldo Pendiente (Deuda Unidad)</td>
                                                        <td style="font-weight: 800; font-size: 14px;" colspan="2">
                                                            S/. {{ number_format($v->monto_ingreso_deuda, 2) }}
                                                        </td>
                                                    </tr>
                                                @endif
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                @endif

                {{-- Bloque: Padrón de Vehículos Asociados --}}
                <div class="card">
                    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                        <div class="card-title" style="display: flex; align-items: center; gap: 8px;">
                            <i class="fa-solid fa-bus" style="color: var(--accent);"></i>
                            Padrón de Vehículos Asociados
                        </div>
                        <span class="pill blue" style="font-size: 11px; font-weight: 800;">{{ $totalVehiculos }} {{ $totalVehiculos === 1 ? 'Vehículo' : 'Vehículos' }}</span>
                    </div>
                    <div class="tbl-wrap">
                        <table class="tbl tbl-modern">
                            <thead>
                                <tr>
                                    <th>Flota / Placa</th>
                                    <th>Marca & Modelo</th>
                                    <th>Año</th>
                                    <th>Estado Operativo</th>
                                    <th>Estado Ingreso</th>
                                    <th style="text-align: right;">Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($propietario->vehiculos as $v)
                                    <tr>
                                        <td>
                                            <div class="flex-v" style="gap:2px;">
                                                <div style="font-weight: 900; color: var(--accent); font-size: 14px;">#{{ $v->numero_flota }}</div>
                                                <div class="mono" style="font-size: 11.5px; font-weight: 700;">{{ $v->placa }}</div>
                                            </div>
                                        </td>
                                        <td>
                                            <div style="font-size: 13px; font-weight: 700;">{{ $v->marca }} {{ $v->modelo }}</div>
                                        </td>
                                        <td>
                                            <div class="mono" style="font-size: 12px; font-weight: 600;">{{ $v->anio ?? '---' }}</div>
                                        </td>
                                        <td>
                                            <span class="pill {{ $v->estado === 'activo' ? 'green' : 'orange' }}" style="font-size: 10px; font-weight: 800;">
                                                {{ strtoupper($v->estado) }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($propietario->es_socio)
                                                <span class="pill blue" style="font-size: 10px; font-weight: 800;">
                                                    EXONERADO
                                                </span>
                                            @elseif($v->monto_ingreso_deuda <= 0)
                                                <span class="pill green" style="font-size: 10px; font-weight: 800;">
                                                    PAGADO
                                                </span>
                                            @else
                                                <span class="pill red" style="font-size: 10px; font-weight: 800;">
                                                    DEUDA: S/. {{ number_format($v->monto_ingreso_deuda, 2) }}
                                                </span>
                                            @endif
                                        </td>
                                        <td style="text-align: right;">
                                            <a href="{{ route('vehiculos.show', $v->id) }}" class="action-icon show-icon" title="Ver Vehículo">
                                                <i class="fa-solid fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" style="text-align: center; padding: 40px; color: var(--text3);">
                                            <i class="fa-solid fa-bus" style="font-size: 32px; opacity: 0.15; display: block; margin-bottom: 10px;"></i>
                                            Este propietario aún no tiene vehículos registrados o vinculados.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- COLUMNA LATERAL (DERECHA) --}}
            <aside class="flex-v" style="gap: 24px;">
                
                {{-- Resumen Financiero Consolidado --}}
                <div class="card" style="border-top: 4px solid var(--accent);">
                    <div class="card-header">
                        <div class="card-title" style="font-size: 14px;">Resumen de Ingresos</div>
                    </div>
                    <div class="card-body" style="padding: 16px;">
                        <div style="display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px dashed var(--border); font-size: 13px;">
                            <span style="color: var(--text3);">Total Unidades:</span>
                            <span style="font-weight: 800;">{{ $totalVehiculos }}</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px dashed var(--border); font-size: 13px;">
                            <span style="color: var(--text3);">Total Abonado:</span>
                            <span style="font-weight: 800; color: var(--green);">S/. {{ number_format($propietario->monto_ingreso_total, 2) }}</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; padding: 8px 0; font-size: 14px; font-weight: 900;">
                            <span style="color: var(--text);">Saldo Deuda Total:</span>
                            <span style="color: {{ $propietario->es_socio ? 'var(--accent)' : ($propietario->monto_ingreso_deuda > 0 ? 'var(--red)' : 'var(--green)') }};">
                                S/. {{ number_format($propietario->monto_ingreso_deuda, 2) }}
                            </span>
                        </div>

                        <a href="{{ route('reportes.deudas', ['tipo' => 'monto_ingreso', 'propietario_id' => $propietario->id]) }}" class="btn-primary" style="width: 100%; justify-content: center; margin-top: 15px; text-decoration: none; font-size: 12.5px;">
                            <i class="fa-solid fa-chart-pie"></i> Ver en Reporte de Deudas
                        </a>
                    </div>
                </div>

                {{-- Conductor Vinculado --}}
                @if($propietario->conductor)
                    <div class="card" style="border-left: 4px solid var(--gold);">
                        <div class="card-header">
                            <div class="card-title" style="font-size: 14px;">Perfil de Conducción Activo</div>
                        </div>
                        <div class="card-body flex-v" style="gap: 10px; padding: 16px;">
                            <div style="font-size: 12.5px; color: var(--text2);">
                                Este socio opera vehículos en el sistema como chofer activo.
                            </div>
                            <div style="font-size: 12px; color: var(--text3);">
                                <b>Licencia:</b> {{ $propietario->conductor->licencia ?? '---' }}
                            </div>
                            <a href="{{ route('conductores.show', $propietario->conductor->id) }}" class="btn-secondary" style="justify-content: center; font-size: 12px; margin-top: 5px; text-decoration: none;">
                                <i class="fa-solid fa-address-card"></i> Ver Historial de Conductor
                            </a>
                        </div>
                    </div>
                @endif

                {{-- Trazabilidad / Auditoría --}}
                <div class="card">
                    <div class="card-header">
                        <div class="card-title" style="font-size: 13px; color: var(--text3);">Trazabilidad</div>
                    </div>
                    <div class="card-body" style="font-size: 11.5px; color: var(--text3); padding: 14px 18px; display: flex; flex-direction: column; gap: 8px;">
                        <div><b>Registrado:</b> {{ $propietario->created_at ? $propietario->created_at->format('d/m/Y h:i A') : '---' }}</div>
                        <div><b>Última actualización:</b> {{ $propietario->updated_at ? $propietario->updated_at->format('d/m/Y h:i A') : '---' }}</div>
                    </div>
                </div>

            </aside>
        </div>
    </div>
@endsection
