<?php

namespace App\Http\Controllers\Propietario;

use App\Http\Controllers\Controller;
use App\Models\Vuelta;
use Carbon\Carbon;
use Illuminate\Http\Request;

class VueltaController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $propietario = $user->propietario;

        if (!$propietario) {
            abort(403, 'No tienes un perfil de propietario asociado.');
        }

        $vehiculos = $propietario->vehiculos;
        $vehiculoIds = $vehiculos->pluck('id')->toArray();

        $fecha = $request->input('fecha', today()->toDateString());
        $carbonFecha = Carbon::parse($fecha);
        $mesActual = $carbonFecha->month;
        $anioActual = $carbonFecha->year;

        // Métricas del mes correspondiente a la fecha
        $vueltasHoy = Vuelta::whereIn('vehiculo_id', $vehiculoIds)
            ->whereDate('fecha', today())
            ->where('estado', 'completada')
            ->count();

        $diasTrabajadosMes = Vuelta::whereIn('vehiculo_id', $vehiculoIds)
            ->whereYear('fecha', $anioActual)
            ->whereMonth('fecha', $mesActual)
            ->where('estado', 'completada')
            ->distinct()
            ->count('fecha');

        $vueltasMes = Vuelta::whereIn('vehiculo_id', $vehiculoIds)
            ->whereYear('fecha', $anioActual)
            ->whereMonth('fecha', $mesActual)
            ->where('estado', 'completada')
            ->count();

        // Listado de vueltas para la fecha seleccionada
        $vueltas = Vuelta::whereIn('vehiculo_id', $vehiculoIds)
            ->whereDate('fecha', $fecha)
            ->with(['ruta', 'conductor', 'vehiculo', 'paraderoSalida', 'paraderoLlegada'])
            ->orderBy('hora_salida', 'desc')
            ->get();

        return view('users.propietario.vueltas', compact(
            'propietario',
            'vehiculos',
            'vueltas',
            'fecha',
            'vueltasHoy',
            'diasTrabajadosMes',
            'vueltasMes'
        ));
    }
}
