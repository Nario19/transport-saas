@extends('layouts.admin')

@php
    $pageTitle = 'Expediente de Propietario';
    $pageSubtitle = $propietario->nombre_completo;
    $totalVehiculos = $propietario->vehiculos->count();
@endphp

@section('back_url', route('propietarios.index'))

@section('content')
    <div class="panel" style="max-width: 1000px; margin: 0 auto; display: flex; flex-direction: column; gap: 20px;">
        
        {{-- 1. CABECERA CON NOMBRE Y ACCIONES --}}
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
            <div>
                <h2 style="font-size: 22px; font-weight: 800; color: var(--text); margin: 0 0 6px 0;">
                    {{ $propietario->nombre }} {{ $propietario->apellidos }}
                </h2>
                <div style="display: flex; gap: 8px; align-items: center; flex-wrap: wrap;">
                    <span class="pill {{ $propietario->activo ? 'green' : 'red' }}" style="font-weight: 800; font-size: 11px;">
                        {{ $propietario->activo ? 'ACTIVO' : 'INACTIVO' }}
                    </span>
                    @if($propietario->es_socio)
                        <span class="pill blue" style="font-size: 11px; font-weight: 800;">
                            <i class="fa-solid fa-star"></i> SOCIO DE LA EMPRESA (EXONERADO)
                        </span>
                    @else
                        <span class="pill gray" style="font-size: 11px; font-weight: 700;">
                            Persona Normal
                        </span>
                    @endif
                    @if($propietario->conductor)
                        <span class="pill gold" style="font-size: 11px; font-weight: 800;">
                            <i class="fa-solid fa-id-card"></i> SOCIO-CONDUCTOR
                        </span>
                    @endif
                </div>
            </div>
            <div style="display: flex; gap: 10px;">
                <a href="{{ route('propietarios.edit', $propietario->id) }}" class="btn-primary" style="text-decoration: none;">
                    <i class="fa-solid fa-user-pen"></i> Editar Propietario
                </a>
            </div>
        </div>

        {{-- 2. DATOS DE IDENTIFICACIÓN Y CONTACTO --}}
        <div class="card">
            <div class="card-header">
                <div class="card-title" style="font-size: 15px;">Datos del Propietario</div>
            </div>
            <div class="card-body" style="padding: 16px 20px;">
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px;">
                    <div>
                        <div style="font-size: 11px; color: var(--text3); font-weight: 700; text-transform: uppercase;">DNI / RUC</div>
                        <div class="mono" style="font-weight: 800; font-size: 14px; color: var(--text); margin-top: 3px;">
                            {{ $propietario->dni ?? '—' }}
                        </div>
                    </div>
                    <div>
                        <div style="font-size: 11px; color: var(--text3); font-weight: 700; text-transform: uppercase;">Teléfono / Celular</div>
                        <div style="font-weight: 800; font-size: 14px; margin-top: 3px; display: flex; align-items: center; gap: 8px;">
                            @if($propietario->telefono)
                                <a href="tel:{{ $propietario->telefono }}" style="text-decoration: none; color: var(--text);">
                                    <i class="fa-solid fa-phone" style="color: var(--green); font-size: 12px;"></i> {{ $propietario->telefono }}
                                </a>
                                <a href="https://wa.me/51{{ $propietario->telefono }}" target="_blank" style="text-decoration: none; color: #16a34a; font-size: 13px;" title="Abrir WhatsApp">
                                    <i class="fa-brands fa-whatsapp"></i>
                                </a>
                            @else
                                <span style="color: var(--text3);">—</span>
                            @endif
                        </div>
                    </div>
                    <div>
                        <div style="font-size: 11px; color: var(--text3); font-weight: 700; text-transform: uppercase;">Correo Electrónico</div>
                        <div style="font-weight: 600; font-size: 13px; color: var(--text); margin-top: 3px;">
                            {{ $propietario->email ?? '—' }}
                        </div>
                    </div>
                    <div>
                        <div style="font-size: 11px; color: var(--text3); font-weight: 700; text-transform: uppercase;">Domicilio</div>
                        <div style="font-weight: 600; font-size: 13px; color: var(--text); margin-top: 3px;">
                            {{ $propietario->direccion ?? '—' }}
                        </div>
                    </div>
                </div>

                @if($propietario->notas)
                    <div style="margin-top: 14px; padding-top: 12px; border-top: 1px dashed var(--border); font-size: 12.5px; color: var(--text2);">
                        <b>Observaciones:</b> {{ $propietario->notas }}
                    </div>
                @endif
            </div>
        </div>

        {{-- 3. CONTROL DE MONTO DE INGRESO Y VEHÍCULOS --}}
        @if($propietario->es_socio)
            {{-- Socio Exonerado --}}
            <div class="card" style="border-left: 4px solid var(--accent);">
                <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                    <div class="card-title" style="font-size: 15px;">Control de Monto de Ingreso</div>
                    <span class="pill blue" style="font-weight: 900; font-size: 11px;">EXONERADO (SOCIO)</span>
                </div>
                <div class="card-body" style="padding: 16px 20px; font-size: 13px; color: var(--text2);">
                    <i class="fa-solid fa-circle-check" style="color: #2563eb; margin-right: 6px;"></i>
                    Este propietario es <b>Socio de la Empresa</b>, por lo que está <b>exonerado de cuotas de ingreso</b> en todas sus unidades.
                </div>
            </div>

            {{-- Padrón de Vehículos para Socio --}}
            <div class="card">
                <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                    <div class="card-title" style="font-size: 15px;">Vehículos Asignados ({{ $totalVehiculos }})</div>
                </div>
                <div class="card-body" style="padding: 0;">
                    <table class="tbl">
                        <thead>
                            <tr>
                                <th>Flota</th>
                                <th>Placa</th>
                                <th>Marca / Modelo</th>
                                <th>Año</th>
                                <th>Estado</th>
                                <th style="text-align: right;">Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($propietario->vehiculos as $v)
                                <tr>
                                    <td style="font-weight: 900; color: var(--accent);">#{{ $v->numero_flota }}</td>
                                    <td class="mono" style="font-weight: 800;">{{ $v->placa }}</td>
                                    <td>{{ $v->marca }} {{ $v->modelo }}</td>
                                    <td class="mono">{{ $v->anio ?? '—' }}</td>
                                    <td>
                                        <span class="pill {{ $v->estado === 'activo' ? 'green' : 'orange' }}" style="font-size: 10px;">
                                            {{ strtoupper($v->estado) }}
                                        </span>
                                    </td>
                                    <td style="text-align: right;">
                                        <a href="{{ route('vehiculos.show', $v->id) }}" class="action-icon show-icon" title="Ver Vehículo">
                                            <i class="fa-solid fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" style="text-align: center; padding: 25px; color: var(--text3);">
                                        No tiene vehículos asignados actualmente.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @else
            {{-- Propietario Normal: Tabla de Control de Ingreso por Unidad --}}
            <div class="card">
                <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                    <div class="card-title" style="font-size: 15px;">Control de Monto de Ingreso por Vehículo</div>
                    @if($propietario->monto_ingreso_deuda > 0)
                        <span class="pill red" style="font-weight: 800; font-size: 11px;">
                            DEUDA TOTAL: S/. {{ number_format($propietario->monto_ingreso_deuda, 2) }}
                        </span>
                    @else
                        <span class="pill green" style="font-weight: 800; font-size: 11px;">
                            AL DÍA (CANCELADO)
                        </span>
                    @endif
                </div>
                <div class="card-body" style="padding: 0;">
                    <table class="tbl">
                        <thead>
                            <tr>
                                <th>Vehículo</th>
                                <th>Monto Inicial</th>
                                <th>Cuota 1</th>
                                <th>Cuota 2</th>
                                <th>Cuota 3</th>
                                <th>Total Pagado</th>
                                <th>Deuda</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if($totalVehiculos === 0)
                                <tr>
                                    <td style="color: var(--text3); font-style: italic;">Sin vehículo</td>
                                    <td>
                                        <div style="font-weight: 700;">S/. {{ number_format($propietario->monto_inicial ?? 0, 2) }}</div>
                                        <div style="font-size: 10px; color: var(--text3);">{{ $propietario->fecha_monto_inicial ? $propietario->fecha_monto_inicial->format('d/m/Y') : '—' }}</div>
                                    </td>
                                    <td>
                                        <div style="font-weight: 700;">S/. {{ number_format($propietario->cuota_1 ?? 0, 2) }}</div>
                                        <div style="font-size: 10px; color: var(--text3);">{{ $propietario->fecha_cuota_1 ? $propietario->fecha_cuota_1->format('d/m/Y') : '—' }}</div>
                                    </td>
                                    <td>
                                        <div style="font-weight: 700;">S/. {{ number_format($propietario->cuota_2 ?? 0, 2) }}</div>
                                        <div style="font-size: 10px; color: var(--text3);">{{ $propietario->fecha_cuota_2 ? $propietario->fecha_cuota_2->format('d/m/Y') : '—' }}</div>
                                    </td>
                                    <td>
                                        <div style="font-weight: 700;">S/. {{ number_format($propietario->cuota_3 ?? 0, 2) }}</div>
                                        <div style="font-size: 10px; color: var(--text3);">{{ $propietario->fecha_cuota_3 ? $propietario->fecha_cuota_3->format('d/m/Y') : '—' }}</div>
                                    </td>
                                    <td style="font-weight: 800; color: var(--green);">
                                        S/. {{ number_format($propietario->monto_ingreso_total, 2) }}
                                    </td>
                                    <td style="font-weight: 800; color: {{ $propietario->monto_ingreso_deuda > 0 ? 'var(--red)' : 'var(--green)' }};">
                                        S/. {{ number_format($propietario->monto_ingreso_deuda, 2) }}
                                    </td>
                                    <td>
                                        <span class="pill {{ $propietario->monto_ingreso_deuda > 0 ? 'red' : 'green' }}" style="font-size: 10px; font-weight: 800;">
                                            {{ $propietario->monto_ingreso_deuda > 0 ? 'DEUDA' : 'PAGADO' }}
                                        </span>
                                    </td>
                                </tr>
                            @else
                                @foreach($propietario->vehiculos as $v)
                                    <tr>
                                        <td>
                                            <a href="{{ route('vehiculos.show', $v->id) }}" style="text-decoration: none; display: flex; flex-direction: column; gap: 2px;">
                                                <span style="font-weight: 900; color: var(--accent); font-size: 13.5px;">Flota #{{ $v->numero_flota }}</span>
                                                <span class="mono" style="font-size: 11px; color: var(--text2); font-weight: 700;">{{ $v->placa }}</span>
                                            </a>
                                        </td>
                                        <td>
                                            <div style="font-weight: 700;">S/. {{ number_format($v->monto_inicial ?? 0, 2) }}</div>
                                            <div style="font-size: 10px; color: var(--text3);">{{ $v->fecha_monto_inicial ? $v->fecha_monto_inicial->format('d/m/Y') : '—' }}</div>
                                        </td>
                                        <td>
                                            <div style="font-weight: 700;">S/. {{ number_format($v->cuota_1 ?? 0, 2) }}</div>
                                            <div style="font-size: 10px; color: var(--text3);">{{ $v->fecha_cuota_1 ? $v->fecha_cuota_1->format('d/m/Y') : '—' }}</div>
                                        </td>
                                        <td>
                                            <div style="font-weight: 700;">S/. {{ number_format($v->cuota_2 ?? 0, 2) }}</div>
                                            <div style="font-size: 10px; color: var(--text3);">{{ $v->fecha_cuota_2 ? $v->fecha_cuota_2->format('d/m/Y') : '—' }}</div>
                                        </td>
                                        <td>
                                            <div style="font-weight: 700;">S/. {{ number_format($v->cuota_3 ?? 0, 2) }}</div>
                                            <div style="font-size: 10px; color: var(--text3);">{{ $v->fecha_cuota_3 ? $v->fecha_cuota_3->format('d/m/Y') : '—' }}</div>
                                        </td>
                                        <td style="font-weight: 800; color: var(--green);">
                                            S/. {{ number_format($v->monto_ingreso_total, 2) }}
                                        </td>
                                        <td style="font-weight: 800; color: {{ $v->monto_ingreso_deuda > 0 ? 'var(--red)' : 'var(--green)' }};">
                                            S/. {{ number_format($v->monto_ingreso_deuda, 2) }}
                                        </td>
                                        <td>
                                            <span class="pill {{ $v->monto_ingreso_deuda > 0 ? 'red' : 'green' }}" style="font-size: 10px; font-weight: 800;">
                                                {{ $v->monto_ingreso_deuda > 0 ? 'DEUDA' : 'PAGADO' }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            @endif
                        </tbody>
                        @if($totalVehiculos > 1)
                            <tfoot>
                                <tr style="background: var(--bg); font-weight: 800; border-top: 2px solid var(--border);">
                                    <td colspan="5" style="text-align: right; color: var(--text);">Totales Consolidados:</td>
                                    <td style="color: var(--green); font-size: 13.5px;">
                                        S/. {{ number_format($propietario->monto_ingreso_total, 2) }}
                                    </td>
                                    <td style="color: {{ $propietario->monto_ingreso_deuda > 0 ? 'var(--red)' : 'var(--green)' }}; font-size: 13.5px;" colspan="2">
                                        S/. {{ number_format($propietario->monto_ingreso_deuda, 2) }}
                                    </td>
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                </div>
            </div>
        @endif

        {{-- 4. ACCESO A LA APLICACIÓN MÓVIL / WEB (DNI) --}}
        <div class="card" style="border-top: 4px solid var(--accent);">
            <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                <div class="card-title"><i class="fa-solid fa-mobile-screen-button"></i> Acceso Móvil / Web de Propietario</div>
                @if($propietario->user)
                    <span class="pill {{ $propietario->user->activo ? 'green' : 'red' }}">
                        {{ $propietario->user->activo ? 'HABILITADO' : 'SUSPENDIDO' }}
                    </span>
                @else
                    <span class="pill gray">SIN ACCESO</span>
                @endif
            </div>
            <div class="card-body">
                @if($propietario->user)
                    <div style="display: flex; flex-direction: column; gap: 14px;">
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 12px; background: var(--bg); padding: 14px; border-radius: 10px;">
                            <div>
                                <span style="font-size: 11px; text-transform: uppercase; color: var(--text3); font-weight: 700;">Usuario (DNI)</span>
                                <div class="mono" style="font-size: 15px; font-weight: 800; color: var(--text);">
                                    {{ $propietario->user->email }}
                                </div>
                            </div>
                            <div>
                                <span style="font-size: 11px; text-transform: uppercase; color: var(--text3); font-weight: 700;">Estado Contraseña</span>
                                <div style="font-size: 13px; font-weight: 700; color: {{ $propietario->primer_ingreso ? 'var(--orange)' : 'var(--green)' }};">
                                    {{ $propietario->primer_ingreso ? '🔑 Clave inicial (Pendiente de cambio)' : '✅ Clave personalizada' }}
                                </div>
                            </div>
                        </div>

                        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                            <form method="POST" action="{{ route('propietarios.acceso.toggle', $propietario->id) }}">
                                @csrf
                                <button type="submit" class="btn-secondary {{ $propietario->user->activo ? 'text-red' : 'text-green' }}">
                                    @if($propietario->user->activo)
                                        <i class="fa-solid fa-power-off"></i> Suspender Acceso
                                    @else
                                        <i class="fa-solid fa-circle-play"></i> Activar Acceso
                                    @endif
                                </button>
                            </form>

                            <form method="POST" action="{{ route('propietarios.acceso.reset', $propietario->id) }}" onsubmit="return confirm('¿Reiniciar la contraseña del propietario a su DNI?')">
                                @csrf
                                <button type="submit" class="btn-secondary">
                                    <i class="fa-solid fa-key"></i> Reiniciar Contraseña a DNI
                                </button>
                            </form>

                            <form method="POST" action="{{ route('propietarios.acceso.destroy', $propietario->id) }}" onsubmit="return confirm('¿Eliminar por completo las credenciales de este propietario?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn-danger btn-sm" style="border-radius: 8px;">
                                    <i class="fa-solid fa-trash"></i> Eliminar Acceso
                                </button>
                            </form>
                        </div>
                    </div>
                @else
                    <div style="display: flex; flex-direction: column; gap: 12px;">
                        <div style="background: var(--bg); padding: 14px; border-radius: 10px; border: 1px dashed var(--border);">
                            <div style="font-size: 13px; font-weight: 700; margin-bottom: 4px;">Habilitar Cuenta para Propietario</div>
                            <div style="font-size: 12px; color: var(--text2);">
                                Se generará el acceso utilizando su <b>DNI ({{ $propietario->dni ?? 'Sin DNI' }})</b> como usuario y contraseña inicial. Podrá consultar sus vueltas, tributos, sanciones y monto de ingreso desde su celular.
                            </div>
                        </div>

                        @if(!empty($propietario->dni))
                            <form method="POST" action="{{ route('propietarios.acceso.store', $propietario->id) }}">
                                @csrf
                                <button type="submit" class="btn-primary" style="width: 100%; justify-content: center; display: inline-flex; align-items: center; gap: 8px;">
                                    <i class="fa-solid fa-mobile-screen-button"></i> Habilitar Acceso con DNI
                                </button>
                            </form>
                        @else
                            <div style="color: var(--red); font-size: 12.5px; font-weight: 700;">
                                <i class="fa-solid fa-triangle-exclamation"></i> Para habilitar el acceso primero registra el DNI del propietario.
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        </div>

    </div>
@endsection
