@extends('layouts.admin')

@php
    $pageTitle = 'Expediente de Socio Propietario';
    $pageSubtitle = 'Detalles legales, padrón y control de cuotas de ingreso';
@endphp

@section('back_url', route('propietarios.index'))

@section('content')
    <div class="panel">
        
        {{-- 1. CABECERA CON PERFIL Y ACCIONES --}}
        <div class="card-header-actions" style="margin-bottom: 24px;">
            <div class="flex-h" style="gap: 16px; align-items: center;">
                <div class="avatar" style="width: 54px; height: 54px; font-size: 20px; background: var(--surface2); border: 2px solid var(--border);">
                    {{ strtoupper(substr($propietario->nombre, 0, 1) . substr($propietario->apellidos, 0, 1)) }}
                </div>
                <div class="flex-v" style="gap: 4px;">
                    <h2 style="font-size: 24px; font-weight: 800; color: var(--text);">{{ $propietario->nombre }} {{ $propietario->apellidos }}</h2>
                    <div class="flex-h" style="gap: 10px; align-items: center; flex-wrap: wrap;">
                        <span class="pill {{ $propietario->activo ? 'green' : 'red' }}">
                            {{ $propietario->activo ? 'VIGENTE' : 'INACTIVO' }}
                        </span>
                        @if($propietario->es_socio)
                            <span class="pill blue" style="font-size: 11px; font-weight: 800;">
                                <i class="fa-solid fa-star"></i> SOCIO DE LA EMPRESA
                            </span>
                        @else
                            <span class="pill gray" style="font-size: 11px; font-weight: 700;">
                                Persona / Tercero Normal
                            </span>
                        @endif
                        @if($propietario->conductor)
                            <span class="pill gold" style="font-size: 11px; font-weight: 800;">
                                <i class="fa-solid fa-id-card"></i> SOCIO-CONDUCTOR
                            </span>
                        @endif
                        <span style="font-size: 13px; color: var(--text3);">Socio ID: #{{ $propietario->id }}</span>
                    </div>
                </div>
            </div>
            <div class="flex-h">
                <a href="{{ route('propietarios.edit', $propietario->id) }}" class="btn-primary">
                    <i class="fa-solid fa-user-pen"></i> Editar Perfil
                </a>
            </div>
        </div>

        {{-- 2. CUERPO EN DOS COLUMNAS --}}
        <div class="g-2-1">
            
            {{-- COLUMNA PRINCIPAL (IZQUIERDA) --}}
            <div class="flex-v" style="gap: 24px;">
                
                {{-- Información Personal --}}
                <div class="card">
                    <div class="card-header">
                        <div class="card-title">Datos Personales y Legales</div>
                    </div>
                    <div class="card-body" style="padding: 0;">
                        <table class="tbl">
                            <tbody>
                                <tr>
                                    <td style="width: 220px; color: var(--text3); font-weight: 600;">Condición / Tipo</td>
                                    <td>
                                        @if($propietario->es_socio)
                                            <span class="pill blue" style="font-weight: 800;"><i class="fa-solid fa-star"></i> SOCIO DE LA EMPRESA (Exonerado de Ingreso)</span>
                                        @else
                                            <span class="pill gray" style="font-weight: 700;">Persona / Tercero Normal (Obligado a S/. 600.00)</span>
                                        @endif
                                    </td>
                                </tr>
                                @if($propietario->dni)
                                    <tr>
                                        <td style="color: var(--text3); font-weight: 600;">DNI / RUC</td>
                                        <td><span class="mono">{{ $propietario->dni }}</span></td>
                                    </tr>
                                @endif
                                @if($propietario->telefono)
                                    <tr>
                                        <td style="color: var(--text3); font-weight: 600;">Teléfono de Contacto</td>
                                        <td>
                                            <a href="tel:{{ $propietario->telefono }}" style="text-decoration: none; color: var(--text); font-weight: 700;">
                                                <i class="fa-solid fa-phone" style="font-size: 12px; color: var(--green); margin-right: 5px;"></i>
                                                {{ $propietario->telefono }}
                                            </a>
                                        </td>
                                    </tr>
                                @endif
                                @if($propietario->direccion)
                                    <tr>
                                        <td style="color: var(--text3); font-weight: 600;">Domicilio Fiscal</td>
                                        <td>{{ $propietario->direccion }}</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Control de Monto de Ingreso --}}
                @if($propietario->vehiculos->count() === 0)
                    <div class="card">
                        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                            <div class="card-title">
                                Control de Monto de Ingreso 
                                @if($propietario->es_socio)
                                    <span style="font-size: 13px; color: var(--accent); font-weight: 700;">(SOCIO EXONERADO: S/. 0.00)</span>
                                @else
                                    <span style="font-size: 13px; color: var(--text3); font-weight: 600;">(Total Obligado: S/. 600.00)</span>
                                @endif
                            </div>
                            @php
                                $estado = $propietario->estado_ingreso;
                                $badgeClass = $propietario->es_socio ? 'blue' : ($estado === 'PAGADO' ? 'green' : 'red');
                            @endphp
                            <span class="pill {{ $badgeClass }}" style="font-weight: 800; font-size: 11px;">
                                {{ $estado }}
                            </span>
                        </div>
                        <div class="card-body" style="padding: 0;">
                            @if($propietario->es_socio)
                                <div style="background: #eff6ff; color: #1e40af; padding: 12px 18px; border-bottom: 1px solid #bfdbfe; font-size: 12.5px;">
                                    <i class="fa-solid fa-circle-check" style="margin-right: 6px;"></i>
                                    <b>Socio de la Empresa:</b> No está sujeto al cobro de monto de ingreso (Total Obligado: S/. 0.00).
                                </div>
                            @endif
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
                                        <td style="color: var(--text3); font-weight: 600;">Monto Inicial</td>
                                        <td style="font-weight: 700;">S/. {{ number_format($propietario->monto_inicial ?? 0, 2) }}</td>
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
                                        <td style="color: var(--text3); font-weight: 600;">Cuota 1</td>
                                        <td style="font-weight: 700;">S/. {{ number_format($propietario->cuota_1 ?? 0, 2) }}</td>
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
                                        <td style="color: var(--text3); font-weight: 600;">Cuota 2</td>
                                        <td style="font-weight: 700;">S/. {{ number_format($propietario->cuota_2 ?? 0, 2) }}</td>
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
                                        <td style="color: var(--text3); font-weight: 600;">Cuota 3</td>
                                        <td style="font-weight: 700;">S/. {{ number_format($propietario->cuota_3 ?? 0, 2) }}</td>
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
                                        <td style="color: var(--accent); font-weight: 800;">
                                            S/. {{ number_format($propietario->monto_ingreso_total, 2) }}
                                        </td>
                                        <td>
                                            @if($propietario->es_socio)
                                                <span class="pill blue" style="font-size: 10px;">EXONERADO</span>
                                            @else
                                                <span style="font-size: 11px; color: var(--text3);">de S/. 600.00</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @if(!$propietario->es_socio && $propietario->monto_ingreso_deuda > 0)
                                        <tr style="background: #fef2f2; color: #b91c1c; font-weight: 800;">
                                            <td style="font-weight: 800;">Saldo Pendiente (Deuda)</td>
                                            <td style="font-weight: 800;" colspan="2">S/. {{ number_format($propietario->monto_ingreso_deuda, 2) }}</td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                @else
                    @foreach($propietario->vehiculos as $v)
                        <div class="card" style="margin-bottom: 20px;">
                            <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                                <div class="card-title">
                                    Control de Monto de Ingreso - Vehículo: <span style="color: var(--accent); font-weight: 800;">{{ $v->placa }}</span> 
                                    @if($propietario->es_socio)
                                        <span style="font-size: 13px; color: var(--accent); font-weight: 700;">(SOCIO EXONERADO: S/. 0.00)</span>
                                    @else
                                        <span style="font-size: 13px; color: var(--text3); font-weight: 600;">(Total Obligado: S/. 600.00)</span>
                                    @endif
                                </div>
                                @php
                                    $estado = $v->estado_ingreso;
                                    $badgeClass = $propietario->es_socio ? 'blue' : ($estado === 'PAGADO' ? 'green' : 'red');
                                @endphp
                                <span class="pill {{ $badgeClass }}" style="font-weight: 800; font-size: 11px;">
                                    {{ $estado }}
                                </span>
                            </div>
                            <div class="card-body" style="padding: 0;">
                                @if($propietario->es_socio)
                                    <div style="background: #eff6ff; color: #1e40af; padding: 12px 18px; border-bottom: 1px solid #bfdbfe; font-size: 12.5px;">
                                        <i class="fa-solid fa-circle-check" style="margin-right: 6px;"></i>
                                        <b>Unidad de Socio de la Empresa:</b> Exonerado de cobro de ingreso (S/. 0.00).
                                    </div>
                                @endif
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
                                            <td style="color: var(--text3); font-weight: 600;">Monto Inicial</td>
                                            <td style="font-weight: 700;">S/. {{ number_format($v->monto_inicial ?? 0, 2) }}</td>
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
                                            <td style="color: var(--text3); font-weight: 600;">Cuota 1</td>
                                            <td style="font-weight: 700;">S/. {{ number_format($v->cuota_1 ?? 0, 2) }}</td>
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
                                            <td style="color: var(--text3); font-weight: 600;">Cuota 2</td>
                                            <td style="font-weight: 700;">S/. {{ number_format($v->cuota_2 ?? 0, 2) }}</td>
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
                                            <td style="color: var(--text3); font-weight: 600;">Cuota 3</td>
                                            <td style="font-weight: 700;">S/. {{ number_format($v->cuota_3 ?? 0, 2) }}</td>
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
                                            <td style="color: var(--text); font-weight: 800;">Total Recaudado</td>
                                            <td style="color: var(--accent); font-weight: 800;">
                                                S/. {{ number_format($v->monto_ingreso_total, 2) }}
                                            </td>
                                            <td>
                                                @if($propietario->es_socio)
                                                    <span class="pill blue" style="font-size: 10px;">EXONERADO</span>
                                                @else
                                                    <span style="font-size: 11px; color: var(--text3);">de S/. 600.00</span>
                                                @endif
                                            </td>
                                        </tr>
                                        @if(!$propietario->es_socio && $v->monto_ingreso_deuda > 0)
                                            <tr style="background: #fef2f2; color: #b91c1c; font-weight: 800;">
                                                <td style="font-weight: 800;">Saldo Pendiente (Deuda)</td>
                                                <td style="font-weight: 800;" colspan="2">S/. {{ number_format($v->monto_ingreso_deuda, 2) }}</td>
                                            </tr>
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endforeach
                @endif

                {{-- Tabla de Vehículos Asociados --}}
                <div class="card">
                    <div class="card-header">
                        <div class="card-title">Unidades de Transporte Asociadas</div>
                        <span class="pill blue" style="font-size: 11px;">{{ $propietario->vehiculos->count() }} Vehículos</span>
                    </div>
                    <div class="tbl-wrap">
                        <table class="tbl tbl-modern">
                            <thead>
                                <tr>
                                    <th>Unidad</th>
                                    <th>Marca / Modelo</th>
                                    <th>Año</th>
                                    <th>Estado</th>
                                    <th style="text-align: right;">Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($propietario->vehiculos as $v)
                                    <tr>
                                        <td>
                                            <div class="flex-v" style="gap:2px;">
                                                <div style="font-weight: 800; color: var(--accent);">#{{ $v->numero_flota }}</div>
                                                <div class="mono" style="font-size: 11px;">{{ $v->placa }}</div>
                                            </div>
                                        </td>
                                        <td><div style="font-size: 13px; font-weight: 600;">{{ $v->marca }} {{ $v->modelo }}</div></td>
                                        <td><div class="mono" style="font-size: 12px;">{{ $v->anio }}</div></td>
                                        <td>
                                            <span class="pill {{ $v->estado === 'activo' ? 'green' : 'orange' }}" style="font-size: 10px;">
                                                {{ strtoupper($v->estado) }}
                                            </span>
                                        </td>
                                        <td style="text-align: right;">
                                            <a href="{{ route('vehiculos.show', $v->id) }}" class="action-icon show-icon"><i class="fa-solid fa-eye"></i></a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" style="text-align: center; padding: 40px; color: var(--text3);">
                                            <i class="fa-solid fa-bus" style="font-size: 32px; opacity: 0.1; display: block; margin-bottom: 10px;"></i>
                                            Este socio no tiene vehículos vinculados actualmente.
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
                
                {{-- Resumen Operativo --}}
                <div class="stat blue" style="padding: 24px;">
                    <div class="stat-label">Capacidad de Flota</div>
                    <div class="stat-val" style="font-size: 32px; margin-top: 10px;">{{ $propietario->vehiculos->count() }}</div>
                    <div class="stat-sub">Vehículos en operación</div>
                    <div class="stat-icon"><i class="fa-solid fa-truck-ramp-box"></i></div>
                </div>

                {{-- Acceso a Perfil de Conductor o Habilitación --}}
                @if($propietario->conductor)
                    <div class="card" style="border-left: 4px solid var(--gold);">
                        <div class="card-body flex-v" style="gap: 12px;">
                            <div style="font-weight: 800; font-size: 13px;">Perfil de Conducción Activo</div>
                            <div style="font-size: 11px; color: var(--text3);">Este socio opera unidades en el sistema.</div>
                            <a href="{{ route('conductores.show', $propietario->conductor->id) }}" class="btn-primary btn-sm" style="justify-content: center; background: var(--gold); border: none;">
                                <i class="fa-solid fa-address-card"></i> Ver Historial de Conductor
                            </a>
                        </div>
                    </div>
                @endif
            </aside>
        </div>
    </div>
@endsection
