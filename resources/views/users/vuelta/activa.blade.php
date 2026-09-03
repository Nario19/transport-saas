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

    /* Animación fluida de desplazamiento en hardware GPU */
    .custom-driver-icon {
        transition: transform 0.6s linear !important;
        will-change: transform;
    }

    .driver-nav-marker {
        position: relative;
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .driver-pulse-ring {
        position: absolute;
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: rgba(37, 99, 235, 0.3);
        animation: driverRing 2s ease-out infinite;
    }
    .driver-arrow-container {
        width: 24px;
        height: 24px;
        border-radius: 50%;
        background: #2563eb;
        border: 2.5px solid #ffffff;
        box-shadow: 0 2px 8px rgba(0,0,0,0.35);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 11px;
        transition: transform 0.35s ease;
    }
    @keyframes driverRing {
        0% { transform: scale(0.6); opacity: 1; }
        100% { transform: scale(1.6); opacity: 0; }
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

    <div class="map-box">
        <div id="map-conductor"></div>
        <button type="button" onclick="recentrarEnConductor()" 
                style="position: absolute; bottom: 8px; right: 8px; z-index: 500; background: white; color: #0f172a; border: 1px solid var(--border); border-radius: 8px; width: 34px; height: 34px; box-shadow: 0 2px 6px rgba(0,0,0,0.25); display: flex; align-items: center; justify-content: center; font-size: 15px; cursor: pointer;"
                title="Recentrar en mi ubicación">
            <i class="fa-solid fa-crosshairs" style="color: var(--accent);"></i>
        </button>
    </div>
</div>

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

<div class="card" style="margin-bottom: 16px;">
    <div class="card-body" style="padding: 16px;">
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
        <div class="summary-row" style="display: flex; justify-content: space-between; align-items: center; padding: 10px 0; border-bottom: 1px solid #f1f5f9;">
            <span class="summary-label" style="font-weight:600; color: var(--text2); display: flex; align-items: center; gap: 8px;">
                <i class="fa-solid fa-map-pin" style="color: var(--green); width: 16px;"></i> Paradero de Inicio
            </span>
            <span class="summary-val" style="font-weight: 700; color: var(--text); text-align: right;">
                {{ $vuelta->paraderoSalida?->nombre ?? ($vuelta->ruta?->origen ?? 'Inicio de Ruta') }}
            </span>
        </div>
        <div class="summary-row" style="display: flex; justify-content: space-between; align-items: center; padding: 10px 0; border-bottom: 1px solid #f1f5f9;">
            <span class="summary-label" style="font-weight:600; color: var(--text2); display: flex; align-items: center; gap: 8px;">
                <i class="fa-solid fa-route" style="color: var(--gold); width: 16px;"></i> Ruta
            </span>
            <span class="summary-val" style="font-weight: 700; color: var(--text); text-align: right;">
                {{ $vuelta->ruta?->nombre ?? 'Sin ruta asignada' }}
            </span>
        </div>
        <div class="summary-row" style="display: flex; justify-content: space-between; align-items: center; padding: 10px 0; border-bottom: 1px solid #f1f5f9;">
            <span class="summary-label" style="font-weight:600; color: var(--text2); display: flex; align-items: center; gap: 8px;">
                <i class="fa-regular fa-clock" style="color: var(--text3); width: 16px;"></i> Salida
            </span>
            <span class="summary-val" style="font-weight: 700; color: var(--text);">
                {{ $vuelta->hora_salida }}
            </span>
        </div>
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
        <div id="paradero-coords-info" style="margin-top: 12px; display: none; padding: 12px; border-radius: 8px; background: var(--bg); border: 1px solid var(--border); font-size: 13px;">
            <div style="font-weight: 700; color: var(--text2); margin-bottom: 6px;"><i class="fa-solid fa-circle-info" style="color: var(--accent);"></i> Estado del Paradero</div>
            <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                <span id="info-badge" class="pill" style="font-size: 11px; font-weight: 800; padding: 3px 8px; border-radius: 99px; color: white;">—</span>
                <span id="info-dist-text" style="font-weight: 700; color: var(--text);">—</span>
            </div>
        </div>
    </div>
</div>

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

let mapConductor = null;
let driverMarker = null;
let userHasPanned = false;
let currentLat = null;
let currentLng = null;
let previousLat = null;
let previousLng = null;

function calcularBearing(lat1, lon1, lat2, lon2) {
    const toRad = Math.PI / 180;
    const toDeg = 180 / Math.PI;
    const dLon = (lon2 - lon1) * toRad;
    const y = Math.sin(dLon) * Math.cos(lat2 * toRad);
    const x = Math.cos(lat1 * toRad) * Math.sin(lat2 * toRad) -
              Math.sin(lat1 * toRad) * Math.cos(lat2 * toRad) * Math.cos(dLon);
    let brng = Math.atan2(y, x) * toDeg;
    return (brng + 360) % 360;
}

function construirTrazadoParaderosJS(paraderosList) {
    const validos = (paraderosList || []).filter(p => p.latitud_a && p.longitud_a);
    if (validos.length === 0) return [];
    if (validos.length === 1) return [[parseFloat(validos[0].latitud_a), parseFloat(validos[0].longitud_a)]];
    const origenes = validos.filter(p => p.tipo === 'origen');
    const intermedios = validos.filter(p => p.tipo === 'intermedio');
    const destinos = validos.filter(p => p.tipo === 'destino');
    const getCoord = (p) => [parseFloat(p.latitud_a), parseFloat(p.longitud_a)];
    if (origenes.length <= 1 && destinos.length <= 1) return validos.map(getCoord);
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
    mapConductor = L.map('map-conductor', {
        preferCanvas: true,
        zoomControl: false,
        attributionControl: false,
        fadeAnimation: true,
        markerZoomAnimation: true,
        inertia: true,
        inertiaDeceleration: 3000
    }).setView([-12.065, -75.204], 16);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 18,
        minZoom: 11,
        keepBuffer: 2,
        updateWhenIdle: true
    }).addTo(mapConductor);
    mapConductor.on('dragstart', () => { userHasPanned = true; });
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
    if (allFlat.length >= 2) {
        L.polyline(lineCoords, { color: RUTA_COLOR, weight: 5, opacity: 0.85 }).addTo(mapConductor);
        mapConductor.fitBounds(L.latLngBounds(allFlat), { padding: [25, 25] });
    }
    (PARADEROS || []).forEach(p => {
        if (p.latitud_a && p.longitud_a) {
            const isEnd = p.tipo === 'destino';
            const isStart = p.tipo === 'origen';
            const markerColor = isStart ? '#22c55e' : (isEnd ? '#ef4444' : RUTA_COLOR);
            L.circleMarker([parseFloat(p.latitud_a), parseFloat(p.longitud_a)], {
                radius: 5, fillColor: markerColor, color: '#ffffff', weight: 1.5, opacity: 1, fillOpacity: 0.95
            }).addTo(mapConductor);
        }
    });
    setTimeout(() => { if (mapConductor) mapConductor.invalidateSize(); }, 250);
}

window.recentrarEnConductor = function() {
    userHasPanned = false;
    if (currentLat !== null && currentLng !== null && mapConductor) {
        mapConductor.panTo([currentLat, currentLng], { animate: true, duration: 0.6 });
    }
};

function actualizarPosicionConductorEnMapa(lat, lng, heading) {
    if (!mapConductor) return;
    if (!driverMarker) {
        const driverIcon = L.divIcon({
            html: `
                <div class="driver-nav-marker">
                    <div class="driver-pulse-ring"></div>
                    <div class="driver-arrow-container" id="driver-arrow-icon">
                        <i class="fa-solid fa-location-arrow" style="transform:rotate(-45deg);"></i>
                    </div>
                </div>
            `,
            className: 'custom-driver-icon',
            iconSize: [32, 32],
            iconAnchor: [16, 16]
        });
        driverMarker = L.marker([lat, lng], { icon: driverIcon, zIndexOffset: 1000 }).addTo(mapConductor);
        if (!userHasPanned) mapConductor.setView([lat, lng], 16, { animate: true });
    } else {
        driverMarker.setLatLng([lat, lng]);
        let angle = heading;
        if (angle === null || angle === undefined || isNaN(angle)) {
            if (previousLat !== null && previousLng !== null) {
                const dist = calcularDistanciaMetros(previousLat, previousLng, lat, lng);
                if (dist > 1.5) angle = calcularBearing(previousLat, previousLng, lat, lng);
            }
        }
        if (angle !== null && angle !== undefined && !isNaN(angle)) {
            const arrowEl = document.getElementById('driver-arrow-icon');
            if (arrowEl) arrowEl.style.transform = `rotate(${angle}deg)`;
        }
        if (!userHasPanned) mapConductor.panTo([lat, lng], { animate: true, duration: 0.6, easeLinearity: 0.3 });
    }
    previousLat = lat;
    previousLng = lng;
}

window.verificarGPSParaderoSeleccionado = function() {
    const selectEl = document.getElementById('paradero_llegada_id');
    const infoPanel = document.getElementById('paradero-coords-info');
    if (!selectEl.value) { infoPanel.style.display = 'none'; return; }
    const opt = selectEl.options[selectEl.selectedIndex];
    const latA = parseFloat(opt.getAttribute('data-lat-a')), lngA = parseFloat(opt.getAttribute('data-lng-a'));
    const latB = parseFloat(opt.getAttribute('data-lat-b')), lngB = parseFloat(opt.getAttribute('data-lng-b'));
    const tolerance = parseInt(opt.getAttribute('data-tolerancia')) || 30;
    infoPanel.style.display = 'block';
    if (isNaN(latA) || isNaN(lngA)) {
        const badge = document.getElementById('info-badge');
        badge.textContent = 'PERMITIDO'; badge.style.background = 'var(--green)';
        document.getElementById('info-dist-text').textContent = 'Este paradero no exige validación de GPS.';
        return;
    }
    if (currentLat === null || currentLng === null) {
        const badge = document.getElementById('info-badge');
        badge.textContent = 'ESPERANDO GPS'; badge.style.background = 'var(--orange)';
        document.getElementById('info-dist-text').textContent = 'Obteniendo señal GPS...';
        return;
    }
    const check = isPointWithinSegmentJS(currentLat, currentLng, latA, lngA, isNaN(latB) ? latA : latB, isNaN(lngB) ? lngA : lngB, tolerance);
    const badge = document.getElementById('info-badge'), distText = document.getElementById('info-dist-text');
    if (check.within) {
        badge.textContent = 'DENTRO DE RANGO'; badge.style.background = '#22c55e';
        distText.textContent = `Distancia: ${check.distance.toFixed(1)} metros.`;
    } else {
        badge.textContent = 'FUERA DE RANGO'; badge.style.background = '#ef4444';
        distText.textContent = `Distancia: ${check.distance.toFixed(1)} metros.`;
    }
};

function isPointWithinSegmentJS(latP, lngP, latA, lngA, latB, lngB, toleranceMeters) {
    const latRef = (latA + latB) / 2;
    const degToRad = Math.PI / 180;
    const scaleX = Math.cos(latRef * degToRad);
    const dy = latB - latA, dx = (lngB - lngA) * scaleX;
    const dyp = latP - latA, dxp = (lngP - lngA) * scaleX;
    const ab2 = (dx * dx) + (dy * dy);
    if (ab2 === 0) {
        const dist = calcularDistanciaMetros(latP, lngP, latA, lngA);
        return { within: dist <= toleranceMeters, distance: dist };
    }
    const ap_ab = (dxp * dx) + (dyp * dy);
    let t = Math.max(0, Math.min(1, ap_ab / ab2));
    const latProj = latA + t * dy, lngProj = lngA + t * (lngB - lngA);
    const distance = calcularDistanciaMetros(latP, lngP, latProj, lngProj);
    return { within: distance <= toleranceMeters, distance: distance };
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
        Swal.fire({
            title: 'GPS no detectado',
            text: 'No se pudo verificar tu ubicación de llegada. Asegúrate de tener activado el GPS de tu celular y vuelve a intentarlo.',
            icon: 'error',
            confirmButtonColor: 'var(--accent)'
        });
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
            Swal.fire({
                title: 'No se pudo finalizar',
                text: data.error || 'Error al terminar vuelta.',
                icon: 'error',
                confirmButtonColor: 'var(--red)'
            });
            document.getElementById('btn-terminar').disabled = false;
            document.getElementById('terminando-msg').classList.add('hidden');
            terminando = false;
        }
    } catch (e) {
        Swal.fire({
            title: 'Error de Conexión',
            text: 'No se pudo contactar con el servidor. Verifica tu conexión a internet.',
            icon: 'error',
            confirmButtonColor: 'var(--accent)'
        });
        document.getElementById('btn-terminar').disabled = false;
        document.getElementById('terminando-msg').classList.add('hidden');
        terminando = false;
    }
}

let lastLat = null, lastLng = null, lastSendTime = 0, watchId = null;
function calcularDistanciaMetros(lat1, lon1, lat2, lon2) {
    const R = 6371000;
    const dLat = (lat2 - lat1) * Math.PI / 180, dLon = (lon2 - lon1) * Math.PI / 180;
    const a = Math.sin(dLat/2) * Math.sin(dLat/2) + Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) * Math.sin(dLon/2) * Math.sin(dLon/2);
    return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
}

function iniciarRastreoGPS() {
    if (terminando) return;
    if (!navigator.geolocation) return;
    watchId = navigator.geolocation.watchPosition(async (pos) => {
        if (terminando) { if (watchId) navigator.geolocation.clearWatch(watchId); return; }
        const lat = pos.coords.latitude, lng = pos.coords.longitude, heading = pos.coords.heading, ahora = Date.now();
        currentLat = lat; currentLng = lng;
        actualizarPosicionConductorEnMapa(lat, lng, heading);
        if (typeof verificarGPSParaderoSeleccionado === 'function') verificarGPSParaderoSeleccionado();
        if (lastLat !== null && lastLng !== null) {
            if (calcularDistanciaMetros(lastLat, lastLng, lat, lng) < 10 && (ahora - lastSendTime) < 20000) return;
        }
        lastLat = lat; lastLng = lng; lastSendTime = ahora;
        try {
            const resp = await fetch(UBICACION_URL, { 
                method: 'POST', 
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF }, 
                body: JSON.stringify({ latitud: lat, longitud: lng }) 
            });
            if (resp.status === 404) {
                if (watchId) navigator.geolocation.clearWatch(watchId);
                Swal.fire({
                    title: 'Vuelta Finalizada',
                    text: 'Esta vuelta ha sido dada por finalizada por la administración.',
                    icon: 'info',
                    confirmButtonColor: 'var(--accent)',
                    confirmButtonText: 'Aceptar'
                }).then(() => {
                    window.location.href = '{{ route("conductor.vuelta.iniciar", [], false) }}';
                });
            }
        } catch (_) {}
    }, null, { enableHighAccuracy: true, timeout: 8000, maximumAge: 0 });
}

let wakeLock = null;
async function solicitarWakeLock() {
    try {
        if ('wakeLock' in navigator) {
            wakeLock = await navigator.wakeLock.request('screen');
            document.addEventListener('visibilitychange', async () => {
                if (wakeLock !== null && document.visibilityState === 'visible') {
                    wakeLock = await navigator.wakeLock.request('screen');
                }
            });
        }
    } catch (_) {}
}

document.addEventListener('DOMContentLoaded', () => {
    inicializarMapaConductor();
    iniciarRastreoGPS();
    solicitarWakeLock();
});
</script>
@endsection
