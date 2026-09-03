<?php

namespace App\Http\Controllers\Conductor;

use App\Http\Controllers\Controller;
use App\Models\AlertaOperativo;
use App\Models\PuntoControl;
use App\Models\TipoAlerta;
use App\Events\AlertaOperativoCreada;
use App\Events\AlertaOperativoFinalizada;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Validation\Rule;

class AlertaOperativoController extends Controller
{
    /**
     * ==========================================
     * CONDUCTOR ACTIONS (JSON API)
     * ==========================================
     */

    /**
     * Reportar un operativo o alerta (Conductor).
     */
    public function store(Request $request)
    {
        $user = auth()->user();
        $conductor = $user->conductor;

        if (!$conductor) {
            return response()->json(['error' => 'No tienes un perfil de conductor asociado.'], 403);
        }

        $empresaId = $conductor->empresa_id;

        // Opción 1: El conductor reportó una alerta de la lista configurada por el Administrador
        if ($request->filled('alerta_id')) {
            $base = AlertaOperativo::where('empresa_id', $empresaId)
                ->where('visible_conductor', true)
                ->find($request->alerta_id);

            if (!$base) {
                return response()->json(['message' => 'Esta alerta no está disponible actualmente.'], 422);
            }

            // Evitar duplicados activos idénticos
            $existeActivo = AlertaOperativo::where('empresa_id', $empresaId)
                ->where('titulo', $base->titulo)
                ->where('punto', $base->punto)
                ->where('estado', 'activo')
                ->where('expires_at', '>', now())
                ->exists();

            if ($existeActivo) {
                return response()->json(['message' => "Ya existe un reporte activo para {$base->titulo}."], 200);
            }

            $alerta = AlertaOperativo::create([
                'empresa_id'        => $empresaId,
                'conductor_id'      => $conductor->id,
                'user_id'           => null,
                'titulo'            => $base->titulo,
                'punto'             => $base->punto,
                'mensaje'           => $base->mensaje,
                'tipo'              => $base->tipo,
                'visible_conductor' => true,
                'estado'            => 'activo',
                'expires_at'        => now()->addMinutes(45),
            ]);

            try {
                broadcast(new AlertaOperativoCreada($alerta))->toOthers();
            } catch (\Exception $e) {}

            return response()->json([
                'success' => true,
                'message' => "Alerta '{$alerta->titulo}' reportada a todos los compañeros.",
                'alerta'  => $alerta
            ]);
        }

        // Opción 2: El conductor seleccionó un punto de control
        $request->validate([
            'punto' => [
                'required',
                'string',
                Rule::exists('puntos_control', 'nombre')->where('empresa_id', $empresaId)
            ],
        ]);

        // Evitar duplicados activos en el mismo punto para la misma empresa
        $existeActivo = AlertaOperativo::where('empresa_id', $empresaId)
            ->where('punto', $request->punto)
            ->where('estado', 'activo')
            ->where('expires_at', '>', now())
            ->exists();

        if ($existeActivo) {
            return response()->json(['message' => 'Ya existe un reporte activo para este punto.'], 200);
        }

        $alerta = AlertaOperativo::create([
            'empresa_id'        => $empresaId,
            'conductor_id'      => $conductor->id,
            'user_id'           => null,
            'titulo'            => '🚨 Control Policial / Operativo',
            'punto'             => $request->punto,
            'mensaje'           => 'Fiscalización reportada en ruta.',
            'tipo'              => 'Operativo / Control',
            'visible_conductor' => true,
            'estado'            => 'activo',
            'expires_at'        => now()->addMinutes(30),
        ]);

        // Transmitir evento en tiempo real
        try {
            broadcast(new AlertaOperativoCreada($alerta))->toOthers();
        } catch (\Exception $e) {}

        return response()->json([
            'success' => true,
            'message' => "Operativo reportado en {$alerta->punto} correctamente.",
            'alerta'  => $alerta
        ]);
    }

    /**
     * Finalizar un operativo (Conductor).
     */
    public function finalizar(AlertaOperativo $alerta)
    {
        $user = auth()->user();
        $conductor = $user->conductor;

        if (!$conductor || $alerta->empresa_id !== $conductor->empresa_id) {
            return response()->json(['error' => 'Acceso no autorizado.'], 403);
        }

        // Que solo pueda apagar la alerta el que la emitió
        if ($alerta->conductor_id !== $conductor->id) {
            return response()->json(['error' => 'Solo el conductor que reportó este operativo puede desactivar la alerta.'], 403);
        }

        if ($alerta->estado === 'activo') {
            $alerta->update(['estado' => 'finalizado']);
            try {
                broadcast(new AlertaOperativoFinalizada($alerta))->toOthers();
            } catch (\Exception $e) {}
        }

        return response()->json([
            'success' => true,
            'message' => 'El operativo se marcó como finalizado.'
        ]);
    }


    /**
     * ==========================================
     * ADMINISTRATOR ACTIONS (Web Views & Redirects)
     * ==========================================
     */

    /**
     * Vista de control y listado (Administrador).
     */
    public function adminIndex()
    {
        $empresaId = auth()->user()->empresa_id;

        // Puntos de control registrados para la empresa
        $puntos = PuntoControl::where('empresa_id', $empresaId)->orderBy('nombre')->get();

        // Tipos de alerta registrados para la empresa
        $tiposAlerta = TipoAlerta::where('empresa_id', $empresaId)->orderBy('nombre')->get();

        // Alertas Activas (estado activo y sin expirar)
        $activas = AlertaOperativo::where('empresa_id', $empresaId)
            ->where('estado', 'activo')
            ->where('expires_at', '>', now())
            ->with(['conductor', 'user'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Alertas Pasadas (finalizadas o ya expiradas)
        $historial = AlertaOperativo::where('empresa_id', $empresaId)
            ->where(function ($q) {
                $q->where('estado', 'finalizado')
                  ->orWhere('expires_at', '<=', now());
            })
            ->with(['conductor', 'user'])
            ->orderBy('created_at', 'desc')
            ->take(20)
            ->get();

        return view('admin.alertas.index', compact('activas', 'historial', 'puntos', 'tiposAlerta'));
    }

    /**
     * Reportar una alerta personalizada (Administrador).
     */
    public function adminStore(Request $request)
    {
        $empresaId = auth()->user()->empresa_id;

        $request->validate([
            'titulo' => 'required|string|max:150',
            'punto' => 'nullable|string|max:150',
            'mensaje' => 'nullable|string|max:1000',
            'tipo' => 'nullable|string|max:100',
            'duracion_minutos' => 'nullable|integer|min:5|max:10080',
            'visible_conductor' => 'nullable',
        ]);

        $punto = $request->input('punto') ?: 'Ubicación General';
        $duracion = (int) $request->input('duracion_minutos', 60);
        if ($duracion <= 0) $duracion = 60;

        $visible = $request->has('visible_conductor') ? $request->boolean('visible_conductor') : true;

        $alerta = AlertaOperativo::create([
            'empresa_id'        => $empresaId,
            'conductor_id'      => null,
            'user_id'           => auth()->id(),
            'titulo'            => $request->input('titulo'),
            'punto'             => $punto,
            'mensaje'           => $request->input('mensaje'),
            'tipo'              => $request->input('tipo', 'Operativo / Control'),
            'visible_conductor' => $visible,
            'estado'            => 'activo',
            'expires_at'        => now()->addMinutes($duracion),
        ]);

        try {
            broadcast(new AlertaOperativoCreada($alerta))->toOthers();
        } catch (\Exception $e) {}

        return back()->with('success', "Alerta '{$alerta->titulo}' emitida correctamente.");
    }

    /**
     * Alternar visibilidad de la alerta para los conductores (Ocultar / Mostrar).
     */
    public function adminToggleVisibilidad(AlertaOperativo $alerta)
    {
        if ($alerta->empresa_id !== auth()->user()->empresa_id) {
            abort(403, 'Acceso no autorizado.');
        }

        $nuevoEstado = !$alerta->visible_conductor;
        $alerta->update(['visible_conductor' => $nuevoEstado]);

        $msg = $nuevoEstado ? 'visible para los conductores' : 'oculta para los conductores';
        return back()->with('success', "La alerta '{$alerta->titulo}' ahora está {$msg}.");
    }

    /**
     * Finalizar un operativo (Administrador).
     */
    public function adminFinalizar(AlertaOperativo $alerta)
    {
        if ($alerta->empresa_id !== auth()->user()->empresa_id) {
            abort(403, 'Acceso no autorizado.');
        }

        if ($alerta->estado === 'activo') {
            $alerta->update(['estado' => 'finalizado']);
            try {
                broadcast(new AlertaOperativoFinalizada($alerta))->toOthers();
            } catch (\Exception $e) {}
        }

        return back()->with('success', 'La alerta se marcó como finalizada.');
    }

    /**
     * Re-emitir una alerta existente / del historial (Administrador).
     */
    public function adminReemitir(AlertaOperativo $alerta, Request $request)
    {
        $empresaId = auth()->user()->empresa_id;
        if ($alerta->empresa_id !== $empresaId) {
            abort(403, 'Acceso no autorizado.');
        }

        $duracion = (int) $request->input('duracion_minutos', 60);
        if ($duracion <= 0) $duracion = 60;

        $nuevaAlerta = AlertaOperativo::create([
            'empresa_id'        => $empresaId,
            'conductor_id'      => null,
            'user_id'           => auth()->id(),
            'titulo'            => $alerta->titulo,
            'punto'             => $alerta->punto,
            'mensaje'           => $alerta->mensaje,
            'tipo'              => $alerta->tipo,
            'visible_conductor' => $alerta->visible_conductor,
            'estado'            => 'activo',
            'expires_at'        => now()->addMinutes($duracion),
        ]);

        try {
            broadcast(new AlertaOperativoCreada($nuevaAlerta))->toOthers();
        } catch (\Exception $e) {}

        return back()->with('success', "Alerta '{$nuevaAlerta->titulo}' emitida de nuevo a la flota.");
    }

    /**
     * Eliminar una alerta del historial (Administrador).
     */
    public function adminDestroy(AlertaOperativo $alerta)
    {
        if ($alerta->empresa_id !== auth()->user()->empresa_id) {
            abort(403, 'Acceso no autorizado.');
        }

        $alerta->delete();

        return back()->with('success', 'Alerta eliminada del historial.');
    }

    /**
     * Agregar un nuevo tipo de alerta predefinido (Administrador).
     */
    public function adminAddTipo(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:100',
        ]);

        $empresaId = auth()->user()->empresa_id;

        // Evitar duplicados de nombre para la misma empresa
        $existe = TipoAlerta::where('empresa_id', $empresaId)
            ->where('nombre', $request->nombre)
            ->exists();

        if ($existe) {
            return back()->with('error', 'Este tipo de alerta ya existe.');
        }

        TipoAlerta::create([
            'empresa_id' => $empresaId,
            'nombre'     => $request->nombre,
        ]);

        return back()->with('success', 'Tipo de alerta agregado correctamente.');
    }

    /**
     * Eliminar un tipo de alerta (Administrador).
     */
    public function adminDeleteTipo($id)
    {
        $empresaId = auth()->user()->empresa_id;
        $tipo = TipoAlerta::where('empresa_id', $empresaId)->findOrFail($id);
        $tipo->delete();

        return back()->with('success', 'Tipo de alerta eliminado correctamente.');
    }

    /**
     * Agregar un nuevo punto de control (Administrador).
     */
    public function adminAddPunto(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:100',
        ]);

        $empresaId = auth()->user()->empresa_id;

        // Evitar duplicados de nombre
        $existe = PuntoControl::where('empresa_id', $empresaId)
            ->where('nombre', $request->nombre)
            ->exists();

        if ($existe) {
            return back()->with('error', 'Este punto de control ya existe.');
        }

        PuntoControl::create([
            'empresa_id' => $empresaId,
            'nombre'     => $request->nombre,
        ]);

        return back()->with('success', 'Punto de control agregado correctamente.');
    }

    /**
     * Eliminar un punto de control (Administrador).
     */
    public function adminDeletePunto($id)
    {
        $empresaId = auth()->user()->empresa_id;
        $punto = PuntoControl::where('empresa_id', $empresaId)->findOrFail($id);
        $punto->delete();

        return back()->with('success', 'Punto de control eliminado correctamente.');
    }

    /**
     * Obtener listado de alertas activas en formato JSON (API para conductores).
     */
    public function getActivosApi()
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json(['error' => 'No autorizado'], 401);
        }
        $empresaId = $user->empresa_id;

        $alertas = AlertaOperativo::with(['conductor.vehiculos'])
            ->where('empresa_id', $empresaId)
            ->where('estado', 'activo')
            ->where('visible_conductor', true)
            ->where('expires_at', '>', now())
            ->get()
            ->map(function ($al) use ($user) {
                $timeStr = $al->created_at->format('h:i A');
                $isCreator = $user->conductor && $al->conductor_id === $user->conductor->id;
                
                $creatorStr = 'Administración';
                if ($al->conductor) {
                    $veh = $al->conductor->vehiculos->first();
                    $creatorStr = $veh ? "la flota {$veh->numero_flota}" : 'la flota S/N';
                }

                return [
                    'id'            => $al->id,
                    'titulo'        => $al->titulo ?: '⚠️ Control / Operativo',
                    'punto'         => $al->punto,
                    'mensaje'       => $al->mensaje,
                    'tipo'          => $al->tipo ?: 'operativo',
                    'creado_at'     => $timeStr,
                    'es_creador'    => $isCreator,
                    'reportado_por' => $creatorStr
                ];
            });

        return response()->json([
            'alertas' => $alertas
        ]);
    }
}
