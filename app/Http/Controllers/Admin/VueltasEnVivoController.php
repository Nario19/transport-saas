<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Vuelta;
use App\Models\Conductor;
use Illuminate\Http\Request;

class VueltasEnVivoController extends Controller
{
    /**
     * Vista del dashboard de vueltas en tiempo real.
     */
    public function index()
    {
        $empresaId = auth()->user()->empresa_id;
        $flota = request('flota');

        $vueltasActivasQuery = Vuelta::with(['conductor', 'vehiculo', 'ruta', 'paraderoSalida', 'paraderoLlegada'])
            ->where('empresa_id', $empresaId)
            ->where('estado', 'activa')
            ->whereDate('fecha', today())
            ->when($flota, function ($q) use ($flota) {
                return $q->whereHas('vehiculo', function ($vQ) use ($flota) {
                    $vQ->where('numero_flota', $flota);
                });
            })
            ->orderBy('hora_salida');

        $vueltasActivas = $vueltasActivasQuery->paginate(15)->withQueryString();
        $totalConductoresActivos = $vueltasActivas->total();

        $rutasTrazados = \App\Models\Ruta::where('empresa_id', $empresaId)
            ->where('estado', 'activa')
            ->with(['paraderos' => function ($q) {
                $q->orderBy('orden');
            }])
            ->get()
            ->map(function ($r) {
                $trazado = $r->trazado;
                if (is_string($trazado)) {
                    $trazado = json_decode($trazado, true);
                }

                return [
                    'id' => $r->id,
                    'nombre' => $r->nombre,
                    'origen' => $r->origen,
                    'destino' => $r->destino,
                    'trazado' => $trazado ?? [],
                    'color' => $r->color ?? '#3b82f6',
                    'paraderos' => $r->paraderos->map(function ($p) {
                        return [
                            'nombre' => $p->nombre,
                            'tipo' => $p->tipo,
                            'orden' => $p->orden,
                            'latitud_a' => $p->latitud_a,
                            'longitud_a' => $p->longitud_a,
                            'latitud_b' => $p->latitud_b,
                            'longitud_b' => $p->longitud_b,
                            'tolerancia' => $p->tolerancia,
                        ];
                    })->values(),
                ];
            })->values();

        return view('admin.vueltas.en-vivo', compact('vueltasActivas', 'totalConductoresActivos', 'rutasTrazados'));
    }

    /**
     * API JSON para polling — /admin/api/vueltas-activas
     */
    public function activas()
    {
        $empresaId = auth()->user()->empresa_id;
        $flota = request('flota');

        $activas = Vuelta::with(['conductor', 'vehiculo', 'ruta', 'paraderoSalida', 'paraderoLlegada'])
            ->where('empresa_id', $empresaId)
            ->where('estado', 'activa')
            ->whereDate('fecha', today())
            ->when($flota, function ($q) use ($flota) {
                return $q->whereHas('vehiculo', function ($vQ) use ($flota) {
                    $vQ->where('numero_flota', $flota);
                });
            })
            ->get();

        $data = $activas->map(function (Vuelta $v) {
            $inicio   = \Carbon\Carbon::parse($v->fecha->format('Y-m-d') . ' ' . $v->hora_salida);
            $minutos  = $inicio->diffInMinutes(now());

            return [
                'id'            => $v->id,
                'conductor'     => $v->conductor?->nombre_completo ?? '—',
                'vehiculo'      => $v->vehiculo?->placa ?? '—',
                'flota'         => $v->vehiculo?->numero_flota ?? '?',
                'ruta'          => $v->ruta?->nombre ?? 'Sin ruta',
                'ruta_origen'   => $v->ruta?->origen ?? 'a',
                'ruta_destino'  => $v->ruta?->destino ?? 'b',
                'hora_salida'   => $v->hora_salida,
                'paradero_salida'=> $v->paraderoSalida?->nombre ?? '—',
                'paradero_llegada'=> $v->paraderoLlegada?->nombre ?? '—',
                'paradero_salida_tipo'=> $v->paraderoSalida?->tipo,
                'paradero_llegada_tipo'=> $v->paraderoLlegada?->tipo,
                'numero_vuelta' => $v->numero_vuelta,
                'latitud'       => $v->lat_actual ?? $v->latitud,
                'longitud'      => $v->lng_actual ?? $v->longitud,
                'lat_salida'    => $v->latitud,
                'lng_salida'    => $v->longitud,
                'lat_actual'    => $v->lat_actual,
                'lng_actual'    => $v->lng_actual,
                'inicio_ts'     => $inicio->timestamp * 1000,
                'hora_llegada'  => '—',
                'minutos_en_ruta' => $minutos,
                'estimado_min'  => $v->ruta?->duracion_min ?? 0,
                'estado'        => 'activa',
                'badge_estado'  => $v->badge_estado,
                'tiempo_label'  => $minutos < 60 ? "{$minutos} min" : floor($minutos / 60) . 'h ' . ($minutos % 60) . 'min',
            ];
        });

        return response()->json([
            'total_activas' => $activas->count(),
            'vueltas'       => $data,
            'hora'          => now()->format('H:i:s'),
        ]);
    }

    public function forzarTerminar(Vuelta $vuelta, \App\Services\VueltaService $vueltaService)
    {
        if ($vuelta->empresa_id !== auth()->user()->empresa_id) {
            return response()->json(['success' => false, 'error' => 'No autorizado para gestionar esta vuelta.'], 403);
        }

        if ($vuelta->estado !== 'activa') {
            return response()->json(['success' => false, 'error' => 'Esta vuelta ya no se encuentra activa.'], 422);
        }

        try {
            // Determinar coordenadas finales (posición GPS actual si existe, o salida)
            $latFin = $vuelta->lat_actual ?? $vuelta->latitud;
            $lngFin = $vuelta->lng_actual ?? $vuelta->longitud;

            // Determinar paradero de llegada si aún no está fijado
            $paraderoLlegadaId = $vuelta->paradero_llegada_id;
            if (!$paraderoLlegadaId && $vuelta->ruta) {
                $destino = $vuelta->ruta->paraderos()->where('tipo', 'destino')->first();
                $paraderoLlegadaId = $destino?->id;
            }

            $duracion = $vueltaService->terminarVuelta($vuelta, $latFin, $lngFin, $paraderoLlegadaId);

            return response()->json([
                'success'  => true,
                'message'  => "La vuelta #{$vuelta->numero_vuelta} fue finalizada con éxito (Duración: {$duracion} min).",
                'duracion' => $duracion,
            ]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Error forzando término de vuelta: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => 'Error al finalizar la vuelta en el servidor.'], 500);
        }
    }

    public function guardarTrazado(\App\Models\Ruta $ruta, Request $request)
    {
        if ($ruta->empresa_id !== auth()->user()->empresa_id) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $request->validate([
            'trazado' => 'nullable|array',
            'color'   => 'nullable|string|max:10',
        ]);

        $ruta->update([
            'trazado' => $request->input('trazado'),
            'color'   => $request->input('color'),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Trazado y color de ruta actualizados con éxito.',
        ]);
    }
}

