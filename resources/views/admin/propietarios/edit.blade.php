@extends('layouts.admin')

@section('back_url', route('propietarios.index'))

@section('content')
    <div class="panel">
        <div class="card" style="max-width: 850px; margin: 0 auto;">
            <div class="card-header">
                <div class="card-title">Editar Propietario: {{ $propietario->nombre_completo }}</div>
            </div>

            <div class="card-body">
                {{-- BLOQUE PARA ERRORES DE VALIDACIÓN --}}
                @if ($errors->any())
                    <div class="alert warning">
                        <ul style="margin: 0; padding-left: 20px;">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('propietarios.update', $propietario->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="form-grid">
                        {{-- NOMBRE --}}
                        <div class="field">
                            <label for="nombre">Nombre(s)</label>
                            <input type="text" id="nombre" name="nombre"
                                value="{{ old('nombre', $propietario->nombre) }}" required pattern="[A-Za-zÀ-ÿ\s]{2,60}"
                                placeholder="Ej. Juan Manuel">
                            @error('nombre')
                                <span style="color: var(--red); font-size: 11px;">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- APELLIDOS --}}
                        <div class="field">
                            <label for="apellidos">Apellidos</label>
                            <input type="text" id="apellidos" name="apellidos"
                                value="{{ old('apellidos', $propietario->apellidos) }}" required
                                pattern="[A-Za-zÀ-ÿ\s]{2,60}" placeholder="Ej. Perez Garcia">
                            @error('apellidos')
                                <span style="color: var(--red); font-size: 11px;">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- TIPO DE PERSONA --}}
                        <div class="field" style="grid-column: span 2;">
                            <label for="tipo_persona">Tipo de Persona / Condición</label>
                            <select id="tipo_persona" name="tipo_persona" onchange="handleTipoPersonaChange()" style="font-weight: 700;" required>
                                <option value="personal_normal" {{ old('tipo_persona', $propietario->tipo_persona ?? 'personal_normal') === 'personal_normal' ? 'selected' : '' }}>
                                    Persona / Tercero Normal (Obligado a Pago de Ingreso S/. 600.00)
                                </option>
                                <option value="socio" {{ old('tipo_persona', $propietario->tipo_persona) === 'socio' ? 'selected' : '' }}>
                                    ⭐ Socio de la Empresa (Exonerado de Pago de Ingreso S/. 0.00)
                                </option>
                            </select>
                            <div style="font-size: 11.5px; color: var(--text3); margin-top: 3px;">
                                Los socios de la empresa no pagan cuotas de ingreso por registrar uno o más vehículos.
                            </div>
                            @error('tipo_persona')
                                <span style="color: var(--red); font-size: 11px;">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- DNI / RUC --}}
                        <div class="field">
                            <label for="dni">DNI / RUC</label>
                            <input type="text" id="dni" name="dni" value="{{ old('dni', $propietario->dni) }}"
                                maxlength="11" oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                                placeholder="8 o 11 dígitos">
                            @error('dni')
                                <span style="color: var(--red); font-size: 11px;">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- TELÉFONO --}}
                        <div class="field">
                            <label for="telefono">Teléfono / Celular</label>
                            <input type="text" id="telefono" name="telefono"
                                value="{{ old('telefono', $propietario->telefono) }}" maxlength="9"
                                oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 9)"
                                placeholder="9 dígitos">
                            @error('telefono')
                                <span style="color: var(--red); font-size: 11px;">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- ESTADO --}}
                        <div class="field">
                            <label for="activo">Estado del Registro</label>
                            <select name="activo" id="activo">
                                <option value="1" {{ old('activo', $propietario->activo) == 1 ? 'selected' : '' }}>
                                    Activo
                                </option>
                                <option value="0" {{ old('activo', $propietario->activo) == 0 ? 'selected' : '' }}>
                                    Inactivo
                                </option>
                            </select>
                            @error('activo')
                                <span style="color: var(--red); font-size: 11px;">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- CORREO (OPCIONAL) --}}
                        <div class="field">
                            <label for="email">Correo Electrónico</label>
                            <input type="email" id="email" name="email"
                                value="{{ old('email', $propietario->email) }}" placeholder="ejemplo@correo.com">
                        </div>

                        {{-- DIRECCIÓN (FULL WIDTH) --}}
                        <div class="field field-full">
                            <label for="direccion">Dirección Residencial</label>
                            <input type="text" id="direccion" name="direccion"
                                value="{{ old('direccion', $propietario->direccion) }}"
                                placeholder="Av. Principal 123, Huancayo">
                            @error('direccion')
                                <span style="color: var(--red); font-size: 11px;">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- NOTAS (FULL WIDTH) --}}
                        <div class="field field-full">
                            <label for="notas">Notas / Observaciones</label>
                            <textarea id="notas" name="notas" rows="3" style="resize: none;"
                                placeholder="Información adicional relevante...">{{ old('notas', $propietario->notas) }}</textarea>
                        </div>
                    </div>

                    {{-- SECCIÓN 4: Control de Monto de Ingreso --}}
                    @if($propietario->vehiculos->count() === 0)
                        <div class="form-section" id="seccion_monto_ingreso" style="margin-top: 30px; border-top: 1px dashed var(--border); padding-top: 20px;">
                            <h4 id="titulo_monto_ingreso" style="font-weight: 800; font-size: 15px; margin-bottom: 15px; display: flex; align-items: center; gap: 8px;">
                                <i class="fa-solid fa-hand-holding-dollar" style="color: var(--accent);"></i> Control de Monto de Ingreso (Total Obligado: S/. 600.00)
                            </h4>

                            <div id="socio_exonerado_banner" style="display: none; background: #eff6ff; border: 1.5px solid #bfdbfe; color: #1e40af; padding: 14px 18px; border-radius: 12px; margin-bottom: 15px; font-size: 13px; line-height: 1.5;">
                                <i class="fa-solid fa-circle-check" style="font-size: 16px; margin-right: 6px; color: #3b82f6;"></i>
                                <b>Socio de la Empresa Exonerado:</b> Por ser socio registrado de la empresa, <u>no se le cobra monto de ingreso</u> al registrar su vehículo ni unidades adicionales (Total Obligado: S/. 0.00).
                            </div>

                            <div class="g-4" style="grid-template-columns: repeat(auto-fit, minmax(170px, 1fr)); gap: 15px; display: grid;">
                                {{-- Monto Inicial --}}
                                <div class="field" style="background: var(--bg); padding: 12px; border-radius: 10px; border: 1px solid var(--border);">
                                    <label for="monto_inicial" style="font-weight: 700;">Monto Inicial (S/.)</label>
                                    <input type="number" id="monto_inicial" name="monto_inicial" step="0.01" min="0" max="600" value="{{ old('monto_inicial', $propietario->monto_inicial) }}" placeholder="0.00" oninput="calcularTotalIngreso()">
                                    @error('monto_inicial')
                                        <span style="color: var(--red); font-size: 11px;">{{ $message }}</span>
                                    @enderror

                                    <label for="fecha_monto_inicial" style="font-size: 11px; color: var(--text3); margin-top: 8px;">Fecha de Pago</label>
                                    <input type="date" id="fecha_monto_inicial" name="fecha_monto_inicial" value="{{ old('fecha_monto_inicial', $propietario->fecha_monto_inicial?->format('Y-m-d')) }}">
                                    @error('fecha_monto_inicial')
                                        <span style="color: var(--red); font-size: 11px;">{{ $message }}</span>
                                    @enderror
                                </div>

                                {{-- Cuota 1 --}}
                                <div class="field" style="background: var(--bg); padding: 12px; border-radius: 10px; border: 1px solid var(--border);">
                                    <label for="cuota_1" style="font-weight: 700;">Cuota 1 (S/.)</label>
                                    <input type="number" id="cuota_1" name="cuota_1" step="0.01" min="0" max="600" value="{{ old('cuota_1', $propietario->cuota_1) }}" placeholder="0.00" oninput="calcularTotalIngreso()">
                                    @error('cuota_1')
                                        <span style="color: var(--red); font-size: 11px;">{{ $message }}</span>
                                    @enderror

                                    <label for="fecha_cuota_1" style="font-size: 11px; color: var(--text3); margin-top: 8px;">Fecha de Pago</label>
                                    <input type="date" id="fecha_cuota_1" name="fecha_cuota_1" value="{{ old('fecha_cuota_1', $propietario->fecha_cuota_1?->format('Y-m-d')) }}">
                                    @error('fecha_cuota_1')
                                        <span style="color: var(--red); font-size: 11px;">{{ $message }}</span>
                                    @enderror
                                </div>

                                {{-- Cuota 2 --}}
                                <div class="field" style="background: var(--bg); padding: 12px; border-radius: 10px; border: 1px solid var(--border);">
                                    <label for="cuota_2" style="font-weight: 700;">Cuota 2 (S/.)</label>
                                    <input type="number" id="cuota_2" name="cuota_2" step="0.01" min="0" max="600" value="{{ old('cuota_2', $propietario->cuota_2) }}" placeholder="0.00" oninput="calcularTotalIngreso()">
                                    @error('cuota_2')
                                        <span style="color: var(--red); font-size: 11px;">{{ $message }}</span>
                                    @enderror

                                    <label for="fecha_cuota_2" style="font-size: 11px; color: var(--text3); margin-top: 8px;">Fecha de Pago</label>
                                    <input type="date" id="fecha_cuota_2" name="fecha_cuota_2" value="{{ old('fecha_cuota_2', $propietario->fecha_cuota_2?->format('Y-m-d')) }}">
                                    @error('fecha_cuota_2')
                                        <span style="color: var(--red); font-size: 11px;">{{ $message }}</span>
                                    @enderror
                                </div>

                                {{-- Cuota 3 --}}
                                <div class="field" style="background: var(--bg); padding: 12px; border-radius: 10px; border: 1px solid var(--border);">
                                    <label for="cuota_3" style="font-weight: 700;">Cuota 3 (S/.)</label>
                                    <input type="number" id="cuota_3" name="cuota_3" step="0.01" min="0" max="600" value="{{ old('cuota_3', $propietario->cuota_3) }}" placeholder="0.00" oninput="calcularTotalIngreso()">
                                    @error('cuota_3')
                                        <span style="color: var(--red); font-size: 11px;">{{ $message }}</span>
                                    @enderror

                                    <label for="fecha_cuota_3" style="font-size: 11px; color: var(--text3); margin-top: 8px;">Fecha de Pago</label>
                                    <input type="date" id="fecha_cuota_3" name="fecha_cuota_3" value="{{ old('fecha_cuota_3', $propietario->fecha_cuota_3?->format('Y-m-d')) }}">
                                    @error('fecha_cuota_3')
                                        <span style="color: var(--red); font-size: 11px;">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            
                            <div style="margin-top: 15px; background: var(--bg); border: 1px solid var(--border); padding: 12px 18px; border-radius: 10px; display: flex; justify-content: space-between; align-items: center;">
                                <span style="font-weight: 800; font-size: 13px;">
                                    Total Recaudado: <span id="suma_total" style="color: var(--accent);">S/. 0.00</span> / <span id="total_obligado_txt">S/. 600.00</span>
                                </span>
                                <span id="estado_badge" class="pill" style="font-weight: 800; font-size: 12px; padding: 4px 10px; border-radius: 99px;">DEUDA</span>
                            </div>
                        </div>
                    @else
                        <div style="margin-top: 30px; border-top: 1px dashed var(--border); padding-top: 20px;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; flex-wrap: wrap; gap: 10px;">
                                <h4 style="font-weight: 800; font-size: 16px; margin: 0; display: flex; align-items: center; gap: 8px;">
                                    <i class="fa-solid fa-hand-holding-dollar" style="color: var(--accent);"></i>
                                    Control de Monto de Ingreso ({{ $propietario->vehiculos->count() }} Vehículos Asignados)
                                </h4>
                                <span style="font-size: 12px; color: var(--text3); font-weight: 600;">(S/. 600.00 por cada vehículo asignado)</span>
                            </div>

                            @foreach($propietario->vehiculos as $index => $v)
                                <div class="form-section vehiculo-ingreso-section" style="margin-bottom: 24px; background: var(--surface); border: 1px solid var(--border); border-radius: 12px; padding: 18px;">
                                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; flex-wrap: wrap; gap: 10px;">
                                        <h5 class="vehiculo-titulo-ingreso" style="font-weight: 800; font-size: 15px; margin: 0; display: flex; align-items: center; gap: 8px;">
                                            <span class="pill blue" style="font-size: 11px; font-weight: 900;">Flota #{{ $v->numero_flota }}</span>
                                            <span style="color: var(--text); font-weight: 800;">{{ $v->placa }}</span>
                                            <span style="font-size: 12px; color: var(--text3); font-weight: 500;">({{ $v->marca }} {{ $v->modelo }})</span>
                                            <span class="v_obligado_header" style="font-size: 12.5px; color: var(--text3); font-weight: 600;">(Obligación: S/. 600.00)</span>
                                        </h5>
                                    </div>

                                    <div class="v_socio_banner" style="display: none; background: #eff6ff; border: 1.5px solid #bfdbfe; color: #1e40af; padding: 12px 16px; border-radius: 10px; margin-bottom: 15px; font-size: 12.5px;">
                                        <i class="fa-solid fa-circle-check" style="color: #3b82f6; margin-right: 4px;"></i> <b>Unidad de Socio Exonerada:</b> Exento de cobro de ingreso (S/. 0.00).
                                    </div>

                                    <div class="g-4" style="grid-template-columns: repeat(auto-fit, minmax(170px, 1fr)); gap: 15px; display: grid;">
                                        <div class="field" style="background: var(--bg); padding: 12px; border-radius: 10px; border: 1px solid var(--border);">
                                            <label style="font-weight: 700;">Monto Inicial (S/.)</label>
                                            <input type="number" class="monto_inicial_v" name="vehiculos[{{ $v->id }}][monto_inicial]" step="0.01" min="0" max="600" value="{{ old('vehiculos.'.$v->id.'.monto_inicial', $v->monto_inicial) }}" placeholder="0.00" oninput="calcularTotalIngresoV(this)">
                                            
                                            <label style="font-size: 11px; color: var(--text3); margin-top: 8px;">Fecha de Pago</label>
                                            <input type="date" name="vehiculos[{{ $v->id }}][fecha_monto_inicial]" value="{{ old('vehiculos.'.$v->id.'.fecha_monto_inicial', $v->fecha_monto_inicial?->format('Y-m-d')) }}">
                                        </div>

                                        <div class="field" style="background: var(--bg); padding: 12px; border-radius: 10px; border: 1px solid var(--border);">
                                            <label style="font-weight: 700;">Cuota 1 (S/.)</label>
                                            <input type="number" class="cuota_1_v" name="vehiculos[{{ $v->id }}][cuota_1]" step="0.01" min="0" max="600" value="{{ old('vehiculos.'.$v->id.'.cuota_1', $v->cuota_1) }}" placeholder="0.00" oninput="calcularTotalIngresoV(this)">
                                            
                                            <label style="font-size: 11px; color: var(--text3); margin-top: 8px;">Fecha de Pago</label>
                                            <input type="date" name="vehiculos[{{ $v->id }}][fecha_cuota_1]" value="{{ old('vehiculos.'.$v->id.'.fecha_cuota_1', $v->fecha_cuota_1?->format('Y-m-d')) }}">
                                        </div>

                                        <div class="field" style="background: var(--bg); padding: 12px; border-radius: 10px; border: 1px solid var(--border);">
                                            <label style="font-weight: 700;">Cuota 2 (S/.)</label>
                                            <input type="number" class="cuota_2_v" name="vehiculos[{{ $v->id }}][cuota_2]" step="0.01" min="0" max="600" value="{{ old('vehiculos.'.$v->id.'.cuota_2', $v->cuota_2) }}" placeholder="0.00" oninput="calcularTotalIngresoV(this)">
                                            
                                            <label style="font-size: 11px; color: var(--text3); margin-top: 8px;">Fecha de Pago</label>
                                            <input type="date" name="vehiculos[{{ $v->id }}][fecha_cuota_2]" value="{{ old('vehiculos.'.$v->id.'.fecha_cuota_2', $v->fecha_cuota_2?->format('Y-m-d')) }}">
                                        </div>

                                        <div class="field" style="background: var(--bg); padding: 12px; border-radius: 10px; border: 1px solid var(--border);">
                                            <label style="font-weight: 700;">Cuota 3 (S/.)</label>
                                            <input type="number" class="cuota_3_v" name="vehiculos[{{ $v->id }}][cuota_3]" step="0.01" min="0" max="600" value="{{ old('vehiculos.'.$v->id.'.cuota_3', $v->cuota_3) }}" placeholder="0.00" oninput="calcularTotalIngresoV(this)">
                                            
                                            <label style="font-size: 11px; color: var(--text3); margin-top: 8px;">Fecha de Pago</label>
                                            <input type="date" name="vehiculos[{{ $v->id }}][fecha_cuota_3]" value="{{ old('vehiculos.'.$v->id.'.fecha_cuota_3', $v->fecha_cuota_3?->format('Y-m-d')) }}">
                                        </div>
                                    </div>
                                    
                                    <div style="margin-top: 15px; background: var(--bg); border: 1px solid var(--border); padding: 12px 18px; border-radius: 10px; display: flex; justify-content: space-between; align-items: center;">
                                        <span style="font-weight: 800; font-size: 13px;">
                                            Total Recaudado en Unidad: <span class="suma_total_v" style="color: var(--accent);">S/. 0.00</span> / <span class="v_obligado_txt">S/. 600.00</span>
                                        </span>
                                        <span class="estado_badge_v pill" style="font-weight: 800; font-size: 12px; padding: 4px 10px; border-radius: 99px;">DEUDA</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    @push('scripts')
                    <script>
                        function handleTipoPersonaChange() {
                            const tipo = document.getElementById('tipo_persona').value;
                            const isSocio = tipo === 'socio';

                            // Propietario sin vehículos
                            const banner = document.getElementById('socio_exonerado_banner');
                            const titulo = document.getElementById('titulo_monto_ingreso');
                            const obligadoTxt = document.getElementById('total_obligado_txt');
                            const badge = document.getElementById('estado_badge');

                            if (isSocio) {
                                if (banner) banner.style.display = 'block';
                                if (titulo) titulo.innerHTML = '<i class="fa-solid fa-star" style="color: #3b82f6;"></i> Control de Monto de Ingreso (SOCIO DE LA EMPRESA - EXONERADO S/. 0.00)';
                                if (obligadoTxt) obligadoTxt.textContent = 'S/. 0.00 (Exonerado)';
                                if (badge) {
                                    badge.textContent = 'EXONERADO (SOCIO)';
                                    badge.style.background = '#dbeafe';
                                    badge.style.color = '#1d4ed8';
                                }
                            } else {
                                if (banner) banner.style.display = 'none';
                                if (titulo) titulo.innerHTML = '<i class="fa-solid fa-hand-holding-dollar"></i> Control de Monto de Ingreso (Total Obligado: S/. 600.00)';
                                if (obligadoTxt) obligadoTxt.textContent = 'S/. 600.00';
                                calcularTotalIngreso();
                            }

                            // Propietario con vehículos
                            document.querySelectorAll('.vehiculo-ingreso-section').forEach(function(section) {
                                const vBanner = section.querySelector('.v_socio_banner');
                                const vHeader = section.querySelector('.v_obligado_header');
                                const vObligado = section.querySelector('.v_obligado_txt');
                                const vBadge = section.querySelector('.estado_badge_v');

                                if (isSocio) {
                                    if (vBanner) vBanner.style.display = 'block';
                                    if (vHeader) vHeader.textContent = '(SOCIO EXONERADO - S/. 0.00)';
                                    if (vObligado) vObligado.textContent = 'S/. 0.00 (Exonerado)';
                                    if (vBadge) {
                                        vBadge.textContent = 'EXONERADO (SOCIO)';
                                        vBadge.style.background = '#dbeafe';
                                        vBadge.style.color = '#1d4ed8';
                                    }
                                } else {
                                    if (vBanner) vBanner.style.display = 'none';
                                    if (vHeader) vHeader.textContent = '(Obligación: S/. 600.00)';
                                    if (vObligado) vObligado.textContent = 'S/. 600.00';
                                    const input = section.querySelector('.monto_inicial_v');
                                    if (input) calcularTotalIngresoV(input);
                                }
                            });
                        }

                        function calcularTotalIngreso() {
                            const tipo = document.getElementById('tipo_persona').value;
                            if (tipo === 'socio') {
                                return handleTipoPersonaChange();
                            }

                            const miEl = document.getElementById('monto_inicial');
                            if (miEl) {
                                const mi = parseFloat(miEl.value) || 0;
                                const c1 = parseFloat(document.getElementById('cuota_1').value) || 0;
                                const c2 = parseFloat(document.getElementById('cuota_2').value) || 0;
                                const c3 = parseFloat(document.getElementById('cuota_3').value) || 0;
                                
                                const total = mi + c1 + c2 + c3;
                                document.getElementById('suma_total').textContent = 'S/. ' + total.toFixed(2);
                                
                                const badge = document.getElementById('estado_badge');
                                if (total >= 600) {
                                    badge.textContent = 'PAGADO';
                                    badge.style.background = 'var(--green-l)';
                                    badge.style.color = 'var(--green)';
                                } else {
                                    const deuda = 600 - total;
                                    badge.textContent = 'DEUDA: S/. ' + deuda.toFixed(2);
                                    badge.style.background = 'var(--red-l)';
                                    badge.style.color = 'var(--red)';
                                }
                            }
                        }

                        function calcularTotalIngresoV(input) {
                            const tipo = document.getElementById('tipo_persona').value;
                            if (tipo === 'socio') {
                                return handleTipoPersonaChange();
                            }

                            const section = input.closest('.vehiculo-ingreso-section');
                            if (section) {
                                const mi = parseFloat(section.querySelector('.monto_inicial_v').value) || 0;
                                const c1 = parseFloat(section.querySelector('.cuota_1_v').value) || 0;
                                const c2 = parseFloat(section.querySelector('.cuota_2_v').value) || 0;
                                const c3 = parseFloat(section.querySelector('.cuota_3_v').value) || 0;
                                
                                const total = mi + c1 + c2 + c3;
                                section.querySelector('.suma_total_v').textContent = 'S/. ' + total.toFixed(2);
                                
                                const badge = section.querySelector('.estado_badge_v');
                                if (total >= 600) {
                                    badge.textContent = 'PAGADO';
                                    badge.style.background = 'var(--green-l)';
                                    badge.style.color = 'var(--green)';
                                } else {
                                    const deuda = 600 - total;
                                    badge.textContent = 'DEUDA: S/. ' + deuda.toFixed(2);
                                    badge.style.background = 'var(--red-l)';
                                    badge.style.color = 'var(--red)';
                                }
                            }
                        }

                        document.addEventListener('DOMContentLoaded', function() {
                            handleTipoPersonaChange();
                            calcularTotalIngreso();
                            document.querySelectorAll('.vehiculo-ingreso-section').forEach(function(section) {
                                const input = section.querySelector('.monto_inicial_v');
                                if (input) {
                                    calcularTotalIngresoV(input);
                                }
                            });
                        });
                    </script>
                    @endpush

                    {{-- BOTONES DE ACCIÓN --}}
                    <div
                        style="margin-top: 30px; display: flex; justify-content: flex-end; gap: 12px; border-top: 1px solid var(--border); padding-top: 20px;">
                        <a href="{{ route('propietarios.index') }}" class="btn-secondary"
                            style="text-decoration: none; display: flex; align-items: center;">
                            Cancelar
                        </a>
                        <button type="submit" class="btn-primary">
                            <span class="ni"></span> Guardar Cambios
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
