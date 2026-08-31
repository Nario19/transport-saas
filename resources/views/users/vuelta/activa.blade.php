@extends('layouts.conductor')

@section('title', 'Vuelta en Curso')

@section('extra_css')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
<style>
    .en-ruta-hero {
        background: linear-gradient(135deg, #15803d 0%, #166534 100%);
        border-radius: 16px;
        padding: 16px;
        color: #fff;
        margin-bottom: 16px;
        box-shadow: 0 4px 15px rgba(22, 101, 52, 0.25);
    }
    .map-box {
        position: relative;
        border-radius: 12px;
        overflow: hidden;
        border: 2px solid rgba(255, 255, 255, 0.2);
        margin-top: 12px;
        box-shadow: 0 4px 10px rgba(0,0,0,0.15);
    }
    #map-conductor {
        width: 100%;
        height: 230px;
        background: #e2e8f0;
    }
    .driver-marker-pulse {
        background: #2563eb;
        width: 24px;
        height: 24px;
        border-radius: 50%;
        border: 2.5px solid #ffffff;
        box-shadow: 0 0 10px rgba(37, 99, 235, 0.7);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 11px;
        animation: markerPulse 1.8s infinite;
    }
    @keyframes markerPulse {
        0% { box-shadow: 0 0 0 0 rgba(37, 99, 235, 0.7); }
        70% { box-shadow: 0 0 0 12px rgba(37, 99, 235, 0); }
        100% { box-shadow: 0 0 0 0 rgba(37, 99, 235, 0); }
    }

    .cronometro {
        font-family: 'JetBrains Mono', monospace;
        font-size: 44px;
        font-weight: 800;
        color: var(--accent);
        text-align: center;
        letter-spacing: .05em;
        padding: 14px 0;
    }
    .pulse-dot {
        display: inline-block;
        width: 9px; height: 9px;
        background: var(--green);
        border-radius: 50%;
        margin-right: 6px;
        animation: pulse 1.2s ease-in-out infinite;
    }
    @keyframes pulse {
        0%,100% { transform: scale(1); opacity: 1; }
        50%      { transform: scale(1.4); opacity: .6; }
    }
    .btn-terminar {
        background: var(--red);
        color: #fff;
        border: none;
        border-radius: 12px;
        padding: 16px 20px;
        font-size: 16px;
        font-weight: 700;
        width: 100%;
        cursor: pointer;
        font-family: inherit;
        transition: opacity .15s;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }
    .btn-terminar:hover { opacity: .88; }
    .btn-terminar:disabled { opacity: .5; cursor: not-allowed; }
</style>
@endsection

@section('content')

@php
    $rutaObj = $vuelta->ruta;
    $colorRuta = $rutaObj?->color ?? '#2563eb';
    $rawTrazado = $rutaObj?->trazado;
    if (is_string($rawTrazado)) {
        $rawTrazado = json_decode($rawTrazado, true);
    }
    $paraderosData = $rutaObj?->paraderos ? $rutaObj->paraderos->map(function($p) {
        return [
            'id' => $p->id,
            'nombre' => $p->nombre,
            'tipo' => $p->tipo,
            'orden' => $p->orden,
            'latitud_a' => $p->latitud_a,
            'longitud_a' => $p->longitud_a,
            'tolerancia' => $p->tolerancia ?? 30,
        ];
    })->values() : [];
    $flotaNum = $vuelta->vehiculo?->numero_flota ?? '';
@endphp

{{-- Card Verde con Encabezado, Información en Ruta y Mapa en Vivo --}}
<div class="en-ruta-hero">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2px;">
        <div style="display: flex; align-items: center; gap: 10px;">
            <div style="width: 38px; height: 38px; border-radius: 10px; background: rgba(255,255,255,0.2); display: flex; align-items: center; justify-content: center; font-size: 18px; color: white;">
                <i class="fa-solid fa-car-side"></i>
            </div>
            <div>
                <div style="font-size: 16px; font-weight: 800; line-height: 1.2;">¡En Ruta! Vuelta #{{ $vuelta->numero_vuelta }}</div>
                <div style="font-size: 12px; opacity: 0.85; margin-top: 2px;">
                    Hora de Inicio: <b>{{ $vuelta->hora_salida }}</b>
                </div>
            </div>
        </div>
        <div>
            <span style="background: rgba(255,255,255,0.2); padding: 4px 10px; border-radius: 99px; font-size: 11px; font-weight: 800; display: inline-flex; align-items: center; gap: 5px;">
                <span class="pulse-dot" style="background:#4ade80; width:7px; height:7px; margin:0;"></span> EN VIVO
            </span>
        </div>
    </div>

    {{-- Contenedor del Mapa en Vivo del Conductor --}}
    <div class="map-box">
        <div id="map-conductor"></div>
        <button type="button" onclick="recentrarEnConductor()" 
                style="position: absolute; bottom: 8px; right: 8px; z-index: 500; background: white; color: #0f172a; border: 1px solid var(--border); border-radius: 8px; width: 34px; height: 34px; box-shadow: 0 2px 6px rgba(0,0,0,0.25); display: flex; align-items: center; justify-content: center; font-size: 15px; cursor: pointer;"
                title="Recentrar en mi ubicación">
            <i class="fa-solid fa-crosshairs" style="color: var(--accent);"></i>
        </button>
    </div>
</div>

{{-- Cronómetro --}}
<div class="card" style="margin-bottom: 16px;">
    <div class="card-header" style="padding: 14px 16px; display: flex; justify-content: space-between; align-items: center;">
        <span class="card-title" style="display: flex; align-items: center; font-size: 13px; font-weight: 700; color: var(--text2);">
            <span class="pulse-dot"></span>Tiempo en ruta
        </span>
        <span style="font-size: 11px; font-weight: 700; color: var(--text3);">CRONÓMETRO</span>
    </div>
    <div class="card-body" style="padding: 0 16px 12px 16px;">
        <div class="cronometro" id="cronometro">00:00:00</div>
    </div>
</div>

{{-- Info de vuelta: Vehículo / Flota, Paradero de Inicio, Ruta, Salida y Fecha --}}
<div class="card" style="margin-bottom: 16px;">
    <div class="card-body" style="padding: 16px;">
        {{-- Vehículo / Flota --}}
        <div class="summary-row" style="display: flex; justify-content: space-between; align-items: center; padding: 10px 0; border-bottom: 1px solid #f1f5f9;">
            <span class="summary-label" style="font-weight:600; color: var(--text2); display: flex; align-items: center; gap: 8px;">
                <i class="fa-solid fa-bus" style="color: var(--accent); width: 16px;"></i> Vehículo / Flota
            </span>
            <span class="summary-val" style="font-weight: 800; font-size: 14px; color: var(--text); display: flex; align-items: center; gap: 5px;">
                {{ $vuelta->vehiculo?->placa ?? '—' }}
                @if($vuelta->vehiculo?->numero_flota)
                    <span style="background: var(--accent-l); color: var(--accent); padding: 2px 8px; border-radius: 6px; font-size: 12px; font-weight: 800;">
                        #{{ $vuelta->vehiculo->numero_flota }}
                    </span>
                @endif
            </span>
        </div>

        {{-- Paradero de Inicio --}}
        <div class="summary-row" style="display: flex; justify-content: space-between; align-items: center; padding: 10px 0; border-bottom: 1px solid #f1f5f9;">
            <span class="summary-label" style="font-weight:600; color: var(--text2); display: flex; align-items: center; gap: 8px;">
                <i class="fa-solid fa-map-pin" style="color: var(--green); width: 16px;"></i> Paradero de Inicio
            </span>
            <span class="summary-val" style="font-weight: 700; color: var(--text); text-align: right;">
                {{ $vuelta->paraderoSalida?->nombre ?? ($vuelta->ruta?->origen ?? 'Inicio de Ruta') }}
            </span>
        </div>

        {{-- Ruta --}}
        <div class="summary-row" style="display: flex; justify-content: space-between; align-items: center; padding: 10px 0; border-bottom: 1px solid #f1f5f9;">
            <span class="summary-label" style="font-weight:600; color: var(--text2); display: flex; align-items: center; gap: 8px;">
                <i class="fa-solid fa-route" style="color: var(--gold); width: 16px;"></i> Ruta
            </span>
            <span class="summary-val" style="font-weight: 700; color: var(--text); text-align: right;">
                {{ $vuelta->ruta?->nombre ?? 'Sin ruta asignada' }}
            </span>
        </div>

        {{-- Salida --}}
        <div class="summary-row" style="display: flex; justify-content: space-between; align-items: center; padding: 10px 0; border-bottom: 1px solid #f1f5f9;">
            <span class="summary-label" style="font-weight:600; color: var(--text2); display: flex; align-items: center; gap: 8px;">
                <i class="fa-regular fa-clock" style="color: var(--text3); width: 16px;"></i> Salida
            </span>
            <span class="summary-val" style="font-weight: 700; color: var(--text);">
                {{ $vuelta->hora_salida }}
            </span>
        </div>

        {{-- Fecha --}}
        <div class="summary-row" style="display: flex; justify-content: space-between; align-items: center; padding: 10px 0;">
            <span class="summary-label" style="font-weight:600; color: var(--text2); display: flex; align-items: center; gap: 8px;">
                <i class="fa-regular fa-calendar" style="color: var(--text3); width: 16px;"></i> Fecha
            </span>
            <span class="summary-val" style="font-weight: 700; color: var(--text);">
                {{ $vuelta->fecha->format('d/m/Y') }}
            </span>
        </div>
    </div>
</div>

{{-- Selector de Paradero de Llegada --}}
<div class="card" style="margin-bottom: 16px;">
    <div class="card-header" style="padding: 14px 16px; border-bottom: 1px solid #f8fafc;">
        <span class="card-title" style="font-size:14px; color:#64748b; font-weight: 700;">
            <i class="fa-solid fa-location-dot" style="color: var(--red); margin-right: 5px;"></i> ¿Dónde terminarás la vuelta?
        </span>
    </div>
    <div class="card-body" style="padding: 16px;">
        <div class="field" style="margin: 0;">
            <select id="paradero_llegada_id" name="paradero_llegada_id" onchange="verificarGPSParaderoSeleccionado()" style="width: 100%; height: 48px; border-radius: 10px; border: 1px solid var(--border); padding: 0 12px; font-weight: 700; font-size: 14px; color: var(--text); background: white;">
                <option value="" disabled selected>-- Selecciona el paradero de llegada --</option>
                @foreach($paraderosLlegada as $p)
                    <option value="{{ $p->id }}" 
                            data-lat-a="{{ $p->latitud_a }}" 
                            data-lng-a="{{ $p->longitud_a }}" 
                            data-lat-b="{{ $p->latitud_b }}" 
                            data-lng-b="{{ $p->longitud_b }}" 
                            data-tolerancia="{{ $p->tolerancia ?? 30 }}">
                        {{ $p->nombre }} ({{ strtoupper($p->tipo) }})
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Panel Informativo de Coordenadas y Distancia --}}
        <div id="paradero-coords-info" style="margin-top: 12px; display: none; padding: 12px; border-radius: 8px; background: var(--bg); border: 1px solid var(--border); font-size: 13px;">
            <div style="font-weight: 700; color: var(--text2); margin-bottom: 6px;"><i class="fa-solid fa-circle-info" style="color: var(--accent);"></i> Estado del Paradero</div>
            <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                <span id="info-badge" class="pill" style="font-size: 11px; font-weight: 800; padding: 3px 8px; border-radius: 99px; color: white;">—</span>
                <span id="info-dist-text" style="font-weight: 700; color: var(--text);">—</span>
            </div>
        </div>
    </div>
</div>

{{-- Botón terminar --}}
<button class="btn-terminar" id="btn-terminar" onclick="confirmarTerminar()">
    <i class="fa-solid fa-flag-checkered"></i> Terminar Vuelta
</button>

<div id="terminando-msg" class="hidden"
     style="text-align:center;margin-top:12px;color:var(--red);font-weight:600;font-size:13px">
    <i class="fa-solid fa-spinner fa-spin"></i> Registrando llegada...
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script>
const TERMINAR_URL = '{{ route("conductor.vuelta.terminar", [], false) }}';
const UBICACION_URL = '{{ route("conductor.vuelta.ubicacion", [], false) }}';
const CSRF         = '{{ csrf_token() }}';
const INICIO_MS    = {{ \Carbon\Carbon::parse($vuelta->fecha->format("Y-m-d") . ' ' . $vuelta->hora_salida)->timestamp * 1000 }};
const SERVER_AHORA = {{ now()->timestamp * 1000 }};

const RUTA_COLOR   = '{{ $colorRuta }}';
const RUTA_TRAZADO = @json($rawTrazado);
const PARADEROS    = @json($paraderosData);
const FLOTA_NUM    = '{{ $flotaNum }}';

// Cronómetro
const inicio       = new Date(INICIO_MS);
const clockOffset  = SERVER_AHORA - Date.now();

function actualizarCronometro() {
    const ahoraAjustado = Date.now() + clockOffset;
    let diff = Math.max(0, Math.floor((ahoraAjustado - INICIO_MS) / 1000));
    
    if (diff === 0 && (ahoraAjustado > INICIO_MS)) diff = 1;

    const hh = String(Math.floor(diff / 3600)).padStart(2, '0');
    let residuo = diff % 3600;
    const mm = String(Math.floor(residuo / 60)).padStart(2, '0');
    const ss = String(residuo % 60).padStart(2, '0');
    
    document.getElementById('cronometro').textContent = `${hh}:${mm}:${ss}`;
}
let cronometroIntervalId = setInterval(actualizarCronometro, 1000);
actualizarCronometro();

// --- INICIALIZACIÓN OPTIMIZADA DEL MAPA EN VIVO PARA EL CONDUCTOR ---
let mapConductor = null;
let driverMarker = null;
let userHasPanned = false;

function construirTrazadoParaderosJS(paraderosList) {
    const validos = (paraderosList || []).filter(p => p.latitud_a && p.longitud_a);
    if (validos.length === 0) return [];
    if (validos.length === 1) return [[parseFloat(validos[0].latitud_a), parseFloat(validos[0].longitud_a)]];

    const origenes = validos.filter(p => p.tipo === 'origen');
    const intermedios = validos.filter(p => p.tipo === 'intermedio');
    const destinos = validos.filter(p => p.tipo === 'destino');

    const getCoord = (p) => [parseFloat(p.latitud_a), parseFloat(p.longitud_a)];

    if (origenes.length <= 1 && destinos.length <= 1) {
        return validos.map(getCoord);
    }

    const branches = [];
    if (destinos.length > 1 && origenes.length <= 1) {
        const trunk = origenes.length === 1 ? [origenes[0], ...intermedios] : [...intermedios];
        const forkPt = trunk.length > 0 ? trunk[trunk.length - 1] : null;
        const forkCoord = forkPt ? getCoord(forkPt) : null;
        const trunkCoords = trunk.map(getCoord);

        branches.push([...trunkCoords, getCoord(destinos[0])]);
        for (let i = 1; i < destinos.length; i++) {
            if (forkCoord) branches.push([forkCoord, getCoord(destinos[i])]);
            else branches.push([getCoord(destinos[i])]);
        }
        return branches;
    }

    return validos.map(getCoord);
}

function inicializarMapaConductor() {
    const mapEl = document.getElementById('map-conductor');
    if (!mapEl) return;

    // Configuración ligera: sin controles pesados, optimizado para móvil
    mapConductor = L.map('map-conductor', {
        zoomControl: false,
        attributionControl: false,
        fadeAnimation: false,
        markerZoomAnimation: false
    }).setView([-12.065, -75.204], 14);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 18,
        minZoom: 11
    }).addTo(mapConductor);

    // Detectar si el usuario mueve el mapa manualmente para no forzar el recentrado de inmediato
    mapConductor.on('dragstart', () => {
        userHasPanned = true;
    });

    let lineCoords = [];
    let allFlat = [];

    if (RUTA_TRAZADO && RUTA_TRAZADO.length > 0) {
        if (Array.isArray(RUTA_TRAZADO[0]) && Array.isArray(RUTA_TRAZADO[0][0])) {
            lineCoords = RUTA_TRAZADO.map(b => b.map(c => [parseFloat(c[0]), parseFloat(c[1])]));
            RUTA_TRAZADO.forEach(b => b.forEach(c => allFlat.push([parseFloat(c[0]), parseFloat(c[1])])));
        } else {
            lineCoords = RUTA_TRAZADO.map(c => [parseFloat(c[0]), parseFloat(c[1])]);
            allFlat = lineCoords;
        }
    } else {
        const auto = construirTrazadoParaderosJS(PARADEROS);
        if (Array.isArray(auto[0]) && Array.isArray(auto[0][0])) {
            lineCoords = auto;
            auto.forEach(b => b.forEach(c => allFlat.push(c)));
        } else {
            lineCoords = auto;
            allFlat = auto;
        }
    }

    // Dibujar el trazado de la ruta
    if (allFlat.length >= 2) {
        L.polyline(lineCoords, {
            color: RUTA_COLOR,
            weight: 5,
            opacity: 0.85
        }).addTo(mapConductor);

        mapConductor.fitBounds(L.latLngBounds(allFlat), { padding: [25, 25] });
    }

    // Dibujar paraderos con marcadores ultra ligeros
    (PARADEROS || []).forEach(p => {
        if (p.latitud_a && p.longitud_a) {
            const isEnd = p.tipo === 'destino';
            const isStart = p.tipo === 'origen';
            const markerColor = isStart ? '#22c55e' : (isEnd ? '#ef4444' : RUTA_COLOR);

            L.circleMarker([parseFloat(p.latitud_a), parseFloat(p.longitud_a)], {
                radius: 4.5,
                fillColor: markerColor,
                color: '#ffffff',
                weight: 1.5,
                opacity: 1,
                fillOpacity: 0.95
            }).addTo(mapConductor);
        }
    });

    setTimeout(() => {
        if (mapConductor) mapConductor.invalidateSize();
    }, 300);
}

window.recentrarEnConductor = function() {
    userHasPanned = false;
    if (currentLat !== null && currentLng !== null && mapConductor) {
        mapConductor.setView([currentLat, currentLng], 16, { animate: true });
    } else if (mapConductor) {
        mapConductor.locate({ setView: true, maxZoom: 16 });
    }
};

// --- GEOLOCALIZACIÓN DEL PARADERO EN TIEMPO REAL ---
let currentLat = null;
let currentLng = null;

window.verificarGPSParaderoSeleccionado = function() {
    const selectEl = document.getElementById('paradero_llegada_id');
    const infoPanel = document.getElementById('paradero-coords-info');
    
    if (!selectEl.value) {
        infoPanel.style.display = 'none';
        return;
    }

    const opt = selectEl.options[selectEl.selectedIndex];
    const latA = parseFloat(opt.getAttribute('data-lat-a'));
    const lngA = parseFloat(opt.getAttribute('data-lng-a'));
    const latB = parseFloat(opt.getAttribute('data-lat-b'));
    const lngB = parseFloat(opt.getAttribute('data-lng-b'));
    const tolerance = parseInt(opt.getAttribute('data-tolerancia')) || 30;

    infoPanel.style.display = 'block';

    if (isNaN(latA) || isNaN(lngA)) {
        const badge = document.getElementById('info-badge');
        badge.textContent = 'PERMITIDO';
        badge.style.background = 'var(--green)';
        document.getElementById('info-dist-text').textContent = 'Este paradero no exige validación de GPS.';
        document.getElementById('info-dist-text').style.color = 'var(--text)';
        return;
    }

    if (currentLat === null || currentLng === null) {
        const badge = document.getElementById('info-badge');
        badge.textContent = 'ESPERANDO GPS';
        badge.style.background = 'var(--orange)';
        document.getElementById('info-dist-text').textContent = 'Obteniendo señal de GPS de tu celular...';
        document.getElementById('info-dist-text').style.color = 'var(--text)';
        return;
    }

    const check = isPointWithinSegmentJS(currentLat, currentLng, latA, lngA, isNaN(latB) ? latA : latB, isNaN(lngB) ? lngA : lngB, tolerance);
    
    const badge = document.getElementById('info-badge');
    const distText = document.getElementById('info-dist-text');

    if (check.within) {
        badge.textContent = 'DENTRO DE RANGO';
        badge.style.background = '#22c55e';
        distText.textContent = `Distancia: ${check.distance.toFixed(1)} metros. ¡Puedes terminar!`;
        distText.style.color = '#22c55e';
    } else {
        badge.textContent = 'FUERA DE RANGO';
        badge.style.background = '#ef4444';
        distText.textContent = `Distancia: ${check.distance.toFixed(1)} metros. Acércate más.`;
        distText.style.color = '#ef4444';
    }
};

function isPointWithinSegmentJS(latP, lngP, latA, lngA, latB, lngB, toleranceMeters) {
    const latRef = (latA + latB) / 2;
    const degToRad = Math.PI / 180;
    
    const scaleX = Math.cos(latRef * degToRad);
    
    const dy = latB - latA;
    const dx = (lngB - lngA) * scaleX;
    
    const dyp = latP - latA;
    const dxp = (lngP - lngA) * scaleX;
    
    const ab2 = (dx * dx) + (dy * dy);
    if (ab2 === 0) {
        const dist = calcularDistanciaMetros(latP, lngP, latA, lngA);
        return { within: dist <= toleranceMeters, distance: dist, tolerance: toleranceMeters };
    }
    
    const ap_ab = (dxp * dx) + (dyp * dy);
    let t = ap_ab / ab2;
    t = Math.max(0, Math.min(1, t));
    
    const latProj = latA + t * dy;
    const lngProj = lngA + t * (lngB - lngA);
    
    const distance = calcularDistanciaMetros(latP, lngP, latProj, lngProj);
    return { within: distance <= toleranceMeters, distance: distance, tolerance: toleranceMeters };
}

let terminando = false;

function confirmarTerminar() {
    const selectEl = document.getElementById('paradero_llegada_id');
    const paraderoLlegadaId = selectEl.value;
    if (!paraderoLlegadaId) {
        Swal.fire({
            title: 'Paradero Requerido',
            text: 'Debes seleccionar el paradero en el que vas a terminar tu vuelta.',
            icon: 'warning',
            confirmButtonColor: 'var(--accent)',
            confirmButtonText: 'Entendido'
        });
        return;
    }

    const tiempoActual = document.getElementById('cronometro').textContent;
    const paraderoNombre = selectEl.options[selectEl.selectedIndex].text;
    
    terminando = true;
    
    Swal.fire({
        title: '¿Finalizar Vuelta?',
        html: `El tiempo transcurrido es <b style="font-family:monospace; font-size:1.2em;">${tiempoActual}</b>.<br>Paradero de destino: <b>${paraderoNombre}</b>.<br><br>¿Estás seguro que deseas terminar la vuelta ahora?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: 'var(--red)',
        cancelButtonColor: 'var(--text3)',
        confirmButtonText: 'Sí, finalizar',
        cancelButtonText: 'Cancelar',
        reverseButtons: true,
        backdrop: `rgba(220, 38, 38, 0.1)`
    }).then((result) => {
        if (result.isConfirmed) {
            if (cronometroIntervalId) clearInterval(cronometroIntervalId);
            terminarVuelta(paraderoLlegadaId);
        } else {
            terminando = false;
        }
    });
}

async function terminarVuelta(paraderoLlegadaId) {
    document.getElementById('btn-terminar').disabled = true;
    document.getElementById('terminando-msg').classList.remove('hidden');

    if (watchId) navigator.geolocation.clearWatch(watchId);

    let lat = null, lng = null;
    try {
        const pos = await new Promise((resolve) => {
            const timeout = setTimeout(() => resolve(null), 15000);
            navigator.geolocation.getCurrentPosition(
                p => { clearTimeout(timeout); resolve(p); },
                e => { clearTimeout(timeout); resolve(null); },
                { enableHighAccuracy: true, timeout: 14000, maximumAge: 0 }
            );
        });
        if (pos) {
            lat = pos.coords.latitude;
            lng = pos.coords.longitude;
        }
    } catch (_) {}

    if (lat === null || lng === null) {
        alert('No se pudo verificar tu ubicación de llegada. Asegúrate de tener activado el GPS de tu celular y vuelve a intentarlo.');
        document.getElementById('btn-terminar').disabled = false;
        document.getElementById('terminando-msg').classList.add('hidden');
        terminando = false;
        return;
    }

    try {
        const resp = await fetch(TERMINAR_URL, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
            body: JSON.stringify({ 
                latitud: lat, 
                longitud: lng, 
                paradero_llegada_id: paraderoLlegadaId 
            })
        });
        const data = await resp.json();
        if (data.ok) {
            Swal.fire({
                title: '¡Vuelta Finalizada!',
                text: data.paradero ? `Has terminado la ruta en el paradero ${data.paradero}.` : 'Has terminado la vuelta correctamente.',
                icon: 'success',
                confirmButtonColor: 'var(--green)',
                confirmButtonText: 'Aceptar'
            }).then(() => {
                window.location.href = data.redirect;
            });
        } else {
            alert('❌ ' + (data.error || 'Error al terminar vuelta'));
            document.getElementById('btn-terminar').disabled = false;
            document.getElementById('terminando-msg').classList.add('hidden');
            terminando = false;
        }
    } catch (e) {
        alert('❌ Error de conexión al servidor.');
        document.getElementById('btn-terminar').disabled = false;
        document.getElementById('terminando-msg').classList.add('hidden');
        terminando = false;
    }
}

// --- GPS BACKGROUND WATCHING Y ACTUALIZACIÓN VISUAL DEL CONDUCTOR ---
let lastLat = null;
let lastLng = null;
let lastSendTime = 0;
let watchId = null;

function calcularDistanciaMetros(lat1, lon1, lat2, lon2) {
    const R = 6371000;
    const dLat = (lat2 - lat1) * Math.PI / 180;
    const dLon = (lon2 - lon1) * Math.PI / 180;
    const a = Math.sin(dLat/2) * Math.sin(dLat/2) +
              Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
              Math.sin(dLon/2) * Math.sin(dLon/2);
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
    return R * c;
}

function actualizarPosicionConductorEnMapa(lat, lng) {
    if (!mapConductor) return;

    if (!driverMarker) {
        const driverIcon = L.divIcon({
            html: `<div class="driver-marker-pulse"><i class="fa-solid fa-location-arrow" style="font-size:11px; transform:rotate(-45deg);"></i></div>`,
            className: 'custom-driver-icon',
            iconSize: [24, 24],
            iconAnchor: [12, 12]
        });

        driverMarker = L.marker([lat, lng], { icon: driverIcon, zIndexOffset: 1000 }).addTo(mapConductor);
        driverMarker.bindTooltip(`<b>Tú</b> (Vehículo ${FLOTA_NUM})`, { direction: 'top', offset: [0, -10] });

        if (!userHasPanned) {
            mapConductor.setView([lat, lng], 16);
        }
    } else {
        // Actualizar coordenadas sin recrear objetos (bajo consumo de CPU y batería)
        driverMarker.setLatLng([lat, lng]);
        if (!userHasPanned) {
            mapConductor.panTo([lat, lng], { animate: true, duration: 0.5 });
        }
    }
}

function iniciarRastreoGPS() {
    if (terminando) return;

    if (!navigator.geolocation) {
        console.warn("Geolocalización no soportada");
        return;
    }

    watchId = navigator.geolocation.watchPosition(
        async (pos) => {
            if (terminando) {
                if (watchId) navigator.geolocation.clearWatch(watchId);
                return;
            }

            const lat = pos.coords.latitude;
            const lng = pos.coords.longitude;
            const ahora = Date.now();

            currentLat = lat;
            currentLng = lng;

            // Actualizar visualmente en el mapa en vivo del conductor
            actualizarPosicionConductorEnMapa(lat, lng);

            if (typeof verificarGPSParaderoSeleccionado === 'function') {
                verificarGPSParaderoSeleccionado();
            }

            // Filtro inteligente para no saturar la red ni gastar batería
            if (lastLat !== null && lastLng !== null) {
                const distancia = calcularDistanciaMetros(lastLat, lastLng, lat, lng);
                const tiempoTranscurrido = (ahora - lastSendTime) / 1000;

                if (distancia < 10 && tiempoTranscurrido < 20) {
                    return;
                }
            }

            lastLat = lat;
            lastLng = lng;
            lastSendTime = ahora;

            try {
                const resp = await fetch(UBICACION_URL, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
                    body: JSON.stringify({ latitud: lat, longitud: lng })
                });
                const data = await resp.json();
                if (data.ok) {
                    console.log("GPS en ruta enviado:", lat, lng);
                }
            } catch (err) {
                console.error("Error enviando ubicación en segundo plano:", err);
            }
        },
        (err) => {
            console.error("Error capturando GPS en segundo plano:", err);
        },
        { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
    );
}

// Iniciar mapa y rastreo al cargar la pantalla
document.addEventListener('DOMContentLoaded', () => {
    inicializarMapaConductor();
    iniciarRastreoGPS();
});
</script>
@endsection

