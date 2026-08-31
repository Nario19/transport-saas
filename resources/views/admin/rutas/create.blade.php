@extends('layouts.admin')

@section('back_url', route('rutas.index'))

@section('content')
    <div class="panel">
        <div class="card" style="max-width: 900px; margin: 0 auto;">
            <div class="card-header">
                <div class="card-title">Registrar Nueva Ruta</div>
            </div>
            <div class="card-body">
                <form action="{{ route('rutas.store') }}" method="POST" id="formRuta">
                    @csrf

                    <div class="form-grid" style="grid-template-columns: 1fr 1fr 1fr;">
                        <div class="field" style="grid-column: span 2;">
                            <label for="nombre">Nombre de la Ruta (Ej: Huancayo - El Tambo)</label>
                            <input type="text" id="nombre" name="nombre" value="{{ old('nombre') }}" required>
                            @error('nombre')
                                <span style="color: var(--red); font-size: 11px;">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="field">
                            <label for="codigo">Código Interno</label>
                            <input type="text" id="codigo" name="codigo" value="{{ old('codigo') }}"
                                placeholder="H-01">
                            @error('codigo')
                                <span style="color: var(--red); font-size: 11px;">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="field">
                            <label for="origen">Origen / Destino (Punto de inicio)</label>
                            <input type="text" id="origen" name="origen" value="{{ old('origen') }}"
                                placeholder="Punto de inicio" required>
                            @error('origen')
                                <span style="color: var(--red); font-size: 11px;">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="field">
                            <label for="destino">Destino / Origen (Punto final)</label>
                            <input type="text" id="destino" name="destino" value="{{ old('destino') }}"
                                placeholder="Punto final" required>
                            @error('destino')
                                <span style="color: var(--red); font-size: 11px;">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="field">
                            <label for="duracion_min">Duración Est. (min)</label>
                            <input type="number" id="duracion_min" name="duracion_min" value="{{ old('duracion_min') }}">
                            @error('duracion_min')
                                <span style="color: var(--red); font-size: 11px;">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="field">
                            <label for="estado">Estado</label>
                            <select name="estado" id="estado" required>
                                <option value="activa" {{ old('estado') == 'activa' ? 'selected' : '' }}>Activa</option>
                                <option value="inactiva" {{ old('estado') == 'inactiva' ? 'selected' : '' }}>Inactiva
                                </option>
                            </select>
                            @error('estado')
                                <span style="color: var(--red); font-size: 11px;">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="field" style="grid-column: span 2;">
                            <label for="descripcion">Descripción</label>
                            <input type="text" id="descripcion" name="descripcion" value="{{ old('descripcion') }}">
                            @error('descripcion')
                                <span style="color: var(--red); font-size: 11px;">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <hr style="margin: 30px 0; border: 0; border-top: 1px solid var(--border);">

                    <div id="section-paraderos">
                        <div
                            style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; flex-wrap: wrap; gap: 10px;">
                            <div>
                                <h3 style="font-size: 15px; font-weight: 700; color: var(--text); margin-bottom: 2px;">Secuencia de Paraderos</h3>
                                <p style="font-size: 12px; color: var(--text3); margin: 0;">El origen y destino se completan automáticamente con los campos superiores. Añade sólo los puntos intermedios.</p>
                            </div>
                            <button type="button" onclick="agregarFilaIntermedia()" class="btn-secondary"
                                style="padding: 6px 16px; font-size: 12px; font-weight: 700; display: inline-flex; align-items: center; gap: 6px;">
                                <i class="fa-solid fa-plus"></i> Añadir Paradero Intermedio
                            </button>
                        </div>

                        <table class="tbl" id="tablaParaderos">
                            <thead>
                                <tr>
                                    <th width="45" style="text-align: center;">#</th>
                                    <th>Nombre del Paradero</th>
                                    <th width="220">Tipo</th>
                                    <th width="50"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @if(old('paraderos'))
                                    @foreach(old('paraderos') as $idx => $p)
                                        <tr data-auto-type="{{ ($p['tipo'] ?? '') === 'origen' ? 'origen' : (($p['tipo'] ?? '') === 'destino' ? 'destino' : '') }}">
                                            <td class="row-num" style="text-align: center; font-weight: 700; color: var(--text3);">{{ $loop->iteration }}</td>
                                            <td>
                                                <div class="field" style="gap:0">
                                                    <input type="text" name="paraderos[{{ $idx }}][nombre]" value="{{ $p['nombre'] ?? '' }}" required placeholder="Nombre del punto...">
                                                </div>
                                            </td>
                                            <td>
                                                <div class="field" style="gap:0">
                                                    <select name="paraderos[{{ $idx }}][tipo]" required onchange="handleTipoChange(this)">
                                                        <option value="origen" {{ ($p['tipo'] ?? '') == 'origen' ? 'selected' : '' }}>Origen / Destino</option>
                                                        <option value="intermedio" {{ ($p['tipo'] ?? '') == 'intermedio' ? 'selected' : '' }}>Intermedio</option>
                                                        <option value="destino" {{ ($p['tipo'] ?? '') == 'destino' ? 'selected' : '' }}>Destino / Origen</option>
                                                    </select>
                                                </div>
                                            </td>
                                            <td style="text-align: center;">
                                                <button type="button" onclick="eliminarFila(this)" class="action-icon delete-icon" style="border:none; background:none;" title="Eliminar paradero">
                                                    <i class="fa-solid fa-xmark"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                @endif
                            </tbody>
                        </table>
                    </div>

                    <div
                        style="margin-top: 30px; display: flex; justify-content: flex-end; gap: 12px; border-top: 1px solid var(--border); padding-top: 20px;">
                        <a href="{{ route('rutas.index') }}" class="btn-secondary"
                            style="text-decoration: none;">Cancelar</a>
                        <button type="submit" class="btn-primary">
                            <i class="fa-solid fa-route"></i> Guardar Ruta
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        let paraderoIndex = 0;

        function reindexarFilas() {
            const tbody = document.querySelector('#tablaParaderos tbody');
            const rows = tbody.querySelectorAll('tr');
            rows.forEach((tr, idx) => {
                const numCell = tr.querySelector('.row-num');
                if (numCell) numCell.textContent = idx + 1;

                const nombreInput = tr.querySelector('input[name*="[nombre]"]');
                const tipoSelect = tr.querySelector('select[name*="[tipo]"]');
                if (nombreInput) nombreInput.name = `paraderos[${idx}][nombre]`;
                if (tipoSelect) tipoSelect.name = `paraderos[${idx}][tipo]`;
            });
            paraderoIndex = rows.length;
        }

        function crearFilaParadero(nombre = '', tipo = 'intermedio', isAuto = false) {
            const tr = document.createElement('tr');
            if (isAuto) tr.dataset.autoType = tipo;
            tr.innerHTML = `
                <td class="row-num" style="text-align: center; font-weight: 700; color: var(--text3);">${paraderoIndex + 1}</td>
                <td>
                    <div class="field" style="gap:0">
                        <input type="text" name="paraderos[${paraderoIndex}][nombre]" value="${nombre.replace(/"/g, '&quot;')}" required placeholder="Nombre del punto...">
                    </div>
                </td>
                <td>
                    <div class="field" style="gap:0">
                        <select name="paraderos[${paraderoIndex}][tipo]" required onchange="handleTipoChange(this)">
                            <option value="origen" ${tipo === 'origen' ? 'selected' : ''}>Origen / Destino</option>
                            <option value="intermedio" ${tipo === 'intermedio' ? 'selected' : ''}>Intermedio</option>
                            <option value="destino" ${tipo === 'destino' ? 'selected' : ''}>Destino / Origen</option>
                        </select>
                    </div>
                </td>
                <td style="text-align: center;">
                    <button type="button" onclick="eliminarFila(this)" class="action-icon delete-icon" style="border:none; background:none;" title="Eliminar paradero">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </td>
            `;
            paraderoIndex++;
            return tr;
        }

        function handleTipoChange(selectEl) {
            const tr = selectEl.closest('tr');
            tr.dataset.autoType = '';
        }

        function eliminarFila(btn) {
            btn.closest('tr').remove();
            reindexarFilas();
        }

        function agregarFilaIntermedia(nombre = '') {
            const tbody = document.querySelector('#tablaParaderos tbody');
            const tr = crearFilaParadero(nombre, 'intermedio', false);
            
            // Buscar la última fila de tipo destino para insertar antes de ella
            const rows = Array.from(tbody.querySelectorAll('tr'));
            let destinoRow = null;
            for (let i = rows.length - 1; i >= 0; i--) {
                const select = rows[i].querySelector('select[name*="[tipo]"]');
                if (select && select.value === 'destino') {
                    destinoRow = rows[i];
                    break;
                }
            }

            if (destinoRow) {
                tbody.insertBefore(tr, destinoRow);
            } else {
                tbody.appendChild(tr);
            }
            
            reindexarFilas();
            const input = tr.querySelector('input[name*="[nombre]"]');
            if (input) input.focus();
        }

        function syncOrigenDestino() {
            const origenInput = document.getElementById('origen');
            const destinoInput = document.getElementById('destino');
            const origenVal = origenInput.value.trim();
            const destinoVal = destinoInput.value.trim();
            const tbody = document.querySelector('#tablaParaderos tbody');

            // --- 1. Sincronizar Origen ---
            let origenRow = tbody.querySelector('tr[data-auto-type="origen"]');
            if (!origenRow) {
                const rows = Array.from(tbody.querySelectorAll('tr'));
                origenRow = rows.find(r => {
                    const sel = r.querySelector('select[name*="[tipo]"]');
                    return sel && sel.value === 'origen';
                });
                if (origenRow) origenRow.dataset.autoType = 'origen';
            }

            if (origenRow) {
                const input = origenRow.querySelector('input[name*="[nombre]"]');
                if (input) input.value = origenVal;
            } else if (origenVal !== '') {
                const newRow = crearFilaParadero(origenVal, 'origen', true);
                if (tbody.firstChild) {
                    tbody.insertBefore(newRow, tbody.firstChild);
                } else {
                    tbody.appendChild(newRow);
                }
            }

            // --- 2. Sincronizar Destino ---
            let destinoRow = tbody.querySelector('tr[data-auto-type="destino"]');
            if (!destinoRow) {
                const rows = Array.from(tbody.querySelectorAll('tr'));
                for (let i = rows.length - 1; i >= 0; i--) {
                    const sel = rows[i].querySelector('select[name*="[tipo]"]');
                    if (sel && sel.value === 'destino') {
                        destinoRow = rows[i];
                        destinoRow.dataset.autoType = 'destino';
                        break;
                    }
                }
            }

            if (destinoRow) {
                const input = destinoRow.querySelector('input[name*="[nombre]"]');
                if (input) input.value = destinoVal;
            } else if (destinoVal !== '') {
                const newRow = crearFilaParadero(destinoVal, 'destino', true);
                tbody.appendChild(newRow);
            }

            reindexarFilas();
        }

        document.addEventListener('DOMContentLoaded', () => {
            const origenInput = document.getElementById('origen');
            const destinoInput = document.getElementById('destino');
            const tbody = document.querySelector('#tablaParaderos tbody');

            origenInput.addEventListener('input', syncOrigenDestino);
            destinoInput.addEventListener('input', syncOrigenDestino);

            // Si no hay filas cargadas (por ejemplo, primera visita), inicializar origen y destino
            if (tbody.querySelectorAll('tr').length === 0) {
                const origenRow = crearFilaParadero(origenInput.value.trim(), 'origen', true);
                const destinoRow = crearFilaParadero(destinoInput.value.trim(), 'destino', true);
                tbody.appendChild(origenRow);
                tbody.appendChild(destinoRow);
                reindexarFilas();
            } else {
                reindexarFilas();
            }

            document.getElementById('formRuta').addEventListener('submit', () => {
                reindexarFilas();
            });
        });
    </script>
@endsection
