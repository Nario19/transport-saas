<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Vehiculo;
use App\Models\Conductor;
use App\Models\Vuelta;
use App\Events\VueltaUbicacionActualizada;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TraccarGpsController extends Controller
{
    /**
     * Endpoint para recibir telemetría GPS directa desde Traccar Client / OsmAnd Protocol.
     * Soporta tanto peticiones GET como POST.
     */
    public function handle(Request $request)
    {
        // 1. Obtener identificador del dispositivo (Placa, Flota o DNI)
        $rawId = $request->input('id') ?? $request->input('device_id') ?? $request->input('uniqueId');

        if (!$rawId) {
            return response('ERR: Missing device ID', 400)->header('Content-Type', 'text/plain');
        }

        // 2. Obtener coordenadas
        $lat = $request->input('lat') ?? $request->input('latitude');
        $lon = $request->input('lon') ?? $request->input('lng') ?? $request->input('longitude');

        if ($lat === null || $lon === null || !is_numeric($lat) || !is_numeric($lon)) {
            return response('ERR: Invalid coordinates', 400)->header('Content-Type', 'text/plain');
        }

        $lat = (float) $lat;
        $lon = (float) $lon;

        // Validar rango geográfico realista
        if ($lat < -90 || $lat > 90 || $lon < -180 || $lon > 180) {
            return response('ERR: Out of bounds', 400)->header('Content-Type', 'text/plain');
        }

        // 3. Buscar el vehículo correspondiente
        $vehiculo = $this->buscarVehiculo($rawId);

        if (!$vehiculo) {
            // Se responde 200 OK para que Traccar Client no se quede en reintentos infinitos
            return response('OK: Vehicle not found or inactive', 200)->header('Content-Type', 'text/plain');
        }

        // 4. Buscar si el vehículo tiene una vuelta activa
        $vuelta = Vuelta::where('vehiculo_id', $vehiculo->id)
            ->where('estado', 'activa')
            ->latest()
            ->first();

        if ($vuelta) {
            $vuelta->update([
                'lat_actual' => $lat,
                'lng_actual' => $lon,
            ]);

            // Transmitir en tiempo real para el panel de administración
            try {
                broadcast(new VueltaUbicacionActualizada($vuelta, $lat, $lon));
            } catch (\Throwable $e) {
                // Si WebSockets no está configurado en el entorno, continuar sin fallar
            }
        }

        return response('OK', 200)->header('Content-Type', 'text/plain');
    }

    /**
     * Busca el vehículo por placa limpia, número de flota o DNI del conductor.
     */
    private function buscarVehiculo(string $identificador): ?Vehiculo
    {
        $limpio = strtoupper(trim(str_replace([' ', '-', '#'], '', $identificador)));

        // 1. Búsqueda por placa (ej: W1A-777 o W1A777)
        $vehiculo = Vehiculo::whereRaw("UPPER(REPLACE(placa, '-', '')) = ?", [$limpio])
            ->orWhere('placa', $identificador)
            ->first();

        if ($vehiculo) {
            return $vehiculo;
        }

        // 2. Búsqueda por número de flota (ej: 15, 33)
        if (is_numeric($limpio)) {
            $vehiculo = Vehiculo::where('numero_flota', (int) $limpio)->first();
            if ($vehiculo) {
                return $vehiculo;
            }
        }

        // 3. Búsqueda por DNI del conductor asignado
        $conductor = Conductor::where('dni', $limpio)->first();
        if ($conductor) {
            return $conductor->vehiculos()->activos()->first() ?? $conductor->vehiculos()->first();
        }

        return null;
    }
}
